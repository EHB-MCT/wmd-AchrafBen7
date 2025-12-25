<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insight;

class InsightController extends Controller
{
    public function userInsights(string $id)
    {
        $insights = Insight::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($insights);
    }
}
