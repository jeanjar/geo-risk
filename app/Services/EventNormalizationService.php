<?php

namespace App\Services;

class EventNormalizationService
{
    public function normalizeEarthquake(array $feature): array
    {
        $props = $feature['properties'];
        $coords = $feature['geometry']['coordinates'];

        return [
            'external_id' => 'usgs_' . $feature['id'],
            'type' => 'earthquake',
            'lat' => $coords[1],
            'lng' => $coords[0],
            'location' => $props['place'] ?? null,
            'description' => sprintf('Magnitude %s earthquake - %s', $props['mag'] ?? 'N/A', $props['place'] ?? 'Unknown'),
            'severity' => $props['mag'] ?? null,
            'source' => 'usgs',
            'timestamp' => date('Y-m-d H:i:s', ($props['time'] ?? time()) / 1000),
        ];
    }

    public function normalizeWeatherAlert(array $feature): array
    {
        $props = $feature['properties'];

        $severity = match ($props['severity'] ?? 'Unknown') {
            'Extreme' => 5.0,
            'Severe' => 4.0,
            'Moderate' => 3.0,
            'Minor' => 2.0,
            default => 1.0,
        };

        $centroid = $this->calculateCentroid($feature['geometry'] ?? null);

        return [
            'external_id' => 'noaa_' . md5($props['id'] ?? $props['headline'] ?? uniqid()),
            'type' => 'weather',
            'lat' => $centroid['lat'],
            'lng' => $centroid['lng'],
            'location' => $props['areaDesc'] ?? null,
            'description' => $props['headline'] ?? $props['event'] ?? 'Weather Alert',
            'severity' => $severity,
            'source' => 'noaa',
            'timestamp' => $props['onset'] ?? $props['effective'] ?? now()->toDateTimeString(),
        ];
    }

    public function normalizeEonetEvent(array $event): array
    {
        $category = strtolower($event['categories'][0]['id'] ?? 'unknown');
        $type = $this->mapEonetCategory($category);

        $geometry = $event['geometry'][0] ?? null;
        $coords = $geometry['coordinates'] ?? [0, 0];

        // EONET may have Point or other geometry types
        $lat = is_array($coords[0] ?? null) ? $coords[0][1] : ($coords[1] ?? 0);
        $lng = is_array($coords[0] ?? null) ? $coords[0][0] : ($coords[0] ?? 0);

        $severity = $this->extractEonetSeverity($event);

        return [
            'external_id' => 'eonet_' . $event['id'],
            'type' => $type,
            'lat' => $lat,
            'lng' => $lng,
            'location' => $event['title'] ?? null,
            'description' => $event['title'] ?? 'EONET Event',
            'severity' => $severity,
            'source' => 'nasa_eonet',
            'timestamp' => $geometry['date'] ?? now()->toDateTimeString(),
        ];
    }

    public function normalizeGdacsEvent(array $feature): array
    {
        $props = $feature['properties'] ?? [];
        $coords = $feature['geometry']['coordinates'] ?? [0, 0];

        $eventType = strtolower($props['eventtype'] ?? 'unknown');
        $type = $this->mapGdacsEventType($eventType);

        $severity = $this->mapGdacsAlertLevel($props['alertlevel'] ?? null);

        return [
            'external_id' => 'gdacs_' . ($props['eventid'] ?? md5(json_encode($props))),
            'type' => $type,
            'lat' => $coords[1] ?? 0,
            'lng' => $coords[0] ?? 0,
            'location' => $props['country'] ?? $props['name'] ?? null,
            'description' => $props['name'] ?? $props['description'] ?? 'GDACS Event',
            'severity' => $severity,
            'source' => 'gdacs',
            'timestamp' => $props['fromdate'] ?? now()->toDateTimeString(),
        ];
    }

    private function mapEonetCategory(string $category): string
    {
        return match ($category) {
            'volcanoes' => 'volcano',
            'floods' => 'flood',
            'severestorms' => 'storm',
            'landslides' => 'landslide',
            'wildfires' => 'wildfire',
            'earthquakes' => 'earthquake',
            'drought' => 'drought',
            'seaandlakeice', 'snow' => 'ice',
            'tempextremes' => 'extreme_temp',
            default => $category,
        };
    }

    private function mapGdacsEventType(string $eventType): string
    {
        return match ($eventType) {
            'eq' => 'earthquake',
            'tc' => 'cyclone',
            'fl' => 'flood',
            'vo' => 'volcano',
            'wf' => 'wildfire',
            'dr' => 'drought',
            default => $eventType,
        };
    }

    private function mapGdacsAlertLevel(?string $level): float
    {
        return match (strtolower($level ?? '')) {
            'red' => 5.0,
            'orange' => 3.5,
            'green' => 2.0,
            default => 1.0,
        };
    }

    private function extractEonetSeverity(array $event): float
    {
        $magnitudeValue = $event['geometry'][0]['magnitudeValue'] ?? null;

        if ($magnitudeValue !== null && is_numeric($magnitudeValue)) {
            $unit = strtolower($event['geometry'][0]['magnitudeUnit'] ?? '');

            return match (true) {
                str_contains($unit, 'kts') => min($magnitudeValue / 30, 5.0),
                str_contains($unit, 'acres') => min(log10(max($magnitudeValue, 1)) / 1.2, 5.0),
                default => min((float) $magnitudeValue, 5.0),
            };
        }

        return 3.0;
    }

    public function calculateCentroid(?array $geometry): array
    {
        if (! $geometry || empty($geometry['coordinates'])) {
            return ['lat' => 0, 'lng' => 0];
        }

        $coords = $geometry['coordinates'];

        if ($geometry['type'] === 'Point') {
            return ['lat' => $coords[1], 'lng' => $coords[0]];
        }

        $points = $this->flattenCoordinates($coords);
        if (empty($points)) {
            return ['lat' => 0, 'lng' => 0];
        }

        $lats = array_column($points, 1);
        $lngs = array_column($points, 0);

        return [
            'lat' => array_sum($lats) / count($lats),
            'lng' => array_sum($lngs) / count($lngs),
        ];
    }

    private function flattenCoordinates(array $coords): array
    {
        $points = [];

        foreach ($coords as $item) {
            if (is_array($item) && isset($item[0]) && is_numeric($item[0])) {
                $points[] = $item;
            } elseif (is_array($item)) {
                $points = array_merge($points, $this->flattenCoordinates($item));
            }
        }

        return $points;
    }
}
