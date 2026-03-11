<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventIngestionService
{
    private const EONET_CATEGORIES = [
        'volcanoes',
        'floods',
        'severeStorms',
        'landslides',
        'wildfires',
    ];

    private const GDACS_EVENT_TYPES = 'TC;FL;VO;DR';

    public function __construct(
        private EventNormalizationService $normalizer,
    ) {}

    public function ingestAll(): int
    {
        $total = 0;
        $total += $this->ingestEarthquakes();
        $total += $this->ingestWeatherAlerts();
        $total += $this->ingestEonetEvents();
        $total += $this->ingestGdacsEvents();

        return $total;
    }

    public function ingestEarthquakes(): int
    {
        return $this->safeIngest('USGS', function () {
            $response = Http::timeout(30)
                ->get('https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_day.geojson');

            if (! $response->successful()) {
                return 0;
            }

            $count = 0;
            foreach ($response->json('features') ?? [] as $feature) {
                $normalized = $this->normalizer->normalizeEarthquake($feature);
                $this->upsertEvent($normalized);
                $count++;
            }

            return $count;
        });
    }

    public function ingestWeatherAlerts(): int
    {
        return $this->safeIngest('NOAA', function () {
            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'GlobalGeoRiskDashboard/1.0'])
                ->get('https://api.weather.gov/alerts/active', [
                    'status' => 'actual',
                    'limit' => 50,
                ]);

            if (! $response->successful()) {
                return 0;
            }

            $count = 0;
            foreach ($response->json('features') ?? [] as $feature) {
                $normalized = $this->normalizer->normalizeWeatherAlert($feature);

                if ($normalized['lat'] == 0 && $normalized['lng'] == 0) {
                    continue;
                }

                $this->upsertEvent($normalized);
                $count++;
            }

            return $count;
        });
    }

    public function ingestEonetEvents(): int
    {
        return $this->safeIngest('NASA EONET', function () {
            $categories = implode(',', self::EONET_CATEGORIES);

            $response = Http::timeout(30)
                ->get('https://eonet.gsfc.nasa.gov/api/v3/events', [
                    'category' => $categories,
                    'status' => 'open',
                    'limit' => 100,
                ]);

            if (! $response->successful()) {
                return 0;
            }

            $count = 0;
            foreach ($response->json('events') ?? [] as $event) {
                if (empty($event['geometry'])) {
                    continue;
                }

                $normalized = $this->normalizer->normalizeEonetEvent($event);

                if ($normalized['lat'] == 0 && $normalized['lng'] == 0) {
                    continue;
                }

                $this->upsertEvent($normalized);
                $count++;
            }

            return $count;
        });
    }

    public function ingestGdacsEvents(): int
    {
        return $this->safeIngest('GDACS', function () {
            $response = Http::timeout(30)
                ->withHeaders(['Accept' => 'application/json'])
                ->get('https://www.gdacs.org/gdacsapi/api/events/geteventlist/SEARCH', [
                    'eventlist' => self::GDACS_EVENT_TYPES,
                    'fromdate' => now()->subDays(30)->format('Y-m-d'),
                    'todate' => now()->format('Y-m-d'),
                    'alertlevel' => 'red;orange;green',
                ]);

            if (! $response->successful()) {
                return 0;
            }

            $data = $response->json();
            $features = $data['features'] ?? [];

            $count = 0;
            foreach ($features as $feature) {
                $coords = $feature['geometry']['coordinates'] ?? null;
                if (! $coords || (($coords[0] ?? 0) == 0 && ($coords[1] ?? 0) == 0)) {
                    continue;
                }

                $normalized = $this->normalizer->normalizeGdacsEvent($feature);
                $this->upsertEvent($normalized);
                $count++;
            }

            return $count;
        });
    }

    private function upsertEvent(array $normalized): void
    {
        Event::updateOrCreate(
            ['external_id' => $normalized['external_id']],
            $normalized,
        );
    }

    private function safeIngest(string $source, callable $callback): int
    {
        try {
            $count = $callback();
            Log::info("Ingested {$count} events from {$source}");

            return $count;
        } catch (\Throwable $e) {
            Log::error("{$source} ingestion failed", ['error' => $e->getMessage()]);

            return 0;
        }
    }
}
