<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderViewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|uuid',
            'uid' => 'nullable|string|max:255',
            'provider_id' => 'nullable|string|max:255',
            'view_duration' => 'required|integer|min:1',
            'timestamp' => 'nullable|date',
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

        $view = ProviderView::firstOrNew([
            'user_id' => $data['user_id'],
            'provider_id' => $data['provider_id'] ?? null,
        ]);

        if ($view->exists) {
            $count = max(0, (int) $view->view_count);
            $avg = max(0, (int) $view->avg_view_duration);
            $view->view_count = $count + 1;
            $view->avg_view_duration = (int) round((($avg * $count) + $data['view_duration']) / ($count + 1));
        } else {
            $view->view_count = 1;
            $view->avg_view_duration = $data['view_duration'];
        }

        $view->last_viewed_at = $data['timestamp'] ?? now();
        $view->save();

        return response()->json([
            'status' => 'opgeslagen',
            'id' => $view->id,
        ]);
    }

    public function index()
    {
        return ProviderView::orderBy('view_count', 'desc')->get();
    }

    public function top()
    {
        $top = ProviderView::select('provider_id', DB::raw('SUM(view_count) as total_views'))
            ->whereNotNull('provider_id')
            ->groupBy('provider_id')
            ->orderByDesc('total_views')
            ->first();

        return response()->json([
            'provider_id' => $top?->provider_id,
            'total_views' => (int) ($top->total_views ?? 0),
        ]);
    }
}
