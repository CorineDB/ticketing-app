<?php

namespace App\Services\Contracts;

use App\Services\Core\Contracts\BaseServiceInterface;

interface TicketServiceContract extends BaseServiceInterface
{
    public function generateTicket(array $data);

    public function generateBulkTickets(array $data);

    public function getByCode(string $code);

    public function getByEvent(string $eventId);

    public function getByMagicLink(string $token);

    public function generateQRCode(string $ticketId);

    public function getQRCodeFile(string $ticketId);

    public function regenerateQRCodeSecret(string $ticketId);

    public function markAsPaid(string $ticketId);

    public function sendTicketByEmail(string $ticketId);

    public function invalidateTicket(string $ticketId, string $reason);
}
