<?php

namespace App\Jobs;

use App\Mail\TicketConfirmationMail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTicketEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Ticket $ticket
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->ticket->email)
                ->send(new TicketConfirmationMail($this->ticket));

            Log::info('Ticket confirmation email sent successfully', [
                'ticket_id' => $this->ticket->id,
                'email' => $this->ticket->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ticket confirmation email', [
                'ticket_id' => $this->ticket->id,
                'email' => $this->ticket->email,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}
