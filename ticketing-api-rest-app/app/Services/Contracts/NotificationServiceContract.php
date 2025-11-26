<?php

namespace App\Services\Contracts;

interface NotificationServiceContract
{
    /**
     * Send ticket confirmation email with QR code
     */
    public function sendTicketConfirmation(string $ticketId): void;

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation(string $ticketId, array $paymentData): void;

    /**
     * Send scan notification (entry/exit)
     */
    public function sendScanNotification(string $ticketId, string $action, array $scanData): void;

    /**
     * Send SMS notification
     */
    public function sendSMS(string $phoneNumber, string $message): void;

    /**
     * Send email notification
     */
    public function sendEmail(string $email, string $subject, string $body, array $attachments = []): void;
}
