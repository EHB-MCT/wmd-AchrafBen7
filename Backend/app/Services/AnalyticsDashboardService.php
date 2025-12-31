<?php

namespace App\Services;

use App\DTO\Dashboard\EventsStatsDTO;
use App\DTO\Dashboard\OverviewDTO;
use App\DTO\Dashboard\SessionsStatsDTO;
use App\Models\Event;
use App\Models\Funnel;
use App\Models\SearchQuery;
use App\Models\User;
use App\Models\UserSession;
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

    public function overview(string $range = '7d', bool $withComparison = false): OverviewDTO
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
                'label' => 'Actieve sessies',
                'value' => number_format($sessionCount),
                'subtitle' => 'Unieke gebruikers',
                'trend' => $this->trend($sessionCount, $previousSessionCount),
                'icon' => 'users',
            ],
            [
                'label' => 'Totale events',
                'value' => number_format($eventCount),
                'subtitle' => 'Gelogde acties',
                'trend' => $this->trend($eventCount, $previousEventCount),
                'icon' => 'sparkles',
            ],
            [
                'label' => 'Gemiddelde duur',
                'value' => $this->formatDuration($avgDuration),
                'subtitle' => 'Per sessie',
                'trend' => $this->trend($avgDuration, max($previousAvgDuration, 1)),
                'icon' => 'clock',
            ],
        ];

        $activity = [
            'labels' => $labels,
            'sessions' => array_values($activitySessions),
            'events' => array_values($activityEvents),
        ];

        $comparison = null;

        if ($withComparison) {
            $previousSessions = $this->sessions->dailyCounts($previousStart, $previousEnd);
            $previousEvents = $this->events->dailyCounts($previousStart, $previousEnd);

            $comparison = [
                'activity' => [
                    'labels' => array_map(
                        fn ($date) => Carbon::parse($date)->isoFormat('ddd'),
                        array_keys($previousSessions)
                    ),
                    'sessions' => array_values($previousSessions),
                    'events' => array_values($previousEvents),
                ],
            ];
        }

        if (empty($realtime)) {
            $realtime = [];
        }

        return new OverviewDTO($kpis, $activity, $realtime, $comparison);
    }

    public function events(string $range = '7d', bool $withComparison = false): EventsStatsDTO
    {
        [$start, $now] = $this->resolveRange($range);
        [$previousStart, $previousEnd] = $this->previousRange($start, $now);

        $timeline = $this->events->dailyCounts($start, $now);
        $totals = [
            'weekly' => $this->events->countBetween($start, $now),
            'conversions' => $this->events->countByType('conversion', $start, $now),
            'unique_sessions' => $this->sessions->countBetween($start, $now),
        ];

        $totals['conversion_rate'] = $totals['unique_sessions'] > 0
            ? round(($totals['conversions'] / $totals['unique_sessions']) * 100, 1)
            : 0;

        $comparison = null;

        if ($withComparison) {
            $previousTimeline = $this->events->dailyCounts($previousStart, $previousEnd);
            $previousTotals = [
                'weekly' => $this->events->countBetween($previousStart, $previousEnd),
                'conversions' => $this->events->countByType('conversion', $previousStart, $previousEnd),
                'unique_sessions' => $this->sessions->countBetween($previousStart, $previousEnd),
            ];
            $previousTotals['conversion_rate'] = $previousTotals['unique_sessions'] > 0
                ? round(($previousTotals['conversions'] / $previousTotals['unique_sessions']) * 100, 1)
                : 0;

            $comparison = [
                'timeline' => [
                    'labels' => array_map(
                        fn ($date) => Carbon::parse($date)->isoFormat('ddd'),
                        array_keys($previousTimeline)
                    ),
                    'data' => array_values($previousTimeline),
                ],
                'totals' => $previousTotals,
            ];
        }

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
            comparison: $comparison,
        );
    }

    public function sessions(string $range = '7d', bool $withComparison = false): SessionsStatsDTO
    {
        [$start, $now] = $this->resolveRange($range);
        [$previousStart, $previousEnd] = $this->previousRange($start, $now);

        $timeline = $this->sessions->dailyCounts($start, $now);
        $recentSessions = $this->sessions->recent()->map(function ($session) {
            return [
                'id' => $session->id,
                'user' => $session->user?->name ?? 'Gebruiker',
                'started_at' => optional($session->start_time)?->diffForHumans(),
                'platform' => $session->platform ?? 'Onbekend',
                'duration' => $this->formatDuration((int) $session->duration_seconds),
            ];
        })->all();

        $totals = [
            'active' => $this->sessions->countSince($now->copy()->subDay()),
            'weekly' => $this->sessions->countBetween($start, $now),
            'average_duration' => $this->formatDuration($this->sessions->averageDuration($start, $now)),
        ];

        $comparison = null;

        if ($withComparison) {
            $previousTimeline = $this->sessions->dailyCounts($previousStart, $previousEnd);

            $comparison = [
                'timeline' => [
                    'labels' => array_map(
                        fn ($date) => Carbon::parse($date)->isoFormat('ddd'),
                        array_keys($previousTimeline)
                    ),
                    'data' => array_values($previousTimeline),
                ],
                'totals' => [
                    'active' => $this->sessions->countSince($previousEnd->copy()->subDay()),
                    'weekly' => $this->sessions->countBetween($previousStart, $previousEnd),
                    'average_duration' => $this->formatDuration($this->sessions->averageDuration($previousStart, $previousEnd)),
                ],
            ];
        }

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
            comparison: $comparison,
        );
    }

    public function heatmap(string $range = '24h'): array
    {
        [$start, $end] = $this->resolveRange($range);

        $points = Event::select('device_x', 'device_y')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('device_x')
            ->whereNotNull('device_y')
            ->whereBetween('timestamp', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->groupBy('device_x', 'device_y')
            ->orderByDesc('total')
            ->get();

        if ($points->isEmpty()) {
            return [
                'points' => [],
                'meta' => [
                    'total_events' => 0,
                    'max_intensity' => 0,
                    'max_x' => 0,
                    'max_y' => 0,
                    'range' => $range,
                ],
            ];
        }

        $maxX = max(1, (int) $points->max('device_x'));
        $maxY = max(1, (int) $points->max('device_y'));
        $maxIntensity = max(1, (int) $points->max('total'));

        return [
            'points' => $points->map(fn ($point) => [
                'x' => $point->device_x,
                'y' => $point->device_y,
                'normalized_x' => $point->device_x / $maxX,
                'normalized_y' => $point->device_y / $maxY,
                'count' => (int) $point->total,
                'intensity' => round($point->total / $maxIntensity, 3),
            ])->values(),
            'meta' => [
                'total_events' => $points->sum('total'),
                'max_intensity' => $maxIntensity,
                'max_x' => $maxX,
                'max_y' => $maxY,
                'range' => $range,
            ],
        ];
    }

    public function timeline(?string $userId, string $range = '7d'): array
    {
        [$start, $end] = $this->resolveRange($range);

        $users = User::has('sessions')
            ->select('id', 'uid')
            ->orderBy('uid')
            ->get()
            ->map(function ($user) {
                $user->name = $user->uid ?? $user->id;
                return $user;
            });

        if (! $userId && $users->isNotEmpty()) {
            $userId = $users->first()->id;
        }

        if (! $userId) {
            return [
                'users' => $users,
                'active_user' => null,
                'entries' => [],
                'range' => $range,
            ];
        }

        $sessions = UserSession::with(['events' => function ($query) use ($start, $end) {
            $query->whereBetween('timestamp', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                ->orderBy('timestamp');
        }])
            ->where('user_id', $userId)
            ->whereBetween('start_time', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get();

        $entries = [];

        foreach ($sessions as $session) {
            $entries[] = [
                'id' => $session->id . '-start',
                'kind' => 'session_start',
                'label' => 'Sessie gestart',
                'timestamp' => optional($session->start_time)?->toAtomString(),
                'meta' => [
                    'platform' => $session->platform,
                    'battery' => $session->battery_level,
                ],
            ];

            foreach ($session->events as $event) {
                $entries[] = [
                    'id' => $event->id,
                    'kind' => 'event',
                    'type' => $event->type,
                    'label' => $event->name,
                    'timestamp' => optional($event->timestamp)?->toAtomString(),
                    'meta' => [
                        'screen' => $event->value['screen'] ?? null,
                        'details' => $event->value['label'] ?? null,
                    ],
                ];
            }

            $entries[] = [
                'id' => $session->id . '-end',
                'kind' => 'session_end',
                'label' => 'Sessie afgerond',
                'timestamp' => optional($session->end_time ?? $session->start_time)?->toAtomString(),
                'meta' => [
                    'duration' => $this->formatDuration((int) $session->duration_seconds),
                ],
            ];
        }

        $payload = [
            'users' => $users,
            'active_user' => $userId,
            'entries' => $entries,
            'range' => $range,
        ];

        return $payload;
    }

    public function kpiSnapshot(string $range = '7d'): array
    {
        [$start, $end] = $this->resolveRange($range);
        [$previousStart, $previousEnd] = $this->previousRange($start, $end);

        $overviewKpis = [
            'sessions' => $this->sessions->countBetween($start, $end),
            'events' => $this->events->countBetween($start, $end),
            'conversions' => $this->events->countByType('conversion', $start, $end),
            'average_duration' => $this->formatDuration($this->sessions->averageDuration($start, $end)),
        ];

        $conversionPages = Event::selectRaw("COALESCE(value->>'screen', name) as screen")
            ->selectRaw('COUNT(*) as total')
            ->where('type', 'conversion')
            ->whereBetween('timestamp', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->groupBy('screen')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'screen' => $row->screen ?? 'Onbekend',
                'total' => (int) $row->total,
            ])->all();

        $search = $this->search($range);
        $conversions = $this->conversions($range);

        $snapshot = [
            'range' => $range,
            'current_period' => [$start->toDateString(), $end->toDateString()],
            'previous_period' => [$previousStart->toDateString(), $previousEnd->toDateString()],
            'overview' => $overviewKpis,
            'search' => $search,
            'conversions' => $conversions,
            'top_conversion_pages' => $conversionPages,
        ];

        return $snapshot;
    }

    public function search(string $range = '7d'): array
    {
        [$start, $end] = $this->resolveRange($range);
        $bounds = [$start->copy()->startOfDay(), $end->copy()->endOfDay()];

        $baseQuery = SearchQuery::query()->whereBetween('timestamp', $bounds);
        $total = (clone $baseQuery)->count();
        $zero = (clone $baseQuery)->where('result_count', 0)->count();
        $success = (clone $baseQuery)->where('result_count', '>=', 5)->count();
        $partial = (clone $baseQuery)->whereBetween('result_count', [1, 4])->count();

        if (($success + $partial + $zero) < $total) {
            $partial += $total - ($success + $partial + $zero);
        }

        $topQueries = SearchQuery::select('query')
            ->selectRaw('COUNT(*) as volume')
            ->selectRaw('AVG(result_count) as avg_results')
            ->whereBetween('timestamp', $bounds)
            ->groupBy('query')
            ->orderByDesc('volume')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'phrase' => $row->query,
                'volume' => (int) $row->volume,
                'conversion' => $this->conversionRateFromResults((float) $row->avg_results),
            ])
            ->all();

        $response = [
            'totals' => [
                'searches' => $total,
                'click_rate' => $total > 0 ? round((($total - $zero) / $total) * 100, 1) : 0,
                'zero_result_rate' => $total > 0 ? round(($zero / $total) * 100, 1) : 0,
            ],
            'top_queries' => $topQueries,
            'distribution' => [
                'success' => [
                    'count' => $success,
                    'percentage' => $total > 0 ? round(($success / $total) * 100, 1) : 0,
                ],
                'partial' => [
                    'count' => $partial,
                    'percentage' => $total > 0 ? round(($partial / $total) * 100, 1) : 0,
                ],
                'empty' => [
                    'count' => $zero,
                    'percentage' => $total > 0 ? round(($zero / $total) * 100, 1) : 0,
                ],
            ],
        ];

        return $response;
    }

    public function conversions(string $range = '7d'): array
    {
        [$start, $end] = $this->resolveRange($range);
        $bounds = [$start->copy()->startOfDay(), $end->copy()->endOfDay()];

        $steps = Funnel::select('step', 'step_order')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('timestamp', $bounds)
            ->groupBy('step', 'step_order')
            ->orderBy('step_order')
            ->get();

        $previous = 0;
        $funnel = $steps->map(function ($step) use (&$previous) {
            $total = (int) $step->total;
            $rate = $previous > 0 ? round(($total / $previous) * 100, 1) : 100.0;
            $previous = max($total, 1);

            return [
                'label' => $step->step,
                'value' => $total,
                'rate' => $rate,
            ];
        })->values();

        $stageValue = function (string $label) use ($funnel): int {
            $stage = $funnel->firstWhere('label', $label);

            return (int) ($stage['value'] ?? 0);
        };

        $totals = [
            'visits' => $stageValue('Ontdekking'),
            'intents' => $stageValue('Intenties'),
            'quotes' => $stageValue('Offertes'),
            'bookings' => $stageValue('Boekingen'),
        ];

        $totals['conversion_rate'] = $totals['visits'] > 0
            ? round(($totals['bookings'] / max(1, $totals['visits'])) * 100, 1)
            : 0;

        $result = [
            'totals' => $totals,
            'funnel' => $funnel->map(fn ($stage) => [
                'label' => $stage['label'],
                'value' => $stage['value'],
                'rate' => $stage['rate'],
            ])->all(),
        ];

        return $result;
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

    protected function conversionRateFromResults(float $avgResults): float
    {
        if ($avgResults <= 0) {
            return 2.0;
        }

        return round(min(85, max(2, $avgResults * 2.4)), 1);
    }

    protected function mockOverview(): OverviewDTO
    {
        $activity = [
            'labels' => ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'],
            'sessions' => [420, 480, 530, 610, 720, 540, 400],
            'events' => [1200, 1380, 1420, 1550, 1680, 1400, 1100],
        ];

        $kpis = [
            [
                'label' => 'Actieve sessies',
                'value' => number_format(3280),
                'subtitle' => 'Unieke gebruikers',
                'trend' => ['value' => 8.3, 'isPositive' => true],
                'icon' => 'users',
            ],
            [
                'label' => 'Totale events',
                'value' => number_format(16240),
                'subtitle' => 'Gelogde acties',
                'trend' => ['value' => 12.4, 'isPositive' => true],
                'icon' => 'sparkles',
            ],
            [
                'label' => 'Conversieratio',
                'value' => '12.1%',
                'subtitle' => 'Boekingen / Sessies',
                'trend' => ['value' => 4.6, 'isPositive' => true],
                'icon' => 'trending-up',
            ],
            [
                'label' => 'Gemiddelde duur',
                'value' => '14m 32s',
                'subtitle' => 'Per sessie',
                'trend' => ['value' => 2.2, 'isPositive' => true],
                'icon' => 'clock',
            ],
        ];

        return new OverviewDTO($kpis, $activity, $this->mockRealtime());
    }

    protected function mockEventsStats(): EventsStatsDTO
    {
        return new EventsStatsDTO(
            totals: [
                'weekly' => 16240,
                'conversions' => 432,
                'unique_sessions' => 980,
                'conversion_rate' => 12.1,
            ],
            timeline: [
                'labels' => ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'],
                'data' => [2100, 2200, 2350, 2480, 2600, 2300, 1800],
            ],
            topEvents: [
                ['name' => 'button.primary', 'type' => 'click', 'total' => 4320],
                ['name' => 'provider.view', 'type' => 'view', 'total' => 3890],
                ['name' => 'search.performed', 'type' => 'search', 'total' => 2410],
                ['name' => 'cta.start-booking', 'type' => 'click', 'total' => 1650],
                ['name' => 'booking.completed', 'type' => 'conversion', 'total' => 432],
            ],
        );
    }

    protected function mockSessionsStats(): SessionsStatsDTO
    {
        return new SessionsStatsDTO(
            totals: [
                'active' => 420,
                'weekly' => 3280,
                'average_duration' => '14m 32s',
            ],
            platforms: [
                'iOS' => 1520,
                'Android' => 980,
                'Web' => 780,
            ],
            timeline: [
                'labels' => ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'],
                'data' => [320, 360, 410, 470, 520, 420, 280],
            ],
            recent: [
                ['id' => 'mock-1', 'user' => 'Sophie', 'platform' => 'iOS', 'duration' => '12m 18s', 'started_at' => 'il y a 4 min'],
                ['id' => 'mock-2', 'user' => 'Tom', 'platform' => 'Android', 'duration' => '08m 46s', 'started_at' => 'il y a 12 min'],
                ['id' => 'mock-3', 'user' => 'Noor', 'platform' => 'Web', 'duration' => '18m 02s', 'started_at' => 'il y a 35 min'],
            ],
        );
    }

    protected function mockRealtime(): array
    {
        return [
            ['id' => 'rt-1', 'name' => 'booking.completed', 'type' => 'conversion', 'timestamp' => Carbon::now()->toAtomString(), 'time_ago' => 'zojuist'],
            ['id' => 'rt-2', 'name' => 'search.performed', 'type' => 'search', 'timestamp' => Carbon::now()->subMinutes(2)->toAtomString(), 'time_ago' => '2 min geleden'],
            ['id' => 'rt-3', 'name' => 'provider.view', 'type' => 'view', 'timestamp' => Carbon::now()->subMinutes(5)->toAtomString(), 'time_ago' => '5 min geleden'],
        ];
    }

    protected function mockSearch(): array
    {
        return [
            'totals' => [
                'searches' => 12430,
                'click_rate' => 42.0,
                'zero_result_rate' => 3.0,
            ],
            'top_queries' => [
                ['phrase' => 'Premium polijsten', 'volume' => 1230, 'conversion' => 12.0],
                ['phrase' => 'Mobiele uitdeukservice', 'volume' => 980, 'conversion' => 9.0],
                ['phrase' => 'Tesla detailing', 'volume' => 760, 'conversion' => 15.0],
                ['phrase' => 'Ceramic coating', 'volume' => 680, 'conversion' => 8.0],
            ],
            'distribution' => [
                'success' => ['count' => 10442, 'percentage' => 84.0],
                'partial' => ['count' => 1243, 'percentage' => 10.0],
                'empty' => ['count' => 745, 'percentage' => 6.0],
            ],
        ];
    }

    protected function mockConversions(): array
    {
        $funnel = [
            ['label' => 'Ontdekking', 'value' => 24900, 'rate' => 100],
            ['label' => 'Intenties', 'value' => 9320, 'rate' => 37],
            ['label' => 'Offertes', 'value' => 2410, 'rate' => 26],
            ['label' => 'Boekingen', 'value' => 1340, 'rate' => 55],
        ];

        return [
            'totals' => [
                'visits' => 24900,
                'intents' => 9320,
                'quotes' => 2410,
                'bookings' => 1340,
                'conversion_rate' => 5.4,
            ],
            'funnel' => $funnel,
        ];
    }

    protected function mockHeatmap(): array
    {
        $points = [
            ['x' => 80, 'y' => 640, 'normalized_x' => 0.21, 'normalized_y' => 0.21, 'count' => 128, 'intensity' => 1.0],
            ['x' => 210, 'y' => 420, 'normalized_x' => 0.56, 'normalized_y' => 0.48, 'count' => 96, 'intensity' => 0.75],
            ['x' => 330, 'y' => 280, 'normalized_x' => 0.88, 'normalized_y' => 0.65, 'count' => 72, 'intensity' => 0.56],
            ['x' => 150, 'y' => 180, 'normalized_x' => 0.4, 'normalized_y' => 0.78, 'count' => 58, 'intensity' => 0.45],
        ];

        return [
            'points' => $points,
            'meta' => [
                'total_events' => array_sum(array_column($points, 'count')),
                'max_intensity' => 128,
                'max_x' => 375,
                'max_y' => 812,
                'range' => 'mock',
            ],
        ];
    }

    protected function mockTimeline($users): array
    {
        $collection = $users;
        if ($collection->isEmpty()) {
            $collection = collect([
                (object) ['id' => 'mock-user', 'name' => 'Productteam'],
            ]);
        }

        $entries = [
            [
                'id' => 'mock-session-start',
                'kind' => 'session_start',
                'label' => 'Sessie gestart',
                'timestamp' => Carbon::now()->subMinutes(18)->toAtomString(),
                'meta' => ['platform' => 'iOS', 'battery' => 87],
            ],
            [
                'id' => 'mock-event-1',
                'kind' => 'event',
                'type' => 'view',
                'label' => 'provider.view',
                'timestamp' => Carbon::now()->subMinutes(16)->toAtomString(),
                'meta' => ['screen' => 'providers', 'details' => 'LuxeWash'],
            ],
            [
                'id' => 'mock-event-2',
                'kind' => 'event',
                'type' => 'click',
                'label' => 'cta.start-booking',
                'timestamp' => Carbon::now()->subMinutes(14)->toAtomString(),
                'meta' => ['screen' => 'provider.detail', 'details' => 'CTA Boek'],
            ],
            [
                'id' => 'mock-event-3',
                'kind' => 'event',
                'type' => 'conversion',
                'label' => 'booking.completed',
                'timestamp' => Carbon::now()->subMinutes(9)->toAtomString(),
                'meta' => ['screen' => 'booking', 'details' => '€230'],
            ],
            [
                'id' => 'mock-session-end',
                'kind' => 'session_end',
                'label' => 'Sessie afgerond',
                'timestamp' => Carbon::now()->subMinutes(7)->toAtomString(),
                'meta' => ['duration' => '16m 12s'],
            ],
        ];

        return [
            'users' => $collection,
            'active_user' => $collection->first()->id,
            'entries' => $entries,
            'range' => '7d',
        ];
    }

    protected function mockKpiSnapshot(string $range): array
    {
        return [
            'range' => $range,
            'current_period' => [Carbon::now()->subDays(6)->toDateString(), Carbon::now()->toDateString()],
            'previous_period' => [Carbon::now()->subDays(13)->toDateString(), Carbon::now()->subDays(7)->toDateString()],
            'overview' => [
                'sessions' => 3280,
                'events' => 16240,
                'conversions' => 432,
                'average_duration' => '14m 32s',
            ],
            'search' => $this->mockSearch(),
            'conversions' => $this->mockConversions(),
            'top_conversion_pages' => [
                ['screen' => 'provider.detail', 'total' => 650],
                ['screen' => 'booking.summary', 'total' => 420],
                ['screen' => 'offer.detail', 'total' => 310],
            ],
        ];
    }
}
