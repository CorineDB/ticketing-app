<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\Event;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketScanned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public Event $event,
        public string $scanType,  // 'entry' or 'exit'
        public string $result,    // 'valid', 'invalid', etc.
        public ?User $scanner = null
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('scans'),  // Public channel for all scans
            new PrivateChannel('event.' . $this->event->id),  // Event-specific channel
            new PrivateChannel('organizer.' . $this->event->organisateur_id),  // Organizer channel
            new PrivateChannel('scanner.' . ($this->scanner?->id ?? 'guest')),  // Scanner channel
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
            'scan_type' => $this->scanType,
            'result' => $this->result,
            'buyer_name' => $this->ticket->buyer_name,
            'ticket_type' => $this->ticket->ticketType->name ?? 'Unknown',
            'scanner_name' => $this->scanner?->name ?? 'Unknown',
            'timestamp' => now()->toISOString(),
            'current_attendance' => $this->event->current_in ?? 0,
            'capacity' => $this->event->capacity,
            'capacity_percentage' => $this->event->capacity > 0
                ? round(($this->event->current_in / $this->event->capacity) * 100, 2)
                : 0,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.scanned';
    }
}
