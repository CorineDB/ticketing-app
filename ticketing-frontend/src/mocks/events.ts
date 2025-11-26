// src/mocks/events.ts
import type { Event } from '@/types/Event';

export const mockEvents: Event[] = [
  {
    id: 'event-1',
    name: 'Tech Conference 2025',
    date: '2025-12-15T09:00:00Z',
    location: 'Convention Center, Dakar',
    capacity: 2000,
    dress_code: 'Business Casual',
    organizerId: 'user-organizer-1', // EventCorp
    ticket_types: [
      { id: 'tt-1-ga', name: 'General Admission', price: 50, payment_url: 'https://paydunya.example.com/tech-ga' },
      { id: 'tt-1-vip', name: 'VIP Pass', price: 250, payment_url: 'https://paydunya.example.com/tech-vip' },
      { id: 'tt-1-student', name: 'Student', price: 25, payment_url: 'https://paydunya.example.com/tech-student' },
    ],
  },
  {
    id: 'event-2',
    name: 'Dakar Music Festival',
    date: '2025-11-29T18:00:00Z',
    location: 'Monument de la Renaissance',
    capacity: 10000,
    organizerId: 'user-organizer-2', // MusicFest Inc.
    ticket_types: [
      { id: 'tt-2-regular', name: 'Regular Ticket', price: 100, payment_url: 'https://cinetpay.example.com/music-reg' },
      { id: 'tt-2-backstage', name: 'Backstage Access', price: 500, payment_url: 'https://cinetpay.example.com/music-backstage' },
    ],
  },
  {
    id: 'event-3',
    name: 'Startup Pitch Night',
    date: '2026-01-20T17:00:00Z',
    location: 'Impact Hub, Abidjan',
    capacity: 150,
    organizerId: 'user-organizer-1', // EventCorp
    ticket_types: [
      { id: 'tt-3-pitch', name: 'Participant', price: 10, payment_url: 'https://mtnmomo.example.com/startup-pitch' },
    ],
  },
];
