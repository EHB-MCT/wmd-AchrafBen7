<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', fn () => Inertia::render('Dashboard'));
Route::get('/sessions', fn () => Inertia::render('Sessions'));
Route::get('/events', fn () => Inertia::render('Events'));
Route::get('/search', fn () => Inertia::render('Search'));
Route::get('/conversions', fn () => Inertia::render('Conversions'));
Route::get('/timeline', fn () => Inertia::render('Timeline'));
Route::get('/heatmap', fn () => Inertia::render('Heatmap'));
Route::get('/settings', fn () => Inertia::render('Settings'));

Route::get('/api/stats/overview', [AnalyticsController::class, 'overview']);
Route::get('/api/stats/events', [AnalyticsController::class, 'events']);
Route::get('/api/stats/sessions', [AnalyticsController::class, 'sessions']);
Route::get('/api/stats/search', [AnalyticsController::class, 'search']);
Route::get('/api/stats/conversions', [AnalyticsController::class, 'conversions']);
Route::get('/api/stats/heatmap', [AnalyticsController::class, 'heatmap']);
Route::get('/api/stats/timeline', [AnalyticsController::class, 'timeline']);

Route::get('/api/export/kpis.csv', [ExportController::class, 'kpisCsv']);
Route::get('/api/export/kpis.pdf', [ExportController::class, 'kpisPdf']);
