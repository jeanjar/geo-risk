<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RiskSummaryController;
use Illuminate\Support\Facades\Route;

Route::get('/events/geojson', [EventController::class, 'geojson']);
Route::get('/risk-summary', [RiskSummaryController::class, 'generate'])->middleware('throttle:5,1');
Route::get('/risk-summary/models', [RiskSummaryController::class, 'models']);
