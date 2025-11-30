<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Handle payment callback from FedaPay
     * This is where users are redirected after completing payment
     */
    public function callback(Request $request)
    {
        Log::info('FedaPay payment callback received', [
            'query_params' => $request->query(),
            'all_params' => $request->all(),
        ]);

        // Extract common FedaPay callback parameters
        $status = $request->query('status');
        $transactionId = $request->query('id') ?? $request->query('transaction_id');
        $reference = $request->query('reference');

        // Get frontend URL from config or environment
        $frontendUrl = config('app.frontend_url', env('CLIENT_APP_URL', 'http://localhost:5173'));

        // Build redirect URL with payment status
        $redirectUrl = $frontendUrl . '/payment/result?' . http_build_query([
            'status' => $status,
            'transaction_id' => $transactionId,
            'reference' => $reference,
        ]);

        Log::info('Redirecting to frontend', [
            'redirect_url' => $redirectUrl,
            'status' => $status,
        ]);

        // 3. Rediriger l'utilisateur vers le FRONTEND
        return redirect()->away($redirectUrl);
    }
}
