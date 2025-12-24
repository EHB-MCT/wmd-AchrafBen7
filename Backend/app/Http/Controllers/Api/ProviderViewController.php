<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderView;
use Illuminate\Http\Request;

class ProviderViewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|uuid',
            'provider_id' => 'nullable|string|max:255',
            'view_duration' => 'required|integer|min:1',
            'timestamp' => 'nullable|date',
        ]);

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
}
