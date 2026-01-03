<?php

namespace App\Repositories;

use App\DTO\SessionEndDTO;
use App\DTO\SessionStartDTO;
use App\Models\UserSession;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SessionRepository
{
    public function start(SessionStartDTO $dto): UserSession
    {
        return UserSession::create($dto->toArray());
    }

    public function end(SessionEndDTO $dto): ?UserSession
    {
        $session = $this->findById($dto->getSessionId());

        if (! $session) {
            return null;
        }

        $session->fill($dto->toArray());
        $session->save();

        return $session->refresh();
    }

    public function findById(string $sessionId): ?UserSession
    {
        return UserSession::find($sessionId);
    }

    public function getByUser(string $userId): Collection
    {
        return UserSession::where('user_id', $userId)
            ->latest('start_time')
            ->get();
    }

    public function countSince(Carbon $start): int
    {
        return UserSession::where('start_time', '>=', $start)->count();
    }

    public function countBetween(Carbon $start, Carbon $end): int
    {
        return UserSession::whereBetween('start_time', [
            $start->copy()->startOfDay(),
            $end->copy()->endOfDay(),
        ])->count();
    }

    public function averageDuration(?Carbon $start = null, ?Carbon $end = null): float
    {
        $query = UserSession::query()->whereNotNull('duration_seconds');

        if ($start && $end) {
            $query->whereBetween('start_time', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
        } elseif ($start) {
            $query->where('start_time', '>=', $start->copy()->startOfDay());
        }

        return (float) ($query->avg('duration_seconds') ?? 0.0);
    }

    public function dailyCounts(Carbon $start, Carbon $end): array
    {
        $rows = UserSession::selectRaw('DATE(start_time) as day, COUNT(*) as total')
            ->whereBetween('start_time', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->map(fn ($total) => (int) $total)
            ->all();

        $series = [];
        $period = new CarbonPeriod($start->copy()->startOfDay(), '1 day', $end->copy()->startOfDay());

        foreach ($period as $day) {
            $key = $day->toDateString();
            $series[$key] = (int) ($rows[$key] ?? 0);
        }

        return $series;
    }

    public function platformBreakdown(Carbon $start, Carbon $end): array
    {
        return UserSession::select('platform')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('start_time', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->groupBy('platform')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->platform ?? 'Unknown' => (int) $row->total])
            ->all();
    }

    public function recent(int $limit = 6): Collection
    {
        return UserSession::with('user')
            ->latest('start_time')
            ->limit($limit)
            ->get();
    }
}
