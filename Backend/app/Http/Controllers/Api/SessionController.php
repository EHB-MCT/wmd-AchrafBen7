<?php

namespace App\Http\Controllers\Api;

use App\DTO\SessionEndDTO;
use App\DTO\SessionStartDTO;
use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(private AnalyticsService $analytics)
    {
    }

    public function start(Request $request)
    {
        $dto = new SessionStartDTO($request->validate([
            'user_id' => 'required|uuid',
            'platform' => 'required|string|max:50',
            'network_type' => 'nullable|string|max:50',
            'battery_level' => 'nullable|integer|min:0|max:100',
        ]));

        $session = $this->analytics->recordSessionStart($dto);

        return response()->json([
            'status' => 'gestart',
            'session' => $session,
        ]);
    }

    public function end(Request $request)
    {
        $dto = new SessionEndDTO($request->validate([
            'session_id' => 'required|uuid',
            'duration_seconds' => 'required|integer|min:1',
        ]));

        $this->analytics->recordSessionEnd($dto);

        return response()->json(['status' => 'afgesloten']);
    }
}
