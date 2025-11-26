// src/mocks/tickets.ts
import type { Ticket } from '@/types/Ticket';

export const mockTickets: Ticket[] = [
  // Tickets for Tech Conference 2025 (event-1)
  {
    id: 'ticket-1',
    eventId: 'event-1',
    ticketTypeId: 'tt-1-ga',
    participantName: 'Alice',
    participantEmail: 'alice@example.com',
    qr_code_url: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ticket-1',
    status: 'valid',
    secure_token: 'token-alice-12345',
  },
  {
    id: 'ticket-2',
    eventId: 'event-1',
    ticketTypeId: 'tt-1-vip',
    participantName: 'Bob',
    participantEmail: 'bob@example.com',
    qr_code_url: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ticket-2',
    status: 'valid',
    secure_token: 'token-bob-67890',
  },
  {
    id: 'ticket-3',
    eventId: 'event-1',
    ticketTypeId: 'tt-1-ga',
    participantName: 'Charlie',
    participantEmail: 'charlie@example.com',
    qr_code_url: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ticket-3',
    status: 'used',
    secure_token: 'token-charlie-abcde',
  },

  // Tickets for Dakar Music Festival (event-2)
  {
    id: 'ticket-4',
    eventId: 'event-2',
    ticketTypeId: 'tt-2-regular',
    participantEmail: 'diana@example.com',
    qr_code_url: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ticket-4',
    status: 'pending_payment',
    secure_token: 'token-diana-fghij',
  },
  {
    id: 'ticket-5',
    eventId: 'event-2',
    ticketTypeId: 'tt-2-backstage',
    participantName: 'Eve',
    participantEmail: 'eve@example.com',
    qr_code_url: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ticket-5',
    status: 'valid',
    secure_token: 'token-eve-klmno',
  },
];
