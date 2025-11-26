// src/types/User.ts

export type UserRole = 'super_admin' | 'organizer' | 'controller' | 'participant' | 'cashier';

export interface User {
  id: string;
  name: string;
  email: string;
  role: UserRole;
  // Optional: Permissions could be explicitly listed
  // permissions: string[];
}
