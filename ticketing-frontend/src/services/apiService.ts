// src/services/apiService.ts
import { mockUsers } from '@/mocks/users';
import { mockEvents } from '@/mocks/events';
import { mockTickets } from '@/mocks/tickets';
import type { User } from '@/types/User';
import type { Event } from '@/types/Event';
import type { Ticket } from '@/types/Ticket';

// Simulate network delay
const sleep = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

export const apiService = {
  // === User Functions ===
  async getUsers(): Promise<User[]> {
    await sleep(200); // 200ms delay
    return mockUsers;
  },

  async getUserById(id: string): Promise<User | undefined> {
    await sleep(100);
    return mockUsers.find(user => user.id === id);
  },

  // A mock login function
  async login(email: string): Promise<User | undefined> {
    await sleep(300);
    // In a real app, you'd verify password, but here we just find by email
    const user = mockUsers.find(user => user.email.toLowerCase() === email.toLowerCase());
    console.log(`Attempting login for: ${email}`, user);
    return user;
  },

  // === Event Functions ===
  async getEvents(): Promise<Event[]> {
    await sleep(400);
    return mockEvents;
  },
  
  async getEventsByOrganizerId(organizerId: string): Promise<Event[]> {
    await sleep(200);
    return mockEvents.filter(event => event.organizerId === organizerId);
  },

  async getEventById(id: string): Promise<Event | undefined> {
    await sleep(150);
    return mockEvents.find(event => event.id === id);
  },

  // === Ticket Functions ===
  async getTicketsByEventId(eventId: string): Promise<Ticket[]> {
    await sleep(250);
    return mockTickets.filter(ticket => ticket.eventId === eventId);
  },

  async getTicketByIdAndToken(id: string, token: string): Promise<Ticket | undefined> {
    await sleep(100);
    return mockTickets.find(ticket => ticket.id === id && ticket.secure_token === token);
  },
};
