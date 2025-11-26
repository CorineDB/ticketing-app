// src/types/Event.ts

export interface TicketType {
  id: string;
  name: string; // e.g., 'General Admission', 'VIP'
  price: number;
  payment_url?: string;
}

export interface Event {
  id: string;
  name: string;
  date: string; // ISO 8601 format
  location: string;
  capacity: number;
  dress_code?: string;
  organizerId: string; // The ID of the Organizer user
  ticket_types: TicketType[];
}
