<?php

namespace App\DTO\Dashboard;

class EventsStatsDTO
{
    public function __construct(
        public readonly array $totals,
        public readonly array $timeline,
        public readonly array $topEvents,
        public readonly ?array $comparison = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'totals' => $this->totals,
            'timeline' => $this->timeline,
            'top_events' => $this->topEvents,
            'comparison' => $this->comparison,
        ];
    }
}
