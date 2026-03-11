<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function geojson(Request $request): JsonResponse
    {
        $query = Event::query();

        if ($request->filled('type')) {
            $query->ofType($request->input('type'));
        }

        if ($request->filled('bbox')) {
            $bbox = explode(',', $request->input('bbox'));
            if (count($bbox) === 4) {
                $query->withinBbox(
                    (float) $bbox[0],
                    (float) $bbox[1],
                    (float) $bbox[2],
                    (float) $bbox[3],
                );
            }
        }

        $events = $query->orderByDesc('timestamp')->limit(500)->get();

        $features = $events->map(fn (Event $event) => [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$event->lng, $event->lat],
            ],
            'properties' => [
                'id' => $event->id,
                'type' => $event->type,
                'severity' => $event->severity,
                'location' => $event->location,
                'description' => $event->description,
                'source' => $event->source,
                'timestamp' => $event->timestamp->toIso8601String(),
            ],
        ]);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features->values(),
        ]);
    }
}
