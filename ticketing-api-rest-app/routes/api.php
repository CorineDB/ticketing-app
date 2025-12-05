<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\GateController;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketTypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\BroadcastTestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;



// --- Route de diagnostic réseau SMTP ---
// Accessible via : https://votre-app-url.up.railway.app/test-connectivity
Route::get('/test-connectivity', function () {
    $output = "<h1>🔍 Diagnostic Réseau SMTP (Depuis Railway)</h1><pre style='background:#f4f4f4;padding:15px;border-radius:5px;'>";

    // 1. Test DNS
    $output .= "<strong>1. TEST DNS</strong>\n";
    $host = 'smtp.titan.email';
    $ip = gethostbyname($host);
    if ($ip == $host) {
        $output .= "❌ ÉCHEC DNS : Impossible de résoudre $host\n";
    } else {
        $output .= "✅ SUCCÈS DNS : $host = $ip\n";
    }
    $output .= "---------------------------------------------------\n\n";

    // 2. Tests de Ports
    $output .= "<strong>2. TEST DE CONNECTIVITÉ (Ports)</strong>\n";
    $tests = [
        ['host' => 'smtp.titan.email', 'port' => 465, 'name' => 'Titan SMTP (SSL/465)'],
        ['host' => 'smtp.titan.email', 'port' => 587, 'name' => 'Titan SMTP (TLS/587)'],
        ['host' => 'google.com', 'port' => 443, 'name' => 'Témoin : Google (HTTPS)'],
        ['host' => 'smtp.resend.com', 'port' => 465, 'name' => 'Témoin : Resend SMTP'],
        ['host' => 'smtp.mailgun.org', 'port' => 587, 'name' => 'Témoin : Mailgun SMTP'],
    ];

    foreach ($tests as $test) {
        $output .= "Test vers {$test['name']}... ";

        $start = microtime(true);
        // Timeout de 3 secondes pour ne pas bloquer la page trop longtemps
        $fp = @fsockopen($test['host'], $test['port'], $errno, $errstr, 3);
        $end = microtime(true);
        $duration = round(($end - $start) * 1000, 2);

        if ($fp) {
            $output .= "<span style='color:green'>✅ CONNECTÉ</span> ({$duration} ms)\n";
            fclose($fp);
        } else {
            $output .= "<span style='color:red'>❌ ÉCHEC</span> ($errstr - Code $errno)\n";
        }
    }

    $output .= "</pre>";
    $output .= "<h2>📊 Analyse :</h2>";
    $output .= "<ul>";
    $output .= "<li>Si <strong>Google/Resend/Mailgun</strong> sont ✅ verts mais <strong>Titan</strong> est ❌ rouge : <br><code>Titan bloque l'adresse IP de Railway</code></li>";
    $output .= "<li>Si tout est ❌ rouge : Problème de connectivité internet du conteneur (rare)</li>";
    $output .= "<li><strong>Solution :</strong> Utilisez <code>MAIL_MAILER=log</code> temporairement ou passez à Resend/Mailgun/SendGrid</li>";
    $output .= "</ul>";

    return $output;
});

// Health check endpoint
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'timestamp' => now()->toIso8601String()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'database' => 'disconnected',
            'error' => $e->getMessage(),
            'timestamp' => now()->toIso8601String()
        ], 503);
    }
});

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/otp/request', [AuthController::class, 'requestOtp']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/me', [AuthController::class, 'updateProfile']);
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

// Public Scan Request (Rate limited)
Route::post('/scan/request', [ScanController::class, 'request'])
    ->middleware('throttle:scan-request');

// Webhooks (public, no authentication)
Route::post('/webhooks/fedapay', [WebhookController::class, 'fedapayWebhook']);

// Payment callback (public, no authentication - FedaPay redirects users here)
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// Payment details (public - for purchase success page)
Route::get('/payments/{id}', [PaymentController::class, 'show']);

// Ticket purchase (public - no authentication required to buy tickets)
Route::post('/tickets/purchase', [TicketController::class, 'purchase']);

// Event by slug (public - must be BEFORE apiResource to avoid conflict with {id})
Route::get('/events/slug/{slug}', [EventController::class, 'showBySlug']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/organizer', [DashboardController::class, 'organizer']);
        Route::get('/super-admin', [DashboardController::class, 'superAdmin']);
        Route::get('/scanner', [DashboardController::class, 'scanner']);
        Route::get('/participant', [DashboardController::class, 'participant']);
        Route::get('/cashier', [DashboardController::class, 'cashier']);
    });

    // Roles
    Route::apiResource('roles', RoleController::class);

    Route::apiResource('users', UserController::class);
    Route::post('/organizers', [UserController::class, 'storeOrganizer']);

    // Events
    Route::apiResource('events', EventController::class);
    Route::patch('/events/{id}/publish', [EventController::class, 'publish']);
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
    Route::post('/tickets/{id}/send-email', [TicketController::class, 'sendTicketByEmail']);
    Route::post('/tickets/{id}/regenerate-qr', [TicketController::class, 'regenerateCode']);
    Route::get('/tickets/{id}/qr/download', [TicketController::class, 'downloadQr']);
    Route::post('/tickets/{id}/mark-paid', [TicketController::class, 'markPaid']);

    // Gates
    Route::apiResource('gates', GateController::class);

    // Agents
    Route::get('agents', [AgentController::class, 'index']);
    Route::post('agents', [AgentController::class, 'store']);
    Route::get('agents/{id}', [AgentController::class, 'show']);
    Route::patch('agents/{id}/status', [AgentController::class, 'updateStatus']);
    Route::delete('agents/{id}', [AgentController::class, 'destroy']);

    // Scan Protected Endpoints
    Route::prefix('scans')->group(function () {
        Route::post('/confirm', [ScanController::class, 'confirm'])
            ->middleware('throttle:scan-confirm');
        Route::get('/history', [ScanController::class, 'history']);
    });

    // Broadcast Test Endpoints (for development/testing)
    Route::prefix('broadcast/test')->group(function () {
        Route::post('/ticket-purchased', [BroadcastTestController::class, 'testTicketPurchased']);
        Route::post('/ticket-scanned', [BroadcastTestController::class, 'testTicketScanned']);
        Route::post('/capacity-alert', [BroadcastTestController::class, 'testCapacityAlert']);
        Route::post('/all', [BroadcastTestController::class, 'testAll']);
    });
});