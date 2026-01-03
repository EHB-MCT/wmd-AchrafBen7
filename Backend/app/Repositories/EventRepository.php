<?php

namespace App\Repositories;

use App\DTO\EventDTO;
use App\Models\Event;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EventRepository
{
    public function create(EventDTO $dto): Event
    {
        return Event::create($dto->toArray());
    }

    /**
     * @param  array<int, EventDTO>  $dtos
     */
    public function createMany(array $dtos): Collection
    {
        return collect($dtos)
            ->filter(static fn ($dto) => $dto instanceof EventDTO)
            ->map(fn (EventDTO $dto) => $this->create($dto))
            ->values();
    }

    public function findById(string $id): ?Event
    {
        return Event::find($id);
    }

    public function getBySession(string $sessionId): Collection
    {
        return Event::where('session_id', $sessionId)->get();
    }

    public function countSince(Carbon $start): int
    {
        return $this->dateScopedQuery($start)->count();
    }

    public function countBetween(Carbon $start, Carbon $end): int
    {
        return $this->dateScopedQuery($start, $end)->count();
    }

    public function countByType(string $type, ?Carbon $start = null, ?Carbon $end = null): int
    {
        $query = Event::query()->where('type', $type);

        if ($start) {
            $query = $this->dateScopedQuery($start, $end, $query);
        }

        return (int) $query->count();
    }

    public function latest(int $limit = 8): Collection
    {
        return Event::orderByDesc('timestamp')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function topEvents(int $limit = 5, ?Carbon $start = null, ?Carbon $end = null): Collection
    {
        [$from, $to] = $start
            ? $this->normalizeRange($start, $end)
            : [null, null];

        return Event::select('name', 'type')
            ->selectRaw('COUNT(*) as total')
            ->when($from, fn (Builder $query) => $query->whereBetween('timestamp', [$from, $to]))
            ->groupBy('name', 'type')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($event) => [
                'name' => $event->name,
                'type' => $event->type,
                'total' => (int) $event->total,
            ]);
    }

    public function dailyCounts(Carbon $start, Carbon $end): array
    {
        [$from, $to] = $this->normalizeRange($start, $end);

        $rows = Event::selectRaw('DATE(timestamp) as day, COUNT(*) as total')
            ->whereBetween('timestamp', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->map(fn ($total) => (int) $total)
            ->all();

        return $this->fillMissingDates($rows, $from, $to);
    }

    protected function dateScopedQuery(
        Carbon $start,
        ?Carbon $end = null,
        ?Builder $builder = null,
    ): Builder {
        [$from, $to] = $this->normalizeRange($start, $end);

        $builder ??= Event::query();

        return $builder->whereBetween('timestamp', [$from, $to]);
    }

    protected function normalizeRange(Carbon $start, ?Carbon $end = null): array
    {
        return [
            $start->copy()->startOfDay(),
            ($end ?? Carbon::now())->copy()->endOfDay(),
        ];
    }

    protected function fillMissingDates(array $rows, Carbon $start, Carbon $end): array
    {
        $series = [];
        $period = new CarbonPeriod($start, '1 day', $end);

        foreach ($period as $day) {
            $key = $day->toDateString();
            $series[$key] = (int) ($rows[$key] ?? 0);
        }

        return $series;
    }
}
