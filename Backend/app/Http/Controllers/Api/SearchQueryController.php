<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SearchQuery;
use App\Models\User;
use Illuminate\Http\Request;

class SearchQueryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|uuid',
            'uid' => 'nullable|string|max:255',
            'query' => 'required|string|max:255',
            'result_count' => 'nullable|integer|min:0',
            'timestamp' => 'required|date',
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

        $query = SearchQuery::create($data);

        return response()->json([
            'status' => 'opgeslagen',
            'id' => $query->id,
        ]);
    }
}
