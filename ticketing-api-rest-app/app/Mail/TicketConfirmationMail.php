<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class TicketConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Ticket $ticket
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre billet pour ' . $this->ticket->ticketType->event->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $magicLink = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')).'/tickets/' . $this->ticket->id . '?token=' . $this->ticket->magic_link_token;
        return new Content(
            view: 'emails.ticket-confirmation',
            with: [
                'ticket' => $this->ticket,
                'event' => $this->ticket->ticketType->event,
                'ticketType' => $this->ticket->ticketType,
                'magicLink' => $magicLink,//route('tickets.show', $this->ticket->id) . '?token=' . $this->ticket->magic_link_token,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    /*public function attachments(): array
    {
        $attachments = [];

        // Attach QR code if it exists
        if ($this->ticket->qr_path && Storage::disk('local')->exists($this->ticket->qr_path)) {
            $attachments[] = Attachment::fromStorage($this->ticket->qr_path)
                ->as('ticket-qr-' . $this->ticket->code . '.png')
                ->withMime('image/png');
        }

        return $attachments;
    }*/
}
