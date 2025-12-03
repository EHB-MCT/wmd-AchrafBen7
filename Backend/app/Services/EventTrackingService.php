<?php

namespace App\Services;

use App\DTO\EventDTO;
use App\Models\Event;
use App\Repositories\EventRepository;
use Illuminate\Support\Collection;

class EventTrackingService
{
    public function __construct(
        protected EventRepository $eventRepository,
    ) {
    }

    public function track(EventDTO $dto): Event
    {
        return $this->eventRepository->create($dto);
    }

    /**
     * @param  array<int, EventDTO>  $events
     */
    public function trackBatch(array $events): Collection
    {
        return $this->eventRepository->createMany($events);
    }

    public function process(Event $event): void
    {
        // Hook for streaming analytics/alerts; implement when engine available.
    }
}
