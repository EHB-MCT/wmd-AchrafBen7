<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    abort(404);
});

Route::get('/api/stats/overview', [AnalyticsController::class, 'overview']);
Route::get('/api/stats/events', [AnalyticsController::class, 'events']);
Route::get('/api/stats/sessions', [AnalyticsController::class, 'sessions']);
Route::get('/api/stats/search', [AnalyticsController::class, 'search']);
Route::get('/api/stats/conversions', [AnalyticsController::class, 'conversions']);
Route::get('/api/stats/heatmap', [AnalyticsController::class, 'heatmap']);
Route::get('/api/stats/timeline', [AnalyticsController::class, 'timeline']);

Route::get('/api/export/kpis.csv', [ExportController::class, 'kpisCsv']);
Route::get('/api/export/kpis.pdf', [ExportController::class, 'kpisPdf']);
