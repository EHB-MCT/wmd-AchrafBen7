<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FrontendSignalController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'action' => 'required|string|max:100',
            'timestamp' => 'nullable|date',
        ]);

        $cache = Cache::store('file');
        $count = $cache->increment('frontend_signal_count');
        $cache->forever('frontend_signal_last', [
            'action' => $request->input('action'),
            'timestamp' => $request->input('timestamp'),
        ]);

        return response()->json([
            'count' => $count,
        ]);
    }

    public function show()
    {
        $cache = Cache::store('file');

        return response()->json([
            'count' => (int) $cache->get('frontend_signal_count', 0),
            'last' => $cache->get('frontend_signal_last'),
        ]);
    }
}
