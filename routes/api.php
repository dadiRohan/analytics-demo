<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\EventIngestionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['analytics.token:ingest', 'throttle:120,1'])
    ->post('/events', [EventIngestionController::class, 'ingest']);

Route::middleware(['analytics.token:read'])
    ->prefix('analytics')
    ->group(function () {
        Route::get('/overview',      [AnalyticsController::class, 'overview']);
        Route::get('/trend',         [AnalyticsController::class, 'trend']);
        Route::get('/popular-pages', [AnalyticsController::class, 'popularPages']);
        Route::get('/events',        [AnalyticsController::class, 'eventBreakdown']);
    });