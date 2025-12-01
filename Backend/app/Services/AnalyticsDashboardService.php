<?php

namespace App\Services;

use App\DTO\Dashboard\EventsStatsDTO;
use App\DTO\Dashboard\OverviewDTO;
use App\DTO\Dashboard\SessionsStatsDTO;
use App\Repositories\EventRepository;
use App\Repositories\SessionRepository;
use Illuminate\Support\Carbon;

class AnalyticsDashboardService
{
    public function __construct(
        protected EventRepository $events,
        protected SessionRepository $sessions,
    ) {
    }

    public function overview(string $range = '7d'): OverviewDTO
    {
        [$weekStart, $now] = $this->resolveRange($range);
        [$previousStart, $previousEnd] = $this->previousRange($weekStart, $now);

        $sessionCount = $this->sessions->countBetween($weekStart, $now);
        $previousSessionCount = max(1, $this->sessions->countBetween($previousStart, $previousEnd));
        $eventCount = $this->events->countBetween($weekStart, $now);
        $previousEventCount = max(1, $this->events->countBetween($previousStart, $previousEnd));

        $conversionEvents = $this->events->countByType('conversion', $weekStart, $now);
        $previousConversions = $this->events->countByType('conversion', $previousStart, $previousEnd);
        $conversionRate = $sessionCount > 0 ? ($conversionEvents / $sessionCount) * 100 : 0;
        $previousConversionRate = $previousSessionCount > 0
            ? ($previousConversions / $previousSessionCount) * 100
            : 0;

        $avgDuration = $this->sessions->averageDuration($weekStart, $now);
        $previousAvgDuration = $this->sessions->averageDuration($previousStart, $previousEnd);

        $activitySessions = $this->sessions->dailyCounts($weekStart, $now);
        $activityEvents = $this->events->dailyCounts($weekStart, $now);

        $labels = collect(array_keys($activitySessions))
            ->map(fn ($date) => Carbon::parse($date)->isoFormat('ddd'))
            ->all();

        $realtime = $this->events->latest()->map(function ($event) {
            $occurredAt = $event->timestamp ?? $event->created_at;

            return [
                'id' => $event->id,
                'name' => $event->name,
                'type' => $event->type,
                'timestamp' => optional($occurredAt)?->toAtomString(),
                'time_ago' => optional($occurredAt)?->diffForHumans(),
            ];
        })->all();

        $kpis = [
            [
                'label' => 'Sessions actives',
                'value' => number_format($sessionCount),
                'subtitle' => 'Utilisateurs uniques',
                'trend' => $this->trend($sessionCount, $previousSessionCount),
                'icon' => 'users',
            ],
            [
                'label' => 'Événements totaux',
                'value' => number_format($eventCount),
                'subtitle' => 'Actions tracées',
                'trend' => $this->trend($eventCount, $previousEventCount),
                'icon' => 'sparkles',
            ],
            [
                'label' => 'Taux de conversion',
                'value' => number_format($conversionRate, 1) . '%',
                'subtitle' => 'Réservations / Sessions',
                'trend' => $this->trend($conversionRate, $previousConversionRate),
                'icon' => 'trending-up',
            ],
            [
                'label' => 'Durée moyenne',
                'value' => $this->formatDuration($avgDuration),
                'subtitle' => 'Par session',
                'trend' => $this->trend($avgDuration, max($previousAvgDuration, 1)),
                'icon' => 'clock',
            ],
        ];

        $activity = [
            'labels' => $labels,
            'sessions' => array_values($activitySessions),
            'events' => array_values($activityEvents),
        ];

        return new OverviewDTO($kpis, $activity, $realtime);
    }

    public function events(string $range = '7d'): EventsStatsDTO
    {
        [$start, $now] = $this->resolveRange($range);

        $timeline = $this->events->dailyCounts($start, $now);
        $totals = [
            'weekly' => $this->events->countBetween($start, $now),
            'conversions' => $this->events->countByType('conversion', $start, $now),
            'unique_sessions' => $this->sessions->countBetween($start, $now),
        ];

        $totals['conversion_rate'] = $totals['unique_sessions'] > 0
            ? round(($totals['conversions'] / $totals['unique_sessions']) * 100, 1)
            : 0;

        return new EventsStatsDTO(
            totals: $totals,
            timeline: [
                'labels' => array_map(
                    fn ($date) => Carbon::parse($date)->isoFormat('ddd'),
                    array_keys($timeline)
                ),
                'data' => array_values($timeline),
            ],
            topEvents: $this->events->topEvents(6, $start, $now)->all(),
        );
    }

    public function sessions(string $range = '7d'): SessionsStatsDTO
    {
        [$start, $now] = $this->resolveRange($range);

        $timeline = $this->sessions->dailyCounts($start, $now);
        $recentSessions = $this->sessions->recent()->map(function ($session) {
            return [
                'id' => $session->id,
                'user' => $session->user?->name ?? 'Utilisateur',
                'started_at' => optional($session->start_time)?->diffForHumans(),
                'platform' => $session->platform ?? 'Unknown',
                'duration' => $this->formatDuration((int) $session->duration_seconds),
            ];
        })->all();

        $totals = [
            'active' => $this->sessions->countSince($now->copy()->subDay()),
            'weekly' => $this->sessions->countBetween($start, $now),
            'average_duration' => $this->formatDuration($this->sessions->averageDuration($start, $now)),
        ];

        return new SessionsStatsDTO(
            totals: $totals,
            platforms: $this->sessions->platformBreakdown($start, $now),
            timeline: [
                'labels' => array_map(
                    fn ($date) => Carbon::parse($date)->isoFormat('ddd'),
                    array_keys($timeline)
                ),
                'data' => array_values($timeline),
            ],
            recent: $recentSessions,
        );
    }

    protected function trend(float $current, float $previous): array
    {
        $previous = $previous === 0.0 ? 1.0 : $previous;
        $change = (($current - $previous) / $previous) * 100;

        return [
            'value' => round($change, 1),
            'isPositive' => $change >= 0,
        ];
    }

    protected function formatDuration(float $seconds): string
    {
        $seconds = max(0, (int) $seconds);

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes >= 60) {
            $hours = intdiv($minutes, 60);
            $minutes = $minutes % 60;

            return sprintf('%dh %02dm', $hours, $minutes);
        }

        return sprintf('%dm %02ds', $minutes, $remainingSeconds);
    }

    protected function resolveRange(string $range): array
    {
        $end = Carbon::now();

        $start = match ($range) {
            '24h' => $end->copy()->subDay(),
            '30d' => $end->copy()->subDays(29),
            default => $end->copy()->subDays(6),
        };

        return [$start, $end];
    }

    protected function previousRange(Carbon $start, Carbon $end): array
    {
        $length = max(1, $start->diffInDays($end) + 1);
        $previousEnd = $start->copy()->subDay();
        $previousStart = $previousEnd->copy()->subDays($length - 1);

        return [$previousStart, $previousEnd];
    }
}
