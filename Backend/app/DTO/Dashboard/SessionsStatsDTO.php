<?php

namespace App\DTO\Dashboard;

class SessionsStatsDTO
{
    public function __construct(
        public readonly array $totals,
        public readonly array $platforms,
        public readonly array $timeline,
        public readonly array $recent,
    ) {
    }

    public function toArray(): array
    {
        return [
            'totals' => $this->totals,
            'platforms' => $this->platforms,
            'timeline' => $this->timeline,
            'recent' => $this->recent,
        ];
    }
}
