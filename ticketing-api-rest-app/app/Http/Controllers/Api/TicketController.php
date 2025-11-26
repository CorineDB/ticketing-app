<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tickets\CreateTicketRequest;
use App\Services\Contracts\TicketServiceContract;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected TicketServiceContract $ticketService;

    public function __construct(TicketServiceContract $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request)
    {
        $eventId = $request->query('event_id');

        if ($eventId) {
            $tickets = $this->ticketService->getByEvent($eventId);
        } else {
            $tickets = $this->ticketService->list();
        }

        return response()->json(['data' => $tickets]);
    }

    public function store(CreateTicketRequest $request)
    {
        $data = $request->validated();
        $quantity = $data['quantity'] ?? 1;

        if ($quantity > 1) {
            $tickets = $this->ticketService->generateBulkTickets($data);
        } else {
            $tickets = [$this->ticketService->generateTicket($data)];
        }

        return response()->json($tickets, 201);
    }

    public function show(string $id, Request $request)
    {
        $token = $request->query('token');

        if ($token) {
            $ticket = $this->ticketService->getByMagicLink($token);
        } else {
            $ticket = $this->ticketService->get($id);
        }

        return response()->json($ticket);
    }

    public function update(Request $request, string $id)
    {
        $ticket = $this->ticketService->update($id, $request->all());
        return response()->json($ticket);
    }

    public function destroy(string $id)
    {
        $this->ticketService->delete($id);
        return response()->json(null, 204);
    }

    public function qr(string $id, Request $request)
    {
        $token = $request->query('token');

        if ($token) {
            $ticket = $this->ticketService->getByMagicLink($token);
            if (!$ticket) {
                return response()->json(['error' => 'Invalid token'], 401);
            }
            $id = $ticket->id;
        }

        $qrData = $this->ticketService->generateQRCode($id);
        return response()->json($qrData);
    }

    public function markPaid(string $id)
    {
        $ticket = $this->ticketService->markAsPaid($id);
        return response()->json($ticket);
    }
}
