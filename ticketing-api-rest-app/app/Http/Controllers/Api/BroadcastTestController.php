<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\TicketPurchased;
use App\Events\TicketScanned;
use App\Events\EventCapacityAlert;
use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;

class BroadcastTestController extends Controller
{
    /**
     * Test TicketPurchased broadcast
     */
    public function testTicketPurchased(Request $request)
    {
        // Get a random ticket or create a test one
        $ticket = Ticket::with(['ticketType', 'event'])->inRandomOrder()->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'No tickets found in database. Create a ticket first.'
            ], 404);
        }

        $event = $ticket->event;

        // Broadcast the event immediately (skip queue for testing)
        event(new TicketPurchased($ticket, $event));

        return response()->json([
            'success' => true,
            'message' => 'TicketPurchased event broadcasted',
            'data' => [
                'ticket_id' => $ticket->id,
                'event_id' => $event->id,
                'event_title' => $event->title,
                'buyer_name' => $ticket->buyer_name,
                'amount' => $ticket->price,
            ],
            'channels' => [
                'events',
                'event.' . $event->id,
                'organizer.' . $event->organisateur_id,
            ]
        ]);
    }

    /**
     * Test TicketScanned broadcast
     */
    public function testTicketScanned(Request $request)
    {
        $ticket = Ticket::with(['ticketType', 'event'])->inRandomOrder()->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'No tickets found in database.'
            ], 404);
        }

        $event = $ticket->event;
        $scanner = auth()->user();

        // Broadcast the event immediately (skip queue for testing)
        event(new TicketScanned(
            $ticket,
            $event,
            'entry',
            'valid',
            $scanner
        ));

        return response()->json([
            'success' => true,
            'message' => 'TicketScanned event broadcasted',
            'data' => [
                'ticket_id' => $ticket->id,
                'event_id' => $event->id,
                'scan_type' => 'entry',
                'result' => 'valid',
                'current_attendance' => $event->current_in ?? 0,
            ],
            'channels' => [
                'scans',
                'event.' . $event->id,
                'organizer.' . $event->organisateur_id,
                'scanner.' . ($scanner?->id ?? 'guest'),
            ]
        ]);
    }

    /**
     * Test EventCapacityAlert broadcast
     */
    public function testCapacityAlert(Request $request)
    {
        $event = Event::inRandomOrder()->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'No events found in database.'
            ], 404);
        }

        // Simulate different alert levels
        $alertLevel = $request->input('level', 'warning'); // warning, critical, full
        $currentAttendance = match ($alertLevel) {
            'warning' => (int) ($event->capacity * 0.8),
            'critical' => (int) ($event->capacity * 0.9),
            'full' => $event->capacity,
            default => (int) ($event->capacity * 0.8),
        };

        // Broadcast the event immediately (skip queue for testing)
        event(new EventCapacityAlert($event, $currentAttendance, $alertLevel));

        return response()->json([
            'success' => true,
            'message' => 'EventCapacityAlert broadcasted',
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'current_attendance' => $currentAttendance,
                'capacity' => $event->capacity,
                'alert_level' => $alertLevel,
                'capacity_percentage' => round(($currentAttendance / $event->capacity) * 100, 2),
            ],
            'channels' => [
                'event.' . $event->id,
                'organizer.' . $event->organisateur_id,
                'alerts',
            ]
        ]);
    }

    /**
     * Test all broadcasts at once
     */
    public function testAll(Request $request)
    {
        $results = [];

        // Test TicketPurchased
        $ticket = Ticket::with(['ticketType', 'event'])->inRandomOrder()->first();
        if ($ticket) {
            event(new TicketPurchased($ticket, $ticket->event));
            $results[] = 'TicketPurchased broadcasted';
        }

        // Test TicketScanned
        if ($ticket) {
            event(new TicketScanned($ticket, $ticket->event, 'entry', 'valid', auth()->user()));
            $results[] = 'TicketScanned broadcasted';
        }

        // Test EventCapacityAlert
        $event = Event::inRandomOrder()->first();
        if ($event) {
            event(new EventCapacityAlert($event, (int) ($event->capacity * 0.85), 'warning'));
            $results[] = 'EventCapacityAlert broadcasted';
        }

        return response()->json([
            'success' => true,
            'message' => 'All broadcast events triggered',
            'results' => $results
        ]);
    }
}
