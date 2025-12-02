<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\PaymentServiceContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected PaymentServiceContract $paymentService;

    public function __construct(PaymentServiceContract $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function fedapayWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-FedaPay-Signature');

        Log::info('FedaPay webhook received', [
            'has_signature' => !empty($signature),
            'payload_size' => strlen($payload),
        ]);

        // TODO: Re-enable webhook signature verification for production!
        // This is commented out for testing purposes as per user request.
        if (!$this->paymentService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('FedaPay webhook signature verification failed (TEMPORARILY DISABLED FOR TESTING)');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            $eventData = json_decode($payload, true);

            if (!$eventData) {
                Log::error('FedaPay webhook invalid JSON');
                return response()->json(['error' => 'Invalid JSON'], 400);
            }

            // Traiter l'événement
            $this->paymentService->handleWebhookEvent($eventData);

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('FedaPay webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
