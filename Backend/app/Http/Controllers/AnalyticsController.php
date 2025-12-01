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

        return response()->json($this->service->overview($range)->toArray());
    }

    public function events(Request $request)
    {
        $range = (string) $request->query('range', '7d');

        return response()->json($this->service->events($range)->toArray());
    }

    public function sessions(Request $request)
    {
        $range = (string) $request->query('range', '7d');

        return response()->json($this->service->sessions($range)->toArray());
    }
}
