<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserIdentityController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'uid' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:100',
            'os_version' => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:50',
        ]);

        $user = User::firstOrNew(['uid' => $data['uid']]);

        if (! $user->exists) {
            $user->first_seen_at = now();
        }

        $user->fill($data);
        $user->last_seen_at = now();
        $user->save();

        $recentSession = UserSession::where('user_id', $user->id)
            ->where('start_time', '>=', now()->subMinutes(5))
            ->latest('start_time')
            ->first();

        if (! $recentSession) {
            $recentSession = UserSession::create([
                'user_id' => $user->id,
                'start_time' => now(),
                'platform' => 'web',
            ]);
        }

        $recentEvent = Event::where('user_id', $user->id)
            ->where('name', 'frontend.identify')
            ->where('timestamp', '>=', now()->subSeconds(30))
            ->first();

        if (! $recentEvent) {
            Event::create([
                'session_id' => $recentSession->id,
                'user_id' => $user->id,
                'type' => 'view',
                'name' => 'frontend.identify',
                'value' => ['source' => 'identify'],
                'timestamp' => Carbon::now()->toAtomString(),
                'created_at' => Carbon::now()->toAtomString(),
            ]);
        }

        return response()->json([
            'user_id' => $user->id,
            'session_id' => $recentSession->id,
        ]);
    }
}
