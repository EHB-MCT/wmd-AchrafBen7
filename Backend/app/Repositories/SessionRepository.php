<?php

namespace App\Repositories;

use App\DTO\SessionEndDTO;
use App\DTO\SessionStartDTO;
use App\Models\UserSession;
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
}
