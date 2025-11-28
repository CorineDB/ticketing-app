<?php

namespace App\Jobs;

use App\Mail\ScanNotificationMail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendScanNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Ticket $ticket,
        public string $action,
        public array $scanData
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->ticket->buyer_email)
                ->send(new ScanNotificationMail($this->ticket, $this->action, $this->scanData));

            Log::info('Scan notification email sent successfully', [
                'ticket_id' => $this->ticket->id,
                'email' => $this->ticket->buyer_email,
                'action' => $this->action,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send scan notification email', [
                'ticket_id' => $this->ticket->id,
                'email' => $this->ticket->buyer_email,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}
