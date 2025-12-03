<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderView;

class ProviderViewController extends Controller
{
    public function index()
    {
        return ProviderView::orderBy('view_count', 'desc')->get();
    }
}
