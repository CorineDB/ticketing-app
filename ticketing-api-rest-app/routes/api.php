<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\GateController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketTypeController;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::prefix('public')->group(function () {
    // Events
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::get('/events/{id}/ticket-types', [TicketTypeController::class, 'index']);

    // Tickets with magic link
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::get('/tickets/{id}/qr', [TicketController::class, 'qr']);
    Route::get('/tickets/{id}/qr/download', [TicketController::class, 'downloadQr']);
});

// Scan endpoints (public request, authenticated confirm)
Route::post('/scan/request', [ScanController::class, 'request']);
Route::post('/scan/confirm', [ScanController::class, 'confirm'])->middleware('auth:sanctum');

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Roles
    Route::apiResource('roles', RoleController::class);

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
