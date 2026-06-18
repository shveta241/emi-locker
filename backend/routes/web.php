<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\DashboardController;

// Admin Dashboard UI
Route::get('/', [DashboardController::class, 'index']);

// DPC Client and Webhook API Endpoints
Route::prefix('api')->group(function () {
    // Device management
    Route::post('/device/register', [DeviceController::class, 'register']);
    Route::post('/device/heartbeat', [DeviceController::class, 'heartbeat']);
    Route::post('/device/lock-status', [DeviceController::class, 'lockStatus']);

    // Payment processors
    Route::post('/payment/webhook', [PaymentController::class, 'webhook']);
    Route::post('/payment/mock-trigger', [PaymentController::class, 'mockTrigger']);
    
    // Dashboard APIs
    Route::prefix('dashboard')->group(function () {
        Route::get('/data', [DashboardController::class, 'getDashboardData']);
        Route::post('/device/register', [DashboardController::class, 'registerMockDevice']);
        Route::post('/device/{id}/lock', [DashboardController::class, 'lockDevice']);
        Route::post('/device/{id}/unlock', [DashboardController::class, 'unlockDevice']);
    });
});

