<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ScraperController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Scraper routes
Route::post('/scrape', [ScraperController::class, 'scrape']);
Route::get('/analytics', [ScraperController::class, 'analytics']);

// Product API routes (for CRM)
Route::apiResource('products', ProductController::class);