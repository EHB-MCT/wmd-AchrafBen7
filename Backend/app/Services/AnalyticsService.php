<?php

namespace App\Services;

use App\DTO\EventDTO;
use App\DTO\SessionEndDTO;
use App\DTO\SessionStartDTO;
use App\Models\Event;
use App\Models\UserSession;
use App\Repositories\EventRepository;
use App\Repositories\SessionRepository;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function __construct(
        protected EventRepository $eventRepository,
        protected SessionRepository $sessionRepository,
    ) {
    }

    public function recordSessionStart(SessionStartDTO $dto): UserSession
    {
        return $this->sessionRepository->start($dto);
    }

    public function recordSessionEnd(SessionEndDTO $dto): ?UserSession
    {
        return $this->sessionRepository->end($dto);
    }

    public function startSession(SessionStartDTO $dto): UserSession
    {
        return $this->recordSessionStart($dto);
    }

    public function endSession(SessionEndDTO $dto): ?UserSession
    {
        return $this->recordSessionEnd($dto);
    }

    public function recordEvent(EventDTO $dto): Event
    {
        return $this->eventRepository->create($dto);
    }

    /**
     * @param  array<int, EventDTO>  $events
     */
    public function recordEvents(array $events): Collection
    {
        return $this->eventRepository->createMany($events);
    }

    public function eventsForSession(string $sessionId): Collection
    {
        return $this->eventRepository->getBySession($sessionId);
    }
}
