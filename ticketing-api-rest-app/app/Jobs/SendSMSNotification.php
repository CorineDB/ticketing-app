<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSMSNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $phoneNumber,
        public string $message
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // TODO: Integrate with SMS provider (Twilio, Vonage, etc.)
            // For now, just log the SMS
            Log::info('SMS notification (simulated)', [
                'phone_number' => $this->phoneNumber,
                'message' => $this->message,
            ]);

            // Example integration with Twilio (commented out):
            // $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
            // $twilio->messages->create($this->phoneNumber, [
            //     'from' => config('services.twilio.from'),
            //     'body' => $this->message
            // ]);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'phone_number' => $this->phoneNumber,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}
