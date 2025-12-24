<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FunnelController;
use App\Http\Controllers\Api\InsightController;
use App\Http\Controllers\Api\ProviderViewController;
use App\Http\Controllers\Api\SearchQueryController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\UserIdentityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('sessions')->group(function () {
    Route::post('/start', [SessionController::class, 'start']);
    Route::post('/end', [SessionController::class, 'end']);
});

Route::post('/users/identify', [UserIdentityController::class, 'store']);
Route::post('/events', [EventController::class, 'store']);
Route::post('/search-queries', [SearchQueryController::class, 'store']);
Route::post('/funnels', [FunnelController::class, 'store']);
Route::post('/provider-views', [ProviderViewController::class, 'store']);

Route::get('/users/{id}/insights', [InsightController::class, 'userInsights']);

Route::get('/funnels', [FunnelController::class, 'index']);
Route::get('/provider-views', [ProviderViewController::class, 'index']);
