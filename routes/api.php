<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PaymentCallbackController;
use Illuminate\Support\Facades\Route;

// Public API Routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Payment Callbacks (webhook endpoints)
Route::post('/callback/midtrans', [PaymentCallbackController::class, 'midtrans']);
Route::post('/callback/xendit', [PaymentCallbackController::class, 'xendit']);
Route::post('/callback/tripay', [PaymentCallbackController::class, 'tripay']);

// Protected API Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/customer/me', [CustomerController::class, 'me']);
    Route::get('/customer/balance', [CustomerController::class, 'balance']);
    Route::get('/customer/active-plan', [CustomerController::class, 'activePlan']);
    Route::get('/customer/plans', [CustomerController::class, 'plans']);
    Route::get('/customer/transactions', [CustomerController::class, 'transactions']);
});
