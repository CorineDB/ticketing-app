// src/mocks/users.ts
import type { User } from '@/types/User';

export const mockUsers: User[] = [
  {
    id: 'user-super-admin-1',
    name: 'Super Admin',
    email: 'super@ticketing.app',
    role: 'super_admin',
  },
  {
    id: 'user-organizer-1',
    name: 'EventCorp',
    email: 'contact@eventcorp.io',
    role: 'organizer',
  },
  {
    id: 'user-organizer-2',
    name: 'MusicFest Inc.',
    email: 'hello@musicfest.inc',
    role: 'organizer',
  },
  {
    id: 'user-controller-1',
    name: 'John Scanner',
    email: 'john.s@gate-control.co',
    role: 'controller',
  },
  {
    id: 'user-controller-2',
    name: 'Jane Door',
    email: 'jane.d@gate-control.co',
    role: 'controller',
  },
  {
    id: 'user-cashier-1',
    name: 'Cashier Bob',
    email: 'bob.cash@eventcorp.io',
    role: 'cashier',
  },
];
