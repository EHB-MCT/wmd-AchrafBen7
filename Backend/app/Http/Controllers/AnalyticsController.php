<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsDashboardService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsDashboardService $service)
    {
    }

    public function overview(Request $request)
    {
        $range = (string) $request->query('range', '7d');
        $compare = $request->boolean('compare', false);

        return response()->json($this->service->overview($range, $compare)->toArray());
    }

    public function events(Request $request)
    {
        $range = (string) $request->query('range', '7d');
        $compare = $request->boolean('compare', false);

        return response()->json($this->service->events($range, $compare)->toArray());
    }

    public function sessions(Request $request)
    {
        $range = (string) $request->query('range', '7d');
        $compare = $request->boolean('compare', false);

        return response()->json($this->service->sessions($range, $compare)->toArray());
    }

    public function search(Request $request)
    {
        $range = (string) $request->query('range', '7d');

        return response()->json($this->service->search($range));
    }

    public function conversions(Request $request)
    {
        $range = (string) $request->query('range', '7d');

        return response()->json($this->service->conversions($range));
    }

    public function heatmap(Request $request)
    {
        $range = (string) $request->query('range', '24h');

        return response()->json($this->service->heatmap($range));
    }

    public function timeline(Request $request)
    {
        $range = (string) $request->query('range', '7d');
        $userId = $request->query('user_id');

        return response()->json($this->service->timeline($userId, $range));
    }
}
