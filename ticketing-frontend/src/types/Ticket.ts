// src/types/Ticket.ts

export type TicketStatus = 'valid' | 'invalid' | 'used' | 'pending_payment';

export interface Ticket {
  id: string;
  eventId: string;
  ticketTypeId: string;
  participantName?: string;
  participantEmail?: string;
  qr_code_url: string; // URL to the QR code image or data
  status: TicketStatus;
  secure_token: string; // For magic link access
}
