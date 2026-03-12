<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [HomeController::class, 'products'])->name('products.json');
Route::get('/analytics', [HomeController::class, 'analytics'])->name('analytics');
Route::get('/ai-analysis', [HomeController::class, 'aiAnalysis'])->name('ai.analysis');
