<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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

        return response()->json(['user_id' => $user->id]);
    }
}
