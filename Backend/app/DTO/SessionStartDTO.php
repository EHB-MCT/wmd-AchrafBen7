<?php

namespace App\DTO;

use DateTimeInterface;
use Illuminate\Support\Carbon;

class SessionStartDTO
{
    public readonly string $userId;
    public readonly DateTimeInterface|string|null $startTime;
    public readonly ?string $platform;
    public readonly ?string $networkType;
    public readonly ?int $batteryLevel;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes)
    {
        $this->userId = (string) ($attributes['user_id'] ?? '');
        $this->startTime = $attributes['start_time'] ?? null;
        $this->platform = $attributes['platform'] ?? null;
        $this->networkType = $attributes['network_type'] ?? null;
        $this->batteryLevel = isset($attributes['battery_level'])
            ? (int) $attributes['battery_level']
            : null;
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
        $startTime = $this->startTime instanceof DateTimeInterface
            ? $this->startTime->format(DateTimeInterface::ATOM)
            : $this->startTime;

        $payload = [
            'user_id' => $this->userId,
            'start_time' => $startTime ?? Carbon::now()->toAtomString(),
            'platform' => $this->platform,
            'network_type' => $this->networkType,
            'battery_level' => $this->batteryLevel,
        ];

        return array_filter(
            $payload,
            static fn ($value) => $value !== null
        );
    }
}
