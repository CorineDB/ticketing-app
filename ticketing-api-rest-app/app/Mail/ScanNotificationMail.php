<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScanNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Ticket $ticket,
        public string $action,
        public array $scanData
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->action === 'in'
            ? 'Entrée confirmée - ' . $this->ticket->ticketType->event->name
            : 'Sortie confirmée - ' . $this->ticket->ticketType->event->name;

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.scan-notification',
            with: [
                'ticket' => $this->ticket,
                'event' => $this->ticket->ticketType->event,
                'action' => $this->action,
                'scanData' => $this->scanData,
                'gateName' => $this->scanData['gate_name'] ?? 'N/A',
                'scanTime' => $this->scanData['scan_time'] ?? now(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
