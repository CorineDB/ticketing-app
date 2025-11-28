# TypeScript Error To-Do List

This list is prioritized by error type and importance, based on the `npx vue-tsc --noEmit` output.

## High Priority: Critical Compilation Errors

### 1. Fix Property & Type Mismatches in `ScanHistoryView.vue`
- **Errors:**
    - `TS2339: Property 'value' does not exist on type 'ScanFilters'` (5 errors, related to `filters.value` in `@change="fetchScans(filters.value)"`). This implies `fetchScans` expects `ScanFilters` directly, not a `Ref`, or `filters.value` is being used incorrectly.
    - `TS2339: Property 'status' does not exist on type 'Scan'` (3 errors for `s.status` in `successfulScans` and `failedScans` computed properties). This needs to be changed to `s.result` in these computed properties.

### 2. Fix Property & Type Mismatches in `TicketsListView.vue`
- **Errors:**
    - Multiple `TS2345` errors related to incompatible `onChange` handlers for filter `<select>` elements (3 errors).
    - Multiple `TS2339` errors for missing methods from `useTickets` (`deleteTicket`, `exportTickets`) and `usePermissions` (`canCreateTickets`, `canUpdateTickets`, `canDeleteTickets`, `canMarkTicketsPaid`, `canGenerateQRCodes`) (7 errors).
    - `TS2322` type error for `status` filter being an empty string when `TicketStatus | undefined` is expected (2 errors).

### 3. Fix Property & Type Mismatches in `TicketDetailView.vue`
- **Errors:**
    - `TS2339` error for missing `buyer_name` property on type '{ id: string; ticket_type_id: string; ticket_type?: { ...; } | undefined; event_id: string; event?: { ...; } | undefined; order_id?: number | undefined; order?: { ...; } | undefined; code: string; qr_code: string; qr_hmac: string; magic_link_token: string; holder_name: string; buyer_email: string; holder_phone?: string | undefined; price: number; currency: string; status: TicketStatus; payment_method?: PaymentMethod | undefined; payment_provider?: PaymentProvider | undefined; payment_reference?: string | undefined; payment_url?: string | undefined; paid_at?: string | undefined; used_at?: string | undefined; used_count: number; scanned_by?: number | undefined; scanner?: User | undefined; gate_in?: number | undefined; last_gate_out?: number | undefined; entry_time?: string | undefined; exit_time?: string | undefined; notes?: string | undefined; created_at: string; updated_at: string; }'. (For `ticket.buyer_name`). This likely refers to `holder_name` instead of `buyer_name` on the Ticket type.
    - `TS6133: 'ref' is declared but its value is never read.`

### 4. Fix Property & Type Mismatches in `UsersListView.vue`
- **Errors:**
    - `TS2322` type error for `Badge` variant prop where `"default"` is not assignable to allowed types (`'primary' | 'success' | 'warning' | 'danger' | 'info' | 'secondary'`).
    - `TS2339` error for missing `status` property on `user`.
    - `TS2322` type error for `StatusBadge` type prop being `"user"`, which is not a valid type. Allowed types: `'order' | 'custom' | 'event' | 'ticket'`.
    - `TS2339` errors for missing methods from `usePermissions` (`canManageUsers`, `canAssignRoles`).
    - `TS2322` type error for `status` filter being an empty string `''` when it expects one of `'active' | 'inactive' | 'suspended' | undefined`.

### 5. Fix Property & Type Mismatches in `ProfileView.vue`
- **Errors:**
    - `TS2322` type error for `Badge` variant prop where `"default"` is not assignable to allowed types (`'primary' | 'success' | 'warning' | 'danger' | 'info' | 'secondary'`).
    - `TS2339` error for missing `status` property on type '{ id: string; name: string; email: string; phone?: string | undefined; type: UserType; avatar?: string | undefined; organisateur_id?: string | undefined; organisateur?: ... | undefined; ... 4 more ...; last_login_at?: string | undefined; }'. (For `<StatusBadge :status="user?.status || 'active'" type="user" />`)
    - `TS2322` type error for `StatusBadge` type prop being `"user"`, which is not a valid type. Allowed types: `'order' | 'custom' | 'event' | 'ticket'`.
    - `TS2322` type error for `Tabs` component props: `Property 'key' is missing in type '{ id: string; label: string; icon: FunctionalComponent<LucideProps, {}, any, {}>; }[]' but required in type 'Tab'.`
    - `TS6133: 'onMounted' is declared but its value is never read.`

### 6. Fix Property & Type Mismatches in `ReportsView.vue`
- **Errors:** `TS2551` Property 'totalTicketsSold' does not exist on type '...' (should be `total_tickets`)
- **Errors:** `TS2551` Property 'totalRevenue' does not exist on type '...' (should be `total_revenue`)

### 7. Fix Property & Type Mismatches in `EventDetailView.vue`
- **Errors:**
    *   `TS2339: Property 'canUpdateEvents' does not exist on type '{ can: (permission: string) => boolean; ... }'.` (4 errors, related to `usePermissions()`).
    *   `TS2345: Argument of type 'string | string[]' is not assignable to parameter of type 'string'.` (for `fetchEvent(eventId)`).
    *   `TS2345: Argument of type '{ event_id: string; }' is not assignable to parameter of type 'string'.` (for `fetchTicketTypes({ event_id: event.value.id })` - 2 errors).
    *   `TS2304: Cannot find name 'fetchStatistics'.` (for `fetchStatistics(event.value.id)`).
    *   `TS6196: 'Event' is declared but never used.`
    *   `TS2322: Type '(gate: Gate) => void' is not assignable to type '(gateId: string) => any'.` (for `@delete="confirmDeleteGate"`).
    *   `TS2551: Property 'quantity_total' does not exist on type '{ ... }'. Did you mean 'quantity_sold'?` (2 errors for `ticketType.quantity_total`).
    *   `TS2322: Type 'string | undefined' is not assignable to type 'number | undefined'.` (for `TicketTypeFormModal :event-id="event?.id"`).
    *   `TS2322: Type 'string | undefined' is not assignable to type 'string'.` (for `GateFormModal :event-id="event?.id"`).

### 8. Fix Property & Type Mismatches in `src/composables/useTickets.ts`
- **Errors:** 5 errors. These errors are related to `fetchTicketById: fetchTicket` and potentially others.

## Medium Priority: Code Quality Issues

### 9. Clean Up Unused Variables
- **Errors:** Multiple `TS6133` errors for unused imports (`computed`, `ref`, `error`, `onMounted`, `TicketType`, `router`, `orderService`) in various files (`ReportsView.vue`, `ScannerView.vue`, `Scanners/ScanHistoryView.vue`, `Scanners/ScannerView.vue`, `TicketDetailView.vue`, `PaymentCallbackView.vue`, `CheckoutView.vue`, `ProfileView.vue`, `OrganizationsListView.vue`, `EventDetailView.vue`).

## Low Priority: Project Configuration Issues

### 10. Investigate and Fix `tsconfig.json` Configuration
- **Errors:** Multiple `TS6305` and `TS6310` errors (from previous runs).
- **Description:** These errors indicate a problem with how `vue-tsc` is configured to generate declaration files (`.d.ts`) or how `tsconfig.json` references other project configurations. This is a project-wide setup issue and is not directly related to application logic. It requires a deeper dive into the TypeScript and Vue build configuration.

---
This concludes the updated listing of errors as tasks. Please let me know which task you would like me to work on next.