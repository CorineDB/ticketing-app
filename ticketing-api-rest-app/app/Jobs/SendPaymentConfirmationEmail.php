<?php

namespace App\Jobs;

use App\Mail\PaymentConfirmationMail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPaymentConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Ticket $ticket,
        public array $paymentData
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if buyer email exists
        if (empty($this->ticket->buyer_email)) {
            Log::warning('Cannot send payment confirmation email: buyer email is missing', [
                'ticket_id' => $this->ticket->id,
                'buyer_name' => $this->ticket->buyer_name,
            ]);
            return;
        }

        try {
            Mail::to($this->ticket->buyer_email)
                ->send(new PaymentConfirmationMail($this->ticket, $this->paymentData));

            Log::info('Payment confirmation email sent successfully', [
                'ticket_id' => $this->ticket->id,
                'email' => $this->ticket->buyer_email,
                'transaction_id' => $this->paymentData['transaction_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'ticket_id' => $this->ticket->id,
                'email' => $this->ticket->buyer_email,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}
