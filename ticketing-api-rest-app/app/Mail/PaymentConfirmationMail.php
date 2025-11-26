<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Ticket $ticket,
        public array $paymentData
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de paiement - ' . $this->ticket->ticketType->event->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-confirmation',
            with: [
                'ticket' => $this->ticket,
                'event' => $this->ticket->ticketType->event,
                'ticketType' => $this->ticket->ticketType,
                'paymentData' => $this->paymentData,
                'amount' => $this->paymentData['amount'] ?? $this->ticket->ticketType->price,
                'transactionId' => $this->paymentData['transaction_id'] ?? 'N/A',
                'magicLink' => route('tickets.show', ['id' => $this->ticket->id, 'token' => $this->ticket->magic_link_token]),
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
