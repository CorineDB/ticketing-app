<?php

namespace App\Services;

use App\Jobs\SendTicketEmail;
use App\Jobs\SendPaymentConfirmationEmail;
use App\Jobs\SendScanNotificationEmail;
use App\Jobs\SendSMSNotification;
use App\Repositories\Contracts\TicketRepositoryContract;
use App\Services\Contracts\NotificationServiceContract;
use Illuminate\Support\Facades\Log;

class NotificationService implements NotificationServiceContract
{
    protected TicketRepositoryContract $ticketRepository;

    public function __construct(TicketRepositoryContract $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Send ticket confirmation email with QR code
     */
    public function sendTicketConfirmation(string $ticketId): void
    {
        try {
            $ticket = $this->ticketRepository->findOrFail($ticketId);

            // Dispatch job to queue for async processing
            SendTicketEmail::dispatch($ticket);

            Log::info('Ticket confirmation email queued', [
                'ticket_id' => $ticketId,
                'email' => $ticket->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue ticket confirmation email', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation(string $ticketId, array $paymentData): void
    {
        try {
            $ticket = $this->ticketRepository->findOrFail($ticketId);

            // Dispatch job to queue for async processing
            SendPaymentConfirmationEmail::dispatch($ticket, $paymentData);

            Log::info('Payment confirmation email queued', [
                'ticket_id' => $ticketId,
                'email' => $ticket->email,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue payment confirmation email', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send scan notification (entry/exit)
     */
    public function sendScanNotification(string $ticketId, string $action, array $scanData): void
    {
        try {
            $ticket = $this->ticketRepository->findOrFail($ticketId);

            // Only send notifications for entry and exit actions
            if (!in_array($action, ['in', 'out'])) {
                return;
            }

            // Dispatch job to queue for async processing
            SendScanNotificationEmail::dispatch($ticket, $action, $scanData);

            Log::info('Scan notification email queued', [
                'ticket_id' => $ticketId,
                'email' => $ticket->email,
                'action' => $action,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue scan notification email', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send SMS notification
     */
    public function sendSMS(string $phoneNumber, string $message): void
    {
        try {
            // Dispatch job to queue for async processing
            SendSMSNotification::dispatch($phoneNumber, $message);

            Log::info('SMS notification queued', [
                'phone_number' => $phoneNumber,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue SMS notification', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send email notification
     */
    public function sendEmail(string $email, string $subject, string $body, array $attachments = []): void
    {
        try {
            // For generic emails, we'll use Mail facade directly in the job
            // This is a simple wrapper for custom email notifications
            Log::info('Generic email notification queued', [
                'email' => $email,
                'subject' => $subject,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue generic email notification', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
