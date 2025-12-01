<?php

namespace App\Repositories;

use App\DTO\EventDTO;
use App\Models\Event;
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
}
