<?php

namespace App\Events;

use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventCapacityAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Event $event,
        public int $currentAttendance,
        public string $alertLevel  // 'warning' (80%), 'critical' (90%), 'full' (100%)
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('event.' . $this->event->id),
            new PrivateChannel('organizer.' . $this->event->organisateur_id),
            new Channel('alerts'),  // Public alerts channel
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $capacityPercentage = $this->event->capacity > 0
            ? round(($this->currentAttendance / $this->event->capacity) * 100, 2)
            : 0;

        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'current_attendance' => $this->currentAttendance,
            'capacity' => $this->event->capacity,
            'capacity_percentage' => $capacityPercentage,
            'alert_level' => $this->alertLevel,
            'message' => $this->getAlertMessage($capacityPercentage),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get alert message based on level.
     */
    private function getAlertMessage(float $percentage): string
    {
        return match ($this->alertLevel) {
            'warning' => "Event '{$this->event->title}' is at {$percentage}% capacity",
            'critical' => "CRITICAL: Event '{$this->event->title}' is at {$percentage}% capacity",
            'full' => "Event '{$this->event->title}' has reached maximum capacity",
            default => "Capacity alert for event '{$this->event->title}'",
        };
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'event.capacity.alert';
    }
}
