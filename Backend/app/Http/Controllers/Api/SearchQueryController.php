<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SearchQuery;
use Illuminate\Http\Request;

class SearchQueryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|uuid',
            'query' => 'required|string|max:255',
            'result_count' => 'nullable|integer|min:0',
            'timestamp' => 'required|date',
        ]);

        $query = SearchQuery::create($data);

        return response()->json([
            'status' => 'opgeslagen',
            'id' => $query->id,
        ]);
    }
}
