<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Event::query();

        if ($request->filled('type')) {
            $query->ofType($request->input('type'));
        }

        $events = $query->orderByDesc('timestamp')->limit((int) config('georisk.events_limit', 1000))->get();

        $criticalEvents = Event::where('severity', '>=', 4)
            ->orderByDesc('timestamp')
            ->limit(5)
            ->get();

        // Activity timeline: events per hour for last 24h
        $timeline = Event::where('timestamp', '>=', now()->subHours(24))
            ->selectRaw("date_trunc('hour', timestamp) as hour, count(*) as count")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->mapWithKeys(fn ($count, $hour) => [
                \Carbon\Carbon::parse($hour)->format('H:i') => $count,
            ]);

        // Fill missing hours with 0
        $fullTimeline = collect(range(0, 23))->mapWithKeys(function ($i) use ($timeline) {
            $hour = now()->subHours(23 - $i)->startOfHour()->format('H:i');

            return [$hour => $timeline[$hour] ?? 0];
        });

        $avgSeverityByType = Event::selectRaw('type, round(avg(severity)::numeric, 1) as avg_severity')
            ->whereNotNull('severity')
            ->groupBy('type')
            ->pluck('avg_severity', 'type');

        return Inertia::render('Dashboard', [
            'events' => $events,
            'eventTypes' => Event::distinct()->pluck('type')->values(),
            'filters' => [
                'type' => $request->input('type'),
            ],
            'stats' => [
                'total' => Event::count(),
                'byType' => Event::selectRaw('type, count(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type'),
                'critical' => Event::where('severity', '>=', 4)->count(),
                'last24h' => Event::where('timestamp', '>=', now()->subHours(24))->count(),
                'avgSeverityByType' => $avgSeverityByType,
            ],
            'criticalEvents' => $criticalEvents,
            'timeline' => $fullTimeline,
        ]);
    }
}
