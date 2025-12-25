<?php

namespace App\Http\Controllers\Api;

use App\DTO\SessionEndDTO;
use App\DTO\SessionStartDTO;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(private AnalyticsService $analytics)
    {
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|uuid',
            'uid' => 'nullable|string|max:255',
            'platform' => 'required|string|max:50',
            'network_type' => 'nullable|string|max:50',
            'battery_level' => 'nullable|integer|min:0|max:100',
        ]);

        if (empty($data['user_id']) && empty($data['uid'])) {
            return response()->json(['message' => 'user_id or uid required'], 422);
        }

        $user = null;
        if (! empty($data['user_id'])) {
            $user = User::find($data['user_id']);
        }

        if (! $user && ! empty($data['uid'])) {
            $user = User::firstOrCreate(
                ['uid' => $data['uid']],
                [
                    'first_seen_at' => now(),
                    'last_seen_at' => now(),
                ]
            );
        }

        if (! $user) {
            return response()->json(['message' => 'user not found'], 422);
        }

        $user->last_seen_at = now();
        $user->save();
        $data['user_id'] = $user->id;

        $dto = new SessionStartDTO($data);

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
