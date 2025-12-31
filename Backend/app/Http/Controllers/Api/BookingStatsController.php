<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BookingStatsController extends Controller
{
    public function index()
    {
        $rows = DB::table('events')
            ->selectRaw("value->>'provider' as provider_id, COUNT(*) as total_bookings")
            ->where('type', 'conversion')
            ->where('name', 'like', 'book.%')
            ->whereRaw("jsonb_exists(value, 'provider')")
            ->groupBy('provider_id')
            ->orderByDesc('total_bookings')
            ->get();

        return response()->json($rows);
    }

    public function top()
    {
        $top = DB::table('events')
            ->selectRaw("value->>'provider' as provider_id, COUNT(*) as total_bookings")
            ->where('type', 'conversion')
            ->where('name', 'like', 'book.%')
            ->whereRaw("jsonb_exists(value, 'provider')")
            ->groupBy('provider_id')
            ->orderByDesc('total_bookings')
            ->first();

        return response()->json([
            'provider_id' => $top?->provider_id,
            'total_bookings' => (int) ($top->total_bookings ?? 0),
        ]);
    }
}
