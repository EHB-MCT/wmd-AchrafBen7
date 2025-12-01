<?php

namespace App\Http\Controllers\Api;

use App\DTO\EventDTO;
use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\EventTrackingService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics,
        private EventTrackingService $engine,
    ) {
    }

    public function store(Request $request)
    {
        $dto = new EventDTO($request->validate([
            'session_id' => 'required|uuid',
            'user_id' => 'nullable|uuid',
            'type' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'value' => 'nullable|array',
            'device_x' => 'nullable|integer',
            'device_y' => 'nullable|integer',
            'timestamp' => 'required|date',
        ]));

        $event = $this->analytics->recordEvent($dto);

        $this->engine->process($event);

        return response()->json([
            'status' => 'recorded',
            'event_id' => $event->id,
        ]);
    }
}
