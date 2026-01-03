<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
Route::get('/sessions', fn () => Inertia::render('Sessions'))->name('sessions');
Route::get('/events', fn () => Inertia::render('Events'))->name('events');
Route::get('/timeline', fn () => Inertia::render('Timeline'))->name('timeline');
Route::get('/search', fn () => Inertia::render('Search'))->name('search');
Route::get('/conversions', fn () => Inertia::render('Conversions'))->name('conversions');
Route::get('/heatmap', fn () => Inertia::render('Heatmap'))->name('heatmap');
Route::get('/settings', fn () => Inertia::render('Settings'))->name('settings');

Route::get('/api/stats/overview', [AnalyticsController::class, 'overview']);
Route::get('/api/stats/events', [AnalyticsController::class, 'events']);
Route::get('/api/stats/sessions', [AnalyticsController::class, 'sessions']);
Route::get('/api/stats/search', [AnalyticsController::class, 'search']);
Route::get('/api/stats/conversions', [AnalyticsController::class, 'conversions']);
Route::get('/api/stats/heatmap', [AnalyticsController::class, 'heatmap']);
Route::get('/api/stats/timeline', [AnalyticsController::class, 'timeline']);

Route::get('/api/export/kpis.csv', [ExportController::class, 'kpisCsv']);
Route::get('/api/export/kpis.pdf', [ExportController::class, 'kpisPdf']);
