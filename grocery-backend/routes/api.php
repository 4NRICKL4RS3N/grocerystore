<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\AnalyticsController;

Route::get('/health', fn () => ['status' => 'ok']);

Route::apiResource('products', ProductController::class);

Route::get('products/{product}/stock', [StockMovementController::class, 'index']);
Route::post('products/{product}/stock/in', [StockMovementController::class, 'storeIn']);
Route::post('products/{product}/stock/out', [StockMovementController::class, 'storeOut']);

Route::get('sales', [SaleController::class, 'index']);
Route::post('sales', [SaleController::class, 'store']);
Route::get('sales/{sale}', [SaleController::class, 'show']);

Route::get('analytics/top-sellers', [AnalyticsController::class, 'topSellers']);
Route::get('analytics/slow-movers', [AnalyticsController::class, 'slowMovers']);
Route::get('analytics/sales-by-hour', [AnalyticsController::class, 'salesByHour']);
Route::get('analytics/summary', [AnalyticsController::class, 'summary']);