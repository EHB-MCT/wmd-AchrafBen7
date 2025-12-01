<?php

namespace App\DTO;

use DateTimeInterface;
use Illuminate\Support\Carbon;

class SessionEndDTO
{
    public readonly string $sessionId;
    public readonly DateTimeInterface|string|null $endTime;
    public readonly ?int $durationSeconds;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes)
    {
        $this->sessionId = (string) ($attributes['session_id'] ?? '');
        $this->endTime = $attributes['end_time'] ?? null;
        $this->durationSeconds = isset($attributes['duration_seconds'])
            ? (int) $attributes['duration_seconds']
            : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self($attributes);
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function toArray(): array
    {
        $endTime = $this->endTime instanceof DateTimeInterface
            ? $this->endTime->format(DateTimeInterface::ATOM)
            : $this->endTime;

        $payload = [
            'end_time' => $endTime ?? Carbon::now()->toAtomString(),
            'duration_seconds' => $this->durationSeconds,
        ];

        return array_filter(
            $payload,
            static fn ($value) => $value !== null
        );
    }
}
