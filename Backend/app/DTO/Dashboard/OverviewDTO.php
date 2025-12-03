<?php

namespace App\DTO\Dashboard;

class OverviewDTO
{
    public function __construct(
        public readonly array $kpis,
        public readonly array $activity,
        public readonly array $realtime,
        public readonly ?array $comparison = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'kpis' => $this->kpis,
            'activity' => $this->activity,
            'realtime' => $this->realtime,
            'comparison' => $this->comparison,
        ];
    }
}
