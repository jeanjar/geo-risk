<?php

namespace App\Services\RiskSummary;

use App\Models\Event;

class RiskContextBuilder
{
    public const SYSTEM_PROMPT = 'You are a senior risk intelligence analyst. Generate a concise risk briefing based on the geospatial event data provided. Focus on: critical patterns, geographic clusters, severity trends, and actionable insights. Use markdown formatting with bold for emphasis. Keep it under 200 words. Write in a professional, urgent tone appropriate for a risk operations center. Do not use emojis.';

    public static function build(?string $typeFilter = null): array
    {
        $query = Event::query();

        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        $total = (clone $query)->count();
        $last24h = (clone $query)->where('timestamp', '>=', now()->subHours(24))->count();
        $critical = (clone $query)->where('severity', '>=', 4)->count();

        $byType = (clone $query)
            ->selectRaw('type, count(*) as count, round(avg(severity)::numeric, 1) as avg_severity')
            ->groupBy('type')
            ->get()
            ->map(fn ($row) => [
                'type' => $row->type,
                'count' => $row->count,
                'avg_severity' => $row->avg_severity,
            ])
            ->toArray();

        $topCritical = (clone $query)
            ->where('severity', '>=', 4)
            ->orderByDesc('severity')
            ->orderByDesc('timestamp')
            ->limit(10)
            ->get(['type', 'location', 'description', 'severity', 'timestamp'])
            ->toArray();

        $recentEvents = (clone $query)
            ->orderByDesc('timestamp')
            ->limit(5)
            ->get(['type', 'location', 'severity', 'timestamp'])
            ->toArray();

        return [
            'total_events' => $total,
            'events_last_24h' => $last24h,
            'critical_events' => $critical,
            'breakdown_by_type' => $byType,
            'top_critical_events' => $topCritical,
            'most_recent_events' => $recentEvents,
            'filter_applied' => $typeFilter,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
