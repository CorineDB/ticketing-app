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
        $totalTicketsSold = Ticket::whereIn('status', ['paid', 'issued', 'in', 'out'])->count();

        // Calculate total revenue
        $totalRevenue = Ticket::whereIn('status', ['paid', 'issued', 'in', 'out'])
            ->with('ticketType')
            ->get()
            ->sum(function ($ticket) {
                return $ticket->ticketType ? $ticket->ticketType->price : 0;
            });

        // Count organisateurs (users with role organizer)
        $totalOrganisateurs = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.slug', 'organizer')
            ->count();

        // Event status breakdown
        $now = now();
        $activeEvents = Event::where('start_datetime', '<=', $now)
            ->where('end_datetime', '>=', $now)
            ->count();

        $upcomingEvents = Event::where('start_datetime', '>', $now)->count();
        $completedEvents = Event::where('end_datetime', '<', $now)->count();
        $draftEvents = Event::where('status', 'draft')->count();

        // Revenue by month (last 6 months)
        $revenueByMonth = DB::table('tickets')
            ->whereIn('status', ['paid', 'issued', 'in', 'out'])
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subMonths(6))
            ->join('ticket_types', 'tickets.ticket_type_id', '=', 'ticket_types.id')
            ->selectRaw("TO_CHAR(paid_at, 'YYYY-MM') as month, SUM(ticket_types.price) as revenue")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => date('M Y', strtotime($item->month . '-01')),
                    'revenue' => (float) $item->revenue,
                ];
            });

        // Top organisateurs by revenue
        $topOrganisateurs = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('events', 'users.id', '=', 'events.organisateur_id')
            ->join('tickets', 'events.id', '=', 'tickets.event_id')
            ->join('ticket_types', 'tickets.ticket_type_id', '=', 'ticket_types.id')
            ->where('roles.slug', 'organizer')
            ->whereIn('tickets.status', ['paid', 'issued', 'in', 'out'])
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(DISTINCT events.id) as events_count'),
                DB::raw('SUM(ticket_types.price) as revenue')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('revenue', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($org) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'events_count' => (int) $org->events_count,
                    'revenue' => (float) $org->revenue,
                ];
            });

        // Recent events
        $recentEvents = Event::with(['organisateur'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start_datetime' => $event->start_datetime,
                    'status' => $event->status,
                    'organisateur' => $event->organisateur ? [
                        'name' => $event->organisateur->name,
                    ] : null,
                ];
            });

        return response()->json([
            'total_organisateurs' => $totalOrganisateurs,
            'total_events' => $totalEvents,
            'total_tickets_sold' => $totalTicketsSold,
            'total_revenue' => $totalRevenue,
            'active_events' => $activeEvents,
            'upcoming_events' => $upcomingEvents,
            'completed_events' => $completedEvents,
            'draft_events' => $draftEvents,
            'revenue_by_month' => $revenueByMonth,
            'top_organisateurs' => $topOrganisateurs,
            'recent_events' => $recentEvents,
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

        // Get current event (most recent event the scanner has scanned for)
        $currentEvent = null;
        $latestScan = DB::table('ticket_scan_logs')
            ->where('agent_id', $user->id)
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestScan) {
            $ticket = Ticket::find($latestScan->ticket_id);
            if ($ticket && $ticket->event) {
                $event = $ticket->event;
                $currentEvent = [
                    'id' => $event->id,
                    'name' => $event->title,
                    'banner' => $event->image_url,
                    'start_date' => $event->start_datetime,
                    'venue' => $event->location,
                    'capacity' => $event->capacity,
                    'tickets_sold' => $event->tickets()->whereIn('status', ['paid', 'issued', 'in', 'out'])->count(),
                    'current_in' => $event->counter ? $event->counter->current_in : 0,
                    'status' => $event->status,
                ];
            }
        }

        // Get recent scans with full details
        $recentScans = DB::table('ticket_scan_logs')
            ->where('agent_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($scan) {
                $ticket = Ticket::with(['ticketType'])->find($scan->ticket_id);
                return [
                    'id' => $scan->id,
                    'result' => $scan->scan_result,
                    'scan_type' => $scan->scan_type,
                    'scanned_at' => $scan->created_at,
                    'ticket' => $ticket ? [
                        'id' => $ticket->id,
                        'holder_name' => $ticket->holder_name,
                        'ticket_type' => $ticket->ticketType ? [
                            'name' => $ticket->ticketType->name,
                        ] : null,
                    ] : null,
                ];
            });

        // Calculate current attendance
        $currentAttendance = $currentEvent ? $currentEvent['current_in'] : 0;

        return response()->json([
            'stats' => [
                'total_scans_today' => $totalScansToday,
                'valid_scans' => $validScans,
                'invalid_scans' => $invalidScans,
                'current_attendance' => $currentAttendance,
            ],
            'current_event' => $currentEvent,
            'recent_scans' => $recentScans,
        ]);
    }

    /**
     * Get participant dashboard data
     */
    public function participant(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get my tickets
        $myTickets = Ticket::where('buyer_email', $user->email)
            ->with(['event', 'ticketType'])
            ->whereIn('status', ['paid', 'issued', 'in', 'out'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'code' => $ticket->code,
                    'holder_name' => $ticket->holder_name,
                    'status' => $ticket->status,
                    'event' => [
                        'id' => $ticket->event->id,
                        'title' => $ticket->event->title,
                        'banner' => $ticket->event->image_url,
                        'start_date' => $ticket->event->start_datetime,
                        'venue' => $ticket->event->location,
                    ],
                    'ticket_type' => $ticket->ticketType ? [
                        'name' => $ticket->ticketType->name,
                        'price' => $ticket->ticketType->price,
                    ] : null,
                ];
            });

        // Get upcoming events with my tickets count
        $upcomingEvents = Event::whereHas('tickets', function ($query) use ($user) {
                $query->where('buyer_email', $user->email)
                    ->whereIn('status', ['paid', 'issued', 'in', 'out']);
            })
            ->where('end_datetime', '>=', now())
            ->with(['tickets' => function ($query) use ($user) {
                $query->where('buyer_email', $user->email)
                    ->whereIn('status', ['paid', 'issued', 'in', 'out']);
            }])
            ->orderBy('start_datetime', 'asc')
            ->get()
            ->map(function ($event) use ($user) {
                $ticketsCount = $event->tickets->where('buyer_email', $user->email)->count();
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'slug' => $event->slug,
                    'banner' => $event->image_url,
                    'start_date' => $event->start_datetime,
                    'venue' => $event->location,
                    'tickets_count' => $ticketsCount,
                ];
            });

        return response()->json([
            'my_tickets' => $myTickets,
            'upcoming_events' => $upcomingEvents,
        ]);
    }

    /**
     * Get cashier dashboard data
     */
    public function cashier(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get today's sales statistics
        $todayTickets = Ticket::whereDate('paid_at', today())
            ->where('created_by', $user->id)
            ->whereIn('status', ['paid', 'issued', 'in', 'out'])
            ->with('ticketType')
            ->get();

        $salesToday = $todayTickets->sum(function ($ticket) {
            return $ticket->ticketType ? $ticket->ticketType->price : 0;
        });

        $ticketsSoldToday = $todayTickets->count();

        $cashPayments = $todayTickets->where('payment_method', 'cash')->sum(function ($ticket) {
            return $ticket->ticketType ? $ticket->ticketType->price : 0;
        });

        // Count unique orders (grouped by buyer_email and paid_at)
        $totalOrdersToday = $todayTickets->groupBy(function ($ticket) {
            return $ticket->buyer_email . '|' . $ticket->paid_at?->format('Y-m-d H:i:s');
        })->count();

        // Get recent transactions (grouped by buyer as orders)
        $recentTransactions = Ticket::where('created_by', $user->id)
            ->whereNotNull('paid_at')
            ->with(['event', 'ticketType'])
            ->orderBy('paid_at', 'desc')
            ->limit(20)
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
                    'customer_name' => $firstTicket->buyer_name,
                    'tickets_count' => $ticketGroup->count(),
                    'payment_method' => $firstTicket->payment_method ?? 'cash',
                    'total_amount' => $totalAmount,
                    'created_at' => $firstTicket->paid_at ?? $firstTicket->created_at,
                ];
            })
            ->values()
            ->take(10);

        return response()->json([
            'stats' => [
                'sales_today' => $salesToday,
                'tickets_sold_today' => $ticketsSoldToday,
                'cash_payments' => $cashPayments,
                'total_orders_today' => $totalOrdersToday,
            ],
            'recent_transactions' => $recentTransactions,
        ]);
    }
}
