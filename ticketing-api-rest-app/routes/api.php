<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\GateController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketTypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::prefix('auths')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/otp/request', [AuthController::class, 'requestOtp']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});

// Public routes (no authentication required)
Route::prefix('public')->group(function () {
    // Events
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::get('/events/slug/{slug}', [EventController::class, 'showBySlug']);
    Route::get('/events/{id}/ticket-types', [TicketTypeController::class, 'index']);

    // Tickets with magic link
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::get('/tickets/{id}/qr', [TicketController::class, 'qr']);
    Route::get('/tickets/{id}/qr/download', [TicketController::class, 'downloadQr']);

    Route::get('/ticket-types/{id}', [TicketTypeController::class, 'show']);
});

// Scan endpoints (public request, authenticated confirm)
// Rate limiting: 60 requests per minute per IP for scan requests
Route::post('/scan/request', [ScanController::class, 'request'])
    ->middleware('throttle:scan-request');

// Rate limiting: 30 requests per minute per user for scan confirmations
Route::post('/scan/confirm', [ScanController::class, 'confirm'])
    ->middleware(['auth:sanctum', 'throttle:scan-confirm']);

// Webhooks (public, no authentication)
Route::post('/webhooks/fedapay', [WebhookController::class, 'fedapayWebhook']);

// Payment callback (public, no authentication - FedaPay redirects users here)
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// Ticket purchase (public - no authentication required to buy tickets)
Route::post('/tickets/purchase', [TicketController::class, 'purchase']);

// Event by slug (public - must be BEFORE apiResource to avoid conflict with {id})
Route::get('/events/slug/{slug}', [EventController::class, 'showBySlug']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Roles
    Route::apiResource('roles', RoleController::class);

    Route::apiResource('users', UserController::class);
    Route::post('/organizers', [UserController::class, 'storeOrganizer']);

    // Events
    Route::apiResource('events', EventController::class);
    Route::get('/events/{id}/stats', [EventController::class, 'stats']);

    // Ticket Types
    Route::get('/events/{eventId}/ticket-types', [TicketTypeController::class, 'index']);
    Route::post('/events/{eventId}/ticket-types', [TicketTypeController::class, 'store']);
    Route::get('/ticket-types/{id}', [TicketTypeController::class, 'show']);
    Route::put('/ticket-types/{id}', [TicketTypeController::class, 'update']);
    Route::delete('/ticket-types/{id}', [TicketTypeController::class, 'destroy']);

    // Tickets
    Route::apiResource('tickets', TicketController::class);
    Route::get('/tickets/{id}/qr', [TicketController::class, 'qr']);
    Route::get('/tickets/{id}/qr/download', [TicketController::class, 'downloadQr']);
    Route::post('/tickets/{id}/mark-paid', [TicketController::class, 'markPaid']);

    // Gates
    Route::apiResource('gates', GateController::class);
});
