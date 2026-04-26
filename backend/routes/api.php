<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\HelpOfferController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RatingController;

// --- Routes Publiques ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// --- Routes Protégées ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Requests
    Route::get('/requests/history', [RequestController::class, 'history']);
    Route::get('/requests', [RequestController::class, 'index']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests/{id}', [RequestController::class, 'show']);
    Route::put('/requests/{id}', [RequestController::class, 'update']);
    Route::delete('/requests/{id}', [RequestController::class, 'destroy']);
    Route::post('/requests/{id}/complete', [RequestController::class, 'markCompleted']);
    Route::patch('/requests/{id}/status', [RequestController::class, 'updateStatus']);

    // Offers
    Route::post('/offers/{requestId}', [HelpOfferController::class, 'store']);
    Route::post('/offers/{id}/accept', [HelpOfferController::class, 'accept']);
    Route::post('/offers/{id}/reject', [HelpOfferController::class, 'reject']);

    // Messages
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{requestId}', [MessageController::class, 'index']);

    // Ratings
    Route::post('/ratings', [RatingController::class, 'store']);
    Route::get('/ratings/received', [RatingController::class, 'received']);
});
