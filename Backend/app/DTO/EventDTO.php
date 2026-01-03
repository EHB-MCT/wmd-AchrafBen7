<?php

namespace App\DTO;

use DateTimeInterface;

class EventDTO
{
    public readonly string $sessionId;
    public readonly ?string $userId;
    public readonly string $type;
    public readonly string $name;
    public readonly array $value;
    public readonly ?float $deviceX;
    public readonly ?float $deviceY;
    public readonly DateTimeInterface|string|null $timestamp;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes)
    {
        $this->sessionId = (string) ($attributes['session_id'] ?? '');
        $this->userId = $attributes['user_id'] ?? null;
        $this->type = (string) ($attributes['type'] ?? 'custom');
        $this->name = (string) ($attributes['name'] ?? 'event');
        $this->value = $attributes['value'] ?? [];
        $this->deviceX = isset($attributes['device_x']) ? (float) $attributes['device_x'] : null;
        $this->deviceY = isset($attributes['device_y']) ? (float) $attributes['device_y'] : null;
        $this->timestamp = $attributes['timestamp'] ?? null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self($attributes);
    }

    public function toArray(): array
    {
        $payload = [
            'session_id' => $this->sessionId,
            'user_id' => $this->userId,
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
            'device_x' => $this->deviceX,
            'device_y' => $this->deviceY,
            'timestamp' => $this->timestamp instanceof DateTimeInterface
                ? $this->timestamp->format(DateTimeInterface::ATOM)
                : $this->timestamp,
        ];

        return array_filter(
            $payload,
            static fn ($value) => $value !== null
        );
    }
}
