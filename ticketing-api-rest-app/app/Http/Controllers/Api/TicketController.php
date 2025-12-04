<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tickets\CreateTicketRequest;
use App\Http\Requests\Api\Tickets\TicketPurchaseRequest;
use App\Services\Contracts\TicketServiceContract;
use App\Services\Contracts\PaymentServiceContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    protected TicketServiceContract $ticketService;
    protected PaymentServiceContract $paymentService;

    public function __construct(
        TicketServiceContract $ticketService,
        PaymentServiceContract $paymentService
    ) {
        $this->ticketService = $ticketService;
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $code = $request->query('code');

        if ($code) {
            $ticket = $this->ticketService->getByCode($code);
            return response()->json(['data' => $ticket ? [$ticket->load(['event', 'ticketType'])] : []]);
        }

        $eventId = $request->query('event_id');

        if ($eventId) {
            $tickets = $this->ticketService->getByEvent($eventId);
        } else {
            $tickets = $this->ticketService->list(limit: 1000, relations: ['event', 'ticketType']);
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

    /**
     * Purchase tickets - Creates tickets and initiates payment
     */
    public function purchase(TicketPurchaseRequest $request)
    {
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data) {
                $ticketTypeId = $data['ticket_type_id'];
                $quantity = $data['quantity'];
                $customer = $data['customer'];

                // Get ticket type and check availability
                $ticketType = $this->ticketService->getTicketType($ticketTypeId);

                if (!$ticketType) {
                    return response()->json(['error' => 'Type de ticket non trouvé'], 404);
                }

                // Check quota availability
                if (!$this->ticketService->checkQuotaAvailability($ticketTypeId, $quantity)) {
                    return response()->json([
                        'error' => 'Quota insuffisant',
                        'message' => 'Il n\'y a pas assez de tickets disponibles pour ce type.'
                    ], 400);
                }

                // Calculate total amount
                $totalAmount = $ticketType->price * $quantity;

                // Create tickets in pending status
                $tickets = [];
                $ticketIds = [];
                for ($i = 0; $i < $quantity; $i++) {
                    $ticket = $this->ticketService->generateTicket([
                        'event_id' => $ticketType->event_id,
                        'ticket_type_id' => $ticketTypeId,
                        'buyer_name' => $customer['firstname'] . ' ' . $customer['lastname'],
                        'buyer_email' => $customer['email'],
                        'buyer_phone' => $customer['phone_number'] ?? null,
                        'status' => 'issued', // Will be updated to 'paid' via webhook
                    ]);
                    $tickets[] = $ticket;
                    $ticketIds[] = $ticket->id;
                }

                // Create payment transaction with ALL ticket IDs
                $paymentData = $this->paymentService->createTransactionForTicket(
                    $ticketIds,
                    $customer,
                    $totalAmount,
                    "Achat de {$quantity} ticket(s) - {$ticketType->name}"
                );

                return response()->json([
                    'tickets' => $tickets,
                    'payment_url' => $paymentData['payment_url'],
                    'transaction_id' => $paymentData['transaction_id'],
                    'total_amount' => $totalAmount,
                    'currency' => $paymentData['currency'],
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Ticket purchase transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            return response()->json([
                'error' => 'Échec de la création de la transaction',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id, Request $request)
    {
        // If user is not authenticated, X-MAGIC-TOKEN is required
        if (!auth()->check()) {
            $token = $request->header('X-MAGIC-TOKEN') ?? $request->query('token');

            if (!$token) {
                return response()->json(['error' => 'Unauthenticated. X-MAGIC-TOKEN header required.'], 401);
            }

            $ticket = $this->ticketService->getByMagicLink($token);
            if (!$ticket) {
                return response()->json(['error' => 'Invalid or expired magic link token'], 404);
            }
        } else {
            // User is authenticated, get ticket normally
            $ticket = $this->ticketService->get($id);
        }

        return response()->json($ticket->load("event", "ticketType"));
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
        // If user is not authenticated, X-MAGIC-TOKEN is required
        if (!auth()->check()) {
            $token = $request->header('X-MAGIC-TOKEN') ?? $request->query('token');

            if (!$token) {
                return response()->json(['error' => 'Unauthenticated. X-MAGIC-TOKEN header required.'], 401);
            }

            $ticket = $this->ticketService->getByMagicLink($token);
            if (!$ticket) {
                return response()->json(['error' => 'Invalid or expired magic link token'], 404);
            }
            $id = $ticket->id;
        }

        $qrData = $this->ticketService->generateQRCode($id);
        return response()->json($qrData);
    }

    public function downloadQr(string $id, Request $request)
    {
        // If user is not authenticated, X-MAGIC-TOKEN is required
        if (!auth()->check()) {
            $token = $request->header('X-MAGIC-TOKEN') ?? $request->query('token');

            if (!$token) {
                return response()->json(['error' => 'Unauthenticated. X-MAGIC-TOKEN header required.'], 401);
            }

            $ticket = $this->ticketService->getByMagicLink($token);
            if (!$ticket || $ticket->id !== $id) {
                return response()->json(['error' => 'Invalid or expired magic link token'], 404);
            }
        }

        try {
            $qrFile = $this->ticketService->getQRCodeFile($id);

            return response($qrFile['content'])
                ->header('Content-Type', $qrFile['mime_type'])
                ->header('Content-Disposition', 'inline; filename="ticket-' . $id . '.png"');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
        }
    }

    public function regenerateCode(string $id)
    {
        try {
            $result = $this->ticketService->regenerateQRCodeSecret($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function markPaid(string $id)
    {
        $ticket = $this->ticketService->markAsPaid($id);
        return response()->json($ticket);
    }

    public function sendTicketByEmail(string $id)
    {
        $ticket = $this->ticketService->sendTicketByEmail($id);
        return response()->json($ticket);
    }

}
