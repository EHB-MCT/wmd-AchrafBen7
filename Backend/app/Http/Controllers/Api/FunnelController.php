<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Funnel;

class FunnelController extends Controller
{
    public function index()
    {
        return Funnel::orderBy('created_at', 'desc')->get();
    }
}
