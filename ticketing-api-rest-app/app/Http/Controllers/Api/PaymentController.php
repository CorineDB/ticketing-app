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
        ]);

        $status = $request->query('status');
        $transactionId = $request->query('id') ?? $request->query('transaction_id');

        $frontendUrl = config('app.frontend_url', env('CLIENT_APP_URL', 'http://localhost:5173'));

        // Récupérer le payment via transaction_id
        $payment = \App\Models\Payment::where('fedapay_transaction_id', $transactionId)->first();

        if ($status === 'approved' && $payment) {
            // ✅ Redirection vers page de tickets avec payment_id
            $redirectUrl = $frontendUrl . '/purchase/' . $payment->id . '?status=success';
        } else {
            // ❌ Redirection vers page d'erreur
            $redirectUrl = $frontendUrl . '/payment/result?' . http_build_query([
                'status' => $status,
                'transaction_id' => $transactionId,
            ]);
        }

        Log::info('Redirecting to frontend', [
            'redirect_url' => $redirectUrl,
            'payment_id' => $payment->id ?? null,
        ]);

        return redirect()->away($redirectUrl);
    }

    /**
     * Get payment details with tickets
     */
    public function show(string $paymentId)
    {
        $payment = \App\Models\Payment::with(['tickets.ticketType', 'event'])
            ->findOrFail($paymentId);

        return response()->json([
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at,
                'event' => [
                    'title' => $payment->event_title,
                    'start_date' => $payment->event_start_date,
                    'end_date' => $payment->event_end_date,
                    'location' => $payment->event_location,
                ],
                'customer' => [
                    'firstname' => $payment->customer_firstname,
                    'lastname' => $payment->customer_lastname,
                    'email' => $payment->customer_email,
                ],
                'ticket_count' => $payment->ticket_count,
                'ticket_types_summary' => $payment->ticket_types_summary,
            ],
            'tickets' => $payment->tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'code' => $ticket->code,
                    'status' => $ticket->status,
                    'magic_link_token' => $ticket->magic_link_token,
                    'qr_data' => $ticket->qr_data,
                    'ticket_type' => [
                        'id' => $ticket->ticketType->id,
                        'name' => $ticket->ticketType->name,
                        'price' => $ticket->ticketType->price,
                    ],
                ];
            }),
        ]);
    }
}
