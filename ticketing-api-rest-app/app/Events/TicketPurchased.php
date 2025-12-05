<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketPurchased implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public Event $event
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('events'),  // Public channel for all events
            new PrivateChannel('event.' . $this->event->id),  // Private channel for specific event
            new PrivateChannel('organizer.' . $this->event->organisateur_id),  // Private channel for organizer
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'ticket_type' => $this->ticket->ticketType->name ?? 'Unknown',
            'buyer_name' => $this->ticket->buyer_name,
            'amount' => $this->ticket->price,
            'currency' => $this->ticket->currency,
            'timestamp' => now()->toISOString(),
            'tickets_sold' => $this->event->tickets_sold ?? 0,
            'capacity' => $this->event->capacity,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.purchased';
    }
}
