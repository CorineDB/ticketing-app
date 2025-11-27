<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get organizer dashboard statistics and data
     */
    public function organizer(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get all events for this organizer
        $events = Event::where('organisateur_id', $user->id)
            ->orWhere('created_by', $user->id)
            ->get();

        $eventIds = $events->pluck('id');

        // Calculate statistics
        $totalEvents = $events->count();

        // Active events are those that haven't ended yet
        $activeEvents = $events->where('end_datetime', '>=', now())->count();

        // Get all tickets for these events
        $tickets = Ticket::whereIn('event_id', $eventIds)
            ->whereIn('status', ['paid', 'issued', 'in', 'out'])
            ->get();

        $totalTicketsSold = $tickets->count();

        // Calculate total revenue from paid tickets
        $totalRevenue = $tickets->sum(function ($ticket) {
            return $ticket->ticketType ? $ticket->ticketType->price : 0;
        });

        // Get upcoming events (not ended yet, ordered by start date)
        $upcomingEvents = Event::where(function ($query) use ($user) {
                $query->where('organisateur_id', $user->id)
                    ->orWhere('created_by', $user->id);
            })
            ->where('end_datetime', '>=', now())
            ->orderBy('start_datetime', 'asc')
            ->with(['ticketTypes', 'counter'])
            ->limit(6)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'image_url' => $event->image_url,
                    'location' => $event->location,
                    'start_datetime' => $event->start_datetime,
                    'end_datetime' => $event->end_datetime,
                    'capacity' => $event->capacity,
                    'current_in' => $event->counter ? $event->counter->current_in : 0,
                    'ticket_types' => $event->ticketTypes,
                    'tickets_sold' => $event->tickets()->whereIn('status', ['paid', 'issued', 'in', 'out'])->count(),
                ];
            });

        // Get recent tickets grouped by buyer (mimicking orders)
        $recentTickets = Ticket::whereIn('event_id', $eventIds)
            ->whereNotNull('paid_at')
            ->with(['event', 'ticketType'])
            ->orderBy('paid_at', 'desc')
            ->limit(10)
            ->get()
            ->groupBy(function ($ticket) {
                return $ticket->buyer_email . '|' . $ticket->event_id . '|' . $ticket->paid_at?->format('Y-m-d H:i:s');
            })
            ->map(function ($ticketGroup) {
                $firstTicket = $ticketGroup->first();
                $totalAmount = $ticketGroup->sum(function ($ticket) {
                    return $ticket->ticketType ? $ticket->ticketType->price : 0;
                });

                return [
                    'id' => $firstTicket->id,
                    'order_number' => substr($firstTicket->code, 0, 8),
                    'event' => [
                        'id' => $firstTicket->event->id,
                        'name' => $firstTicket->event->title,
                    ],
                    'customer_name' => $firstTicket->buyer_name,
                    'customer_email' => $firstTicket->buyer_email,
                    'total_amount' => $totalAmount,
                    'currency' => 'XOF',
                    'status' => $firstTicket->status === 'paid' ? 'completed' : 'pending',
                    'tickets_count' => $ticketGroup->count(),
                    'created_at' => $firstTicket->paid_at ?? $firstTicket->created_at,
                ];
            })
            ->values();

        return response()->json([
            'data' => [
                'stats' => [
                    'total_events' => $totalEvents,
                    'active_events' => $activeEvents,
                    'total_tickets_sold' => $totalTicketsSold,
                    'total_revenue' => $totalRevenue,
                ],
                'upcoming_events' => $upcomingEvents,
                'recent_orders' => $recentTickets,
            ]
        ]);
    }

    /**
     * Get super admin dashboard statistics
     */
    public function superAdmin(): JsonResponse
    {
        // Calculate global statistics
        $totalEvents = Event::count();
        $activeEvents = Event::where('end_datetime', '>=', now())->count();
        $totalTicketsSold = Ticket::whereIn('status', ['paid', 'issued', 'in', 'out'])->count();

        // Calculate total revenue
        $totalRevenue = Ticket::whereIn('status', ['paid', 'issued', 'in', 'out'])
            ->with('ticketType')
            ->get()
            ->sum(function ($ticket) {
                return $ticket->ticketType ? $ticket->ticketType->price : 0;
            });

        // Get recent events
        $recentEvents = Event::with(['organisateur', 'ticketTypes'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get top events by tickets sold
        $topEvents = Event::with(['organisateur', 'ticketTypes'])
            ->withCount(['tickets' => function ($query) {
                $query->whereIn('status', ['paid', 'issued', 'in', 'out']);
            }])
            ->orderBy('tickets_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'total_events' => $totalEvents,
                'active_events' => $activeEvents,
                'total_tickets_sold' => $totalTicketsSold,
                'total_revenue' => $totalRevenue,
                'recent_events' => $recentEvents,
                'top_events' => $topEvents,
            ]
        ]);
    }

    /**
     * Get scanner dashboard statistics
     */
    public function scanner(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get scan logs for today
        $scansToday = DB::table('ticket_scan_logs')
            ->where('agent_id', $user->id)
            ->whereDate('created_at', today())
            ->get();

        $totalScansToday = $scansToday->count();
        $validScans = $scansToday->where('scan_result', 'valid')->count();
        $invalidScans = $scansToday->where('scan_result', '!=', 'valid')->count();

        // Get recent scans
        $recentScans = DB::table('ticket_scan_logs')
            ->where('agent_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'stats' => [
                    'total_scans_today' => $totalScansToday,
                    'valid_scans' => $validScans,
                    'invalid_scans' => $invalidScans,
                    'current_attendance' => 0,
                ],
                'recent_scans' => $recentScans,
            ]
        ]);
    }
}
