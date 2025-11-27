# Ticketing App - Event Management & QR Code Access Control

## Overview

**Ticketing App** is a comprehensive event management platform with ticket generation and QR code-based access control. The application supports multiple user roles (Super Admin, Organizer, Scanner, Cashier, Participant) and provides complete event lifecycle management from creation to ticket validation.

**Project Location**: `/home/unknow/Ticketing/ticketing-app/`

**Architecture**: Based on Sirene Vue3 Architecture Pattern

**Tech Stack**: Vue 3 + TypeScript + Vite + Pinia + Vue Router + Tailwind CSS + Axios + QRCode

---

## Table of Contents

- [Quick Start](#quick-start)
- [Project Architecture](#project-architecture)
- [User Roles & Permissions](#user-roles--permissions)
- [Core Features](#core-features)
- [Directory Structure](#directory-structure)
- [Tech Stack](#tech-stack)
- [Architecture Patterns](#architecture-patterns)
- [State Management](#state-management)
- [API Integration](#api-integration)
- [UI Components Documentation](#ui-components-documentation)
- [Pages & Views Documentation](#pages--views-documentation)
- [Database Schema](#database-schema)
- [Development Guide](#development-guide)
- [Deployment](#deployment)

---

## Quick Start

### Prerequisites
- Node.js 16+
- npm or yarn
- Backend API (Laravel recommended)

### Installation

```bash
# Navigate to project
cd /home/unknow/Ticketing/ticketing-app

# Install dependencies
npm install

# Configure environment
cp .env.example .env
# Edit .env with your API URL and payment gateway credentials

# Start development server
npm run dev
```

### Available Scripts

```bash
npm run dev        # Start dev server (http://localhost:5173)
npm run build      # Build for production
npm run preview    # Preview production build
npm run test       # Run unit tests
npm run type-check # TypeScript type checking
```

---

## Project Architecture

### High-Level Architecture

```
┌──────────────────────────────────────────────────────────┐
│              Ticketing App - Vue 3 Frontend               │
├──────────────────────────────────────────────────────────┤
│  Views (Pages) → User Interface & Page Components        │
│         ↓                                                 │
│  Composables → Business Logic & Reusable Hooks           │
│         ↓                                                 │
│  Services → API Communication Layer                       │
│         ↓                                                 │
│  Axios Client → HTTP Requests with Interceptors          │
│         ↓                                                 │
│  Backend API → Laravel/Node.js                           │
├──────────────────────────────────────────────────────────┤
│  State: Pinia Stores (auth, notifications)               │
│  Routing: Vue Router (30+ routes)                        │
│  Styling: Tailwind CSS                                   │
│  QR Code: qrcode + html5-qrcode libraries                │
└──────────────────────────────────────────────────────────┘
```

### Design Principles

1. **Separation of Concerns**: Views → Composables → Services → API
2. **Type Safety**: Comprehensive TypeScript types for all entities
3. **Reusability**: Composables for shared business logic
4. **Role-Based Access Control**: Granular permissions per user type
5. **Offline-Ready QR Validation**: QR codes work without internet

---

## User Roles & Permissions

### 1. Super Admin (Platform Administrator)

**Purpose**: Manages the entire platform

**Permissions**:
- Create/edit/delete all events
- Manage all organizations
- View all statistics globally
- Manage user roles and permissions
- Monitor all ticket validations
- Access to all dashboards

**Access**:
- Super Admin Dashboard
- All events (all organizations)
- All users management
- Organizations management
- Global reports and analytics

---

### 2. Organizer (Event Admin)

**Purpose**: Manages specific events

**Permissions**:
- Create/edit/delete own events
- Add ticket types (VIP, Regular, Early Bird, etc.)
- Generate tickets
- Access event dashboard (sales analytics)
- Export participant lists
- Manage scanners/agents for events
- View scan history (entries/exits)
- Define payment URLs per ticket type
- View event reports

**Access**:
- Organizer Dashboard
- My Events list
- Event creation/editing
- Ticket management
- Scanner management
- Event analytics

**Restrictions**:
- Cannot manage other organizers' events
- Cannot access super admin features
- Cannot manage organizations (unless Super Admin)

---

### 3. Scanner/Agent (Door Controller)

**Purpose**: Validates tickets at event entrances

**Permissions**:
- Scan QR codes
- View ticket validation result (valid/invalid/already used)
- Record entry/exit
- View own scan history
- Access current event information

**Interface Features**:
- QR Scanner camera interface
- Real-time validation feedback
- Audio/visual indicators (green/red)
- Offline validation capability
- Scan history for current shift

**Restrictions**:
- Cannot edit events
- Cannot see financial data
- Only sees assigned event(s)
- Cannot generate tickets

---

### 4. Cashier (Payment Processor - Optional)

**Purpose**: Processes on-site cash payments

**Permissions**:
- Generate tickets manually
- Mark tickets as "paid in cash"
- View transaction history
- Issue receipts

**Access**:
- Cashier interface
- Manual ticket generation
- Payment recording
- Receipt printing

**Restrictions**:
- Cannot edit events
- Cannot see full financial reports
- Cannot manage scanners

---

### 5. Participant (Ticket Buyer)

**Purpose**: Purchases and uses tickets

**Permissions**:
- View public event page
- Select ticket type
- Pay via payment gateway (PayDunya, CinetPay, MTN Momo)
- View/download ticket QR code
- Receive email/SMS confirmation
- Check ticket status

**Access Method**:
- No account required (magic link)
- Access via: `/ticket/<ticket_id>?token=<secure-token>`

**Features**:
- View event details
- Purchase tickets
- Download PDF ticket
- View ticket status (valid, used, cancelled)

---

## Core Features

### Event Management
- **Create Events**: Name, description, venue, date/time, capacity, dress code
- **Publish/Unpublish**: Control event visibility
- **Event Status**: Draft, Published, Ongoing, Completed, Cancelled
- **Multi-Venue Support**: Track events at different locations
- **Duplicate Events**: Clone events for recurring occasions

### Ticket System
- **Multiple Ticket Types**: VIP, Regular, Early Bird, etc.
- **Dynamic Pricing**: Different prices per ticket type
- **QR Code Generation**: Unique QR code per ticket
- **Ticket Status Tracking**: Pending, Paid, Cancelled, Refunded, Used, Expired
- **Bulk Operations**: Generate multiple tickets at once
- **PDF Export**: Download tickets as PDF

### Payment Integration
- **Multiple Gateways**: PayDunya, CinetPay, MTN Mobile Money
- **Cash Payments**: Manual ticket generation for on-site sales
- **Payment Tracking**: Real-time payment status updates
- **Refund Support**: Process refunds when needed
- **Payment Callbacks**: Automatic ticket activation on payment

### Access Control
- **QR Code Scanning**: Fast ticket validation
- **Entry/Exit Tracking**: Monitor attendance in real-time
- **Duplicate Detection**: Prevent ticket reuse
- **Offline Validation**: Works without internet
- **Scanner Dashboard**: Real-time attendance stats
- **Scan History**: Complete audit trail

### Analytics & Reports
- **Event Statistics**: Sales, attendance, revenue
- **Sales by Type**: Performance per ticket type
- **Sales by Date**: Trend analysis
- **Attendance Rates**: Compare sold vs scanned tickets
- **Revenue Reports**: Financial summaries
- **Export to CSV**: Download reports

### Notifications
- **Email Notifications**: Order confirmations, ticket delivery
- **SMS Notifications**: Ticket codes, event reminders
- **In-App Toasts**: Real-time feedback
- **Queue System**: Background job processing

---

## Directory Structure

```
src/
├── components/              # Reusable UI components
│   ├── common/             # Shared components (Modal, Badge, etc.)
│   │   ├── Modal.vue
│   │   ├── Badge.vue
│   │   ├── FilterBar.vue
│   │   ├── StatusBadge.vue
│   │   ├── ConfirmModal.vue
│   │   └── Tabs.vue
│   │
│   ├── layout/             # Layout components
│   │   ├── DashboardLayout.vue
│   │   ├── PublicLayout.vue
│   │   ├── Header.vue
│   │   └── Sidebar.vue
│   │
│   ├── permissions/        # Permission control components
│   │   ├── Can.vue
│   │   └── Cannot.vue
│   │
│   ├── notifications/      # Toast notification system
│   │   ├── ToastContainer.vue
│   │   └── ToastNotification.vue
│   │
│   ├── events/            # Event-specific components
│   │   ├── EventCard.vue
│   │   ├── EventFormModal.vue
│   │   ├── EventStats.vue
│   │   └── TicketTypeFormModal.vue
│   │
│   ├── tickets/           # Ticket components
│   │   ├── TicketCard.vue
│   │   ├── TicketQRCode.vue
│   │   └── TicketStatusBadge.vue
│   │
│   ├── scanners/          # Scanner components
│   │   ├── QRScanner.vue
│   │   ├── ScanResult.vue
│   │   └── ScannerStats.vue
│   │
│   ├── organizations/     # Organization components
│   │   ├── OrganizationCard.vue
│   │   └── OrganizationFormModal.vue
│   │
│   ├── users/            # User management components
│   │   ├── UserCard.vue
│   │   ├── UserFormModal.vue
│   │   └── UserRolesModal.vue
│   │
│   └── payments/         # Payment components
│       ├── PaymentMethodSelector.vue
│       └── PaymentStatusBadge.vue
│
├── composables/           # Vue 3 Composition API hooks
│   ├── useEvents.ts          # Event management logic
│   ├── useTickets.ts         # Ticket management logic
│   ├── useScanner.ts         # QR scanning logic
│   ├── usePermissions.ts     # Permission checking
│   └── useOrders.ts          # Order management
│
├── services/              # API service layer
│   ├── api.ts                # Axios instance with interceptors
│   ├── authService.ts        # Authentication (login, OTP)
│   ├── eventService.ts       # Event CRUD operations
│   ├── ticketService.ts      # Ticket operations
│   ├── ticketTypeService.ts  # Ticket type management
│   ├── orderService.ts       # Order processing
│   ├── scanService.ts        # Ticket scanning
│   ├── organizationService.ts # Organization management
│   ├── userService.ts        # User management
│   └── dashboardService.ts   # Analytics & statistics
│
├── stores/                # Pinia state management
│   ├── auth.ts           # Authentication state
│   └── notifications.ts  # Toast notifications
│
├── views/                 # Page components
│   ├── Dashboards/
│   │   ├── DashboardView.vue
│   │   ├── SuperAdminDashboard.vue
│   │   ├── OrganizerDashboard.vue
│   │   └── ScannerDashboard.vue
│   │
│   ├── Authentication/
│   │   ├── LoginView.vue
│   │   └── OTPView.vue
│   │
│   ├── Events/
│   │   ├── EventsListView.vue
│   │   ├── EventDetailView.vue
│   │   ├── EventFormView.vue
│   │   └── EventPublicView.vue
│   │
│   ├── Tickets/
│   │   ├── TicketsListView.vue
│   │   ├── TicketDetailView.vue
│   │   └── TicketPublicView.vue
│   │
│   ├── Scanners/
│   │   ├── ScannerView.vue
│   │   └── ScanHistoryView.vue
│   │
│   ├── Organizations/
│   │   ├── OrganizationsListView.vue
│   │   └── OrganizationDetailView.vue
│   │
│   ├── Users/
│   │   ├── UsersListView.vue
│   │   └── ProfileView.vue
│   │
│   ├── Payments/
│   │   ├── CheckoutView.vue
│   │   └── PaymentCallbackView.vue
│   │
│   └── Reports/
│       └── ReportsView.vue
│
├── router/               # Vue Router configuration
│   └── index.ts         # Routes with authentication guards
│
├── types/               # TypeScript type definitions
│   └── api.ts          # All API interfaces and types
│
├── utils/              # Utility functions
│   ├── dateFormatter.ts    # Date formatting
│   ├── validation.ts       # Form validation
│   ├── currency.ts         # Currency formatting
│   └── qrcode.ts          # QR code generation
│
├── config/             # Application configuration
│   ├── api.ts         # API base URL
│   └── features.ts    # Feature flags
│
├── App.vue            # Root component
├── main.ts            # Application entry point
└── style.css          # Global styles (Tailwind)
```

---

## Tech Stack

### Production Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| **vue** | ^3.5.22 | Progressive JavaScript framework |
| **vue-router** | ^4.6.3 | Official routing solution |
| **pinia** | ^3.0.4 | State management |
| **axios** | ^1.13.2 | HTTP client for API calls |
| **lucide-vue-next** | ^0.553.0 | Icon library |
| **qrcode** | ^1.5.3 | QR code generation |
| **html5-qrcode** | ^2.3.8 | QR code scanning |

### Development Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| **vite** | ^7.1.7 | Build tool and dev server |
| **typescript** | ~5.9.3 | Type checking |
| **tailwindcss** | ^3.4.1 | Utility-first CSS framework |
| **vitest** | ^4.0.13 | Unit testing framework |

---

## Architecture Patterns

### 1. Composition API with Composables

Following Vue 3 best practices:

```typescript
// composables/useEvents.ts
export function useEvents() {
  const events = ref<Event[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchEvents(filters?: EventFilters) {
    loading.value = true
    try {
      const response = await eventService.getAll(filters)
      events.value = response.data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  return {
    events: readonly(events),
    loading: readonly(loading),
    error: readonly(error),
    fetchEvents
  }
}
```

### 2. Service Layer Pattern

Abstraction of API calls:

```typescript
// services/eventService.ts
class EventService {
  async getAll(filters?: EventFilters): Promise<PaginatedResponse<Event>> {
    const params = this.buildQueryParams(filters)
    const response = await api.get('/events', { params })
    return response.data
  }

  async create(data: CreateEventData): Promise<Event> {
    const response = await api.post('/events', data)
    return response.data.data
  }
}
```

### 3. Axios Interceptor Pattern

Global request/response handling:

```typescript
// services/api.ts
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      router.push('/login')
    }
    return Promise.reject(error)
  }
)
```

### 4. Role-Based Access Control (RBAC)

Permission-based UI rendering:

```vue
<Can permission="manage_events">
  <button @click="createEvent">Create Event</button>
</Can>

<Cannot permission="manage_events">
  <p>You don't have permission to create events</p>
</Cannot>
```

Route guards:

```typescript
{
  path: '/events/new',
  component: EventFormView,
  meta: {
    requiresAuth: true,
    requiresPermission: 'manage_events'
  }
}
```

---

## State Management

### Pinia Stores

#### 1. Auth Store (`stores/auth.ts`)

**State**:
```typescript
{
  user: User | null
  isAuthenticated: boolean
  loading: boolean
  error: string | null
  otpRequested: boolean
  phoneNumber: string
}
```

**Actions**:
- `login(credentials)` - Email/password authentication
- `requestOtp(phone)` - Send OTP code
- `verifyOtp(phone, code)` - Verify OTP
- `logout()` - Clear session
- `fetchUser()` - Get current user
- `changePassword()` - Update password

**Getters**:
- `isAuthenticated` - Check if user is logged in
- `isSuperAdmin` - Check if user is super admin
- `isOrganizer` - Check if user is organizer
- `isScanner` - Check if user is scanner
- `isCashier` - Check if user is cashier

#### 2. Notifications Store (`stores/notifications.ts`)

**Methods**:
- `success(title, message)` - Show success toast
- `error(title, message)` - Show error toast
- `warning(title, message)` - Show warning toast
- `info(title, message)` - Show info toast
- `removeNotification(id)` - Close notification

---

## API Integration

### Base Configuration

```typescript
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})
```

### Available Services

| Service | Purpose | Key Methods |
|---------|---------|-------------|
| `authService` | Authentication | login, requestOtp, verifyOtp, me, logout |
| `eventService` | Event management | getAll, getById, create, update, delete, publish |
| `ticketService` | Ticket operations | getAll, getByCode, create, cancel, markAsPaid |
| `ticketTypeService` | Ticket types | getAll, create, update, activate, deactivate |
| `orderService` | Order processing | create, initializePayment, handleCallback |
| `scanService` | QR scanning | scanTicket, getMyScans, getEventSummary |
| `organizationService` | Organization CRUD | getAll, create, update, getMembers |
| `userService` | User management | getAll, create, update, assignRole |
| `dashboardService` | Analytics | getSuperAdminStats, getOrganizerStats |

---

## UI Components Documentation

### Common Components

#### 1. Modal Component

**File**: `src/components/common/Modal.vue`

**Purpose**: Reusable modal dialog for forms and confirmations

**Props**:
```typescript
{
  modelValue: boolean      // v-model for show/hide
  title: string           // Modal title
  size: 'sm' | 'md' | 'lg' | 'xl'  // Modal size
  showFooter: boolean     // Show footer with buttons
  showCancel: boolean     // Show cancel button
  showConfirm: boolean    // Show confirm button
  cancelText: string      // Cancel button text
  confirmText: string     // Confirm button text
  confirmDisabled: boolean // Disable confirm button
}
```

**Events**:
- `update:modelValue` - Close modal
- `confirm` - Confirm button clicked
- `close` - Modal closed

**Usage**:
```vue
<Modal
  v-model="showModal"
  title="Create Event"
  size="lg"
  confirm-text="Create"
  @confirm="handleCreate"
>
  <EventForm v-model="formData" />
</Modal>
```

**UI Description**:
- **Header**: Title with close button (X icon)
- **Body**: Slot for content
- **Footer**: Cancel and Confirm buttons (right-aligned)
- **Backdrop**: Semi-transparent black overlay
- **Animation**: Fade in/out transition
- **Responsive**: Adjusts to screen size

---

#### 2. StatusBadge Component

**File**: `src/components/common/StatusBadge.vue`

**Purpose**: Colored badge for status display

**Props**:
```typescript
{
  status: string  // Status text
  type: 'success' | 'error' | 'warning' | 'info' | 'neutral'
}
```

**UI Description**:
- **Success**: Green background (#10B981), white text
- **Error**: Red background (#EF4444), white text
- **Warning**: Yellow background (#F59E0B), dark text
- **Info**: Blue background (#3B82F6), white text
- **Neutral**: Gray background (#6B7280), white text
- **Shape**: Rounded pill
- **Size**: Small (px-3 py-1), text-sm

**Usage**:
```vue
<StatusBadge status="Active" type="success" />
<StatusBadge status="Cancelled" type="error" />
```

---

#### 3. FilterBar Component

**File**: `src/components/common/FilterBar.vue`

**Purpose**: Dynamic filter controls for lists

**Props**:
```typescript
{
  filters: FilterConfig[]  // Array of filter configurations
  modelValue: FilterValues // v-model for filter values
}
```

**Filter Types**:
- **Select**: Dropdown with options
- **DateRange**: Start and end date pickers
- **Search**: Text input with search icon
- **Checkbox**: Boolean filter
- **Radio**: Single selection from options

**UI Description**:
- **Layout**: Horizontal row of filters
- **Search**: Input with magnifying glass icon (left)
- **Selects**: Dropdown with chevron icon
- **Date**: Calendar icon with date picker
- **Clear Button**: Reset all filters
- **Responsive**: Stacks vertically on mobile

**Usage**:
```vue
<FilterBar
  v-model="filters"
  :filters="filterConfig"
  @change="applyFilters"
/>
```

---

### Layout Components

#### 1. DashboardLayout

**File**: `src/components/layout/DashboardLayout.vue`

**Purpose**: Main layout wrapper for authenticated pages

**Structure**:
```
┌─────────────────────────────────────┐
│            Header (fixed)            │
├───────┬─────────────────────────────┤
│       │                              │
│ Side  │      Main Content            │
│ bar   │      (router-view)           │
│       │                              │
│       │                              │
└───────┴─────────────────────────────┘
```

**Components**:
- **Header**: Logo, search, notifications, user menu
- **Sidebar**: Navigation menu (collapsible)
- **Main**: Content area with padding

**Sidebar Menu Items** (role-based):
- Dashboard (all users)
- Events (Organizer, Super Admin)
- Scanner (Scanner role)
- Tickets (Organizer, Super Admin)
- Organizations (Super Admin only)
- Users (Super Admin, Organizer)
- Reports (Organizer, Super Admin)
- Profile (all users)

---

#### 2. Header Component

**File**: `src/components/layout/Header.vue`

**UI Elements**:
```
┌────────────────────────────────────────────────────┐
│ [☰] Logo    [Search...]    [🔔] [👤 User ▼]       │
└────────────────────────────────────────────────────┘
```

**Features**:
- **Menu Toggle**: Hamburger icon (mobile)
- **Logo**: App name/logo (left)
- **Search**: Global search bar (center)
- **Notifications**: Bell icon with badge count
- **User Menu**: Avatar, name, dropdown
  - Profile
  - Settings
  - Logout

**Dropdown Menu**:
- User name and role
- Profile link
- Change password
- Logout button

---

#### 3. Sidebar Component

**File**: `src/components/layout/Sidebar.vue`

**UI Description**:
```
┌──────────────┐
│              │
│  📊 Dashboard│
│  🎫 Events   │
│  🎟️ Tickets  │
│  👤 Users    │
│  📊 Reports  │
│              │
│  ⚙️ Settings │
│              │
└──────────────┘
```

**Features**:
- **Active State**: Highlighted menu item (blue background)
- **Icons**: Lucide icons for each menu
- **Collapsible**: Toggle sidebar on mobile
- **Role Filter**: Show only permitted items
- **Hover Effect**: Light blue background on hover

---

### Permission Components

#### Can Component

**File**: `src/components/permissions/Can.vue`

**Purpose**: Render content only if user has permission

**Usage**:
```vue
<Can permission="manage_events">
  <button>Create Event</button>
</Can>
```

#### Cannot Component

**File**: `src/components/permissions/Cannot.vue`

**Purpose**: Render content only if user lacks permission

**Usage**:
```vue
<Cannot permission="manage_events">
  <p class="text-gray-500">You cannot manage events</p>
</Cannot>
```

---

### Notification Components

#### ToastNotification

**File**: `src/components/notifications/ToastNotification.vue`

**UI Description**:
```
┌────────────────────────────────────┐
│ ✓  Success Title              [X]  │
│    Success message here...         │
└────────────────────────────────────┘
```

**Types**:
- **Success**: Green left border, checkmark icon
- **Error**: Red left border, X icon
- **Warning**: Yellow left border, exclamation icon
- **Info**: Blue left border, info icon

**Features**:
- Auto-dismiss after 5 seconds (configurable)
- Close button (X)
- Slide-in animation from right
- Stacks vertically (top-right corner)

---

### Event Components

#### EventCard

**File**: `src/components/events/EventCard.vue`

**UI Description**:
```
┌──────────────────────────────────────┐
│  [Event Banner Image]                │
├──────────────────────────────────────┤
│  Event Name              [Status]    │
│  📍 Venue                            │
│  📅 Date • ⏰ Time                   │
│                                      │
│  [View Details] [Edit] [Delete]     │
└──────────────────────────────────────┘
```

**Props**:
```typescript
{
  event: Event
  showActions: boolean  // Show action buttons
}
```

**Features**:
- Banner image with fallback
- Status badge (Published, Draft, Completed)
- Venue and date icons
- Tickets sold / capacity indicator
- Action buttons (role-based)
- Hover effect (shadow)

---

#### EventFormModal

**File**: `src/components/events/EventFormModal.vue`

**Purpose**: Create or edit event

**Form Fields**:
1. **Event Name** (required)
   - Text input
   - Max 255 characters

2. **Description**
   - Textarea
   - Rich text editor (optional)

3. **Banner Image**
   - File upload
   - Preview thumbnail
   - Recommended: 1200x630px

4. **Venue** (required)
   - Text input
   - Address autocomplete (optional)

5. **Address**
   - Text input

6. **City**
   - Select dropdown or text input

7. **Country**
   - Select dropdown

8. **Date & Time** (required)
   - Start Date picker
   - End Date picker
   - Start Time picker
   - End Time picker

9. **Capacity** (required)
   - Number input
   - Min: 1

10. **Dress Code**
    - Text input
    - Examples: Formal, Casual, Business

**Buttons**:
- Cancel (gray)
- Save as Draft (blue outline)
- Publish (blue solid)

**Validation**:
- Required fields marked with *
- Date validation (end > start)
- Capacity must be positive
- Real-time validation errors

---

### Ticket Components

#### TicketCard

**File**: `src/components/tickets/TicketCard.vue`

**UI Description**:
```
┌──────────────────────────────────────┐
│  Ticket #TK-12345      [Status]      │
│  Event: Summer Festival 2025         │
│  Type: VIP                           │
│  Holder: John Doe                    │
│  Email: john@example.com             │
│  Price: $50.00                       │
│                                      │
│  [View QR] [Download] [Send Email]  │
└──────────────────────────────────────┘
```

**Features**:
- Ticket code (bold)
- Status badge (Paid, Pending, Used, Cancelled)
- Event name (clickable link)
- Ticket type badge
- Holder information
- Price with currency
- Action buttons

---

#### TicketQRCode

**File**: `src/components/tickets/TicketQRCode.vue`

**Purpose**: Display ticket QR code

**UI Description**:
```
┌──────────────────────────┐
│                          │
│    [QR Code Image]       │
│                          │
│   CODE: TK-XYZ123       │
│                          │
│  [Download QR]          │
└──────────────────────────┘
```

**Features**:
- QR code image (300x300px)
- Ticket code below QR
- Download button
- Print-friendly styling

---

### Scanner Components

#### QRScanner

**File**: `src/components/scanners/QRScanner.vue`

**Purpose**: Camera-based QR code scanner

**UI Description**:
```
┌────────────────────────────────┐
│                                │
│   [Camera Viewfinder]          │
│   ┌──────────────┐            │
│   │              │            │
│   │   Scan QR    │            │
│   │              │            │
│   └──────────────┘            │
│                                │
│  Position QR code in frame    │
│                                │
└────────────────────────────────┘
```

**Features**:
- Live camera feed
- QR detection overlay
- Auto-scan on detection
- Manual input fallback
- Camera selection (front/back)
- Flashlight toggle (mobile)

**Scan Result Display**:
```
┌────────────────────────────┐
│  ✓ VALID TICKET            │
│                            │
│  Ticket: TK-12345         │
│  Type: VIP                │
│  Holder: John Doe         │
│  Entry Time: 8:30 PM      │
│                            │
│  [✓ Allow Entry]          │
└────────────────────────────┘
```

**Error States**:
```
┌────────────────────────────┐
│  ✗ INVALID TICKET          │
│                            │
│  Reason: Already used     │
│  Used at: 8:15 PM         │
│                            │
│  [Scan Next]              │
└────────────────────────────┘
```

---

#### ScannerStats

**File**: `src/components/scanners/ScannerStats.vue`

**UI Description**:
```
┌──────────────────────────────────────┐
│  Today's Stats                       │
│  ┌────────┬────────┬────────────┐   │
│  │ Total  │ Valid  │ Invalid    │   │
│  │  156   │  142   │    14      │   │
│  └────────┴────────┴────────────┘   │
│                                      │
│  Current Attendance: 127/500        │
│  [█████████░░░░░░░] 25.4%          │
└──────────────────────────────────────┘
```

**Features**:
- Real-time stats cards
- Color-coded metrics
- Progress bar for capacity
- Auto-refresh every 30s

---

## Pages & Views Documentation

### Authentication Pages

#### LoginView

**Route**: `/login`

**UI Layout**:
```
┌────────────────────────────────┐
│                                │
│      [App Logo]                │
│                                │
│   Welcome Back                 │
│   Sign in to continue          │
│                                │
│   Email:                       │
│   [________________]           │
│                                │
│   Password:                    │
│   [________________] [👁]      │
│                                │
│   [Sign In]                    │
│                                │
│   Or                           │
│                                │
│   [Sign in with OTP]          │
│                                │
└────────────────────────────────┘
```

**Features**:
- Email/password form
- Password visibility toggle
- Remember me checkbox
- Forgot password link
- OTP login alternative
- Form validation
- Error messages

---

#### OTPView

**Route**: `/auth/otp`

**Step 1 - Request OTP**:
```
┌────────────────────────────────┐
│                                │
│      [App Logo]                │
│                                │
│   Sign in with OTP             │
│                                │
│   Phone Number:                │
│   [+_________________]         │
│                                │
│   [Send OTP]                   │
│                                │
│   [← Back to Login]           │
│                                │
└────────────────────────────────┘
```

**Step 2 - Verify OTP**:
```
┌────────────────────────────────┐
│                                │
│   Enter OTP Code               │
│                                │
│   Code sent to +1234567890    │
│                                │
│   [_] [_] [_] [_] [_] [_]    │
│                                │
│   Didn't receive code?        │
│   [Resend] (30s)              │
│                                │
│   [Verify]                     │
│                                │
└────────────────────────────────┘
```

**Features**:
- Phone number input with country code
- Auto-focus on first OTP field
- Auto-submit when all filled
- Resend OTP (with countdown)
- Clear error messages

---

### Dashboard Pages

#### SuperAdminDashboard

**Route**: `/dashboard/super-admin`

**Layout**:
```
┌─────────────────────────────────────────────┐
│  Super Admin Dashboard                       │
├──────┬──────┬──────┬──────────────────────┤
│Total │Active│Total │Total                 │
│Events│Events│Orgs  │Revenue              │
│ 150  │  42  │  25  │ $125,450            │
└──────┴──────┴──────┴──────────────────────┘

┌─────────────────────────────────────────────┐
│  Revenue Trend (Last 6 Months)              │
│  [Chart: Line graph]                        │
└─────────────────────────────────────────────┘

┌──────────────────┐ ┌─────────────────────┐
│  Recent Events   │ │ Top Organizations   │
│  • Event 1       │ │ • Org A ($50k)     │
│  • Event 2       │ │ • Org B ($30k)     │
│  • Event 3       │ │ • Org C ($25k)     │
└──────────────────┘ └─────────────────────┘
```

**Widgets**:
1. **Stats Cards** (4 cards)
   - Total Events
   - Active Events
   - Total Organizations
   - Total Revenue

2. **Revenue Chart**
   - Line chart by month
   - Filter by date range

3. **Recent Events List**
   - Last 10 events
   - Status indicators

4. **Top Organizations**
   - By revenue
   - Event count

5. **Quick Actions**
   - Create Organization
   - View All Events
   - Manage Users

---

#### OrganizerDashboard

**Route**: `/dashboard/organizer`

**Layout**:
```
┌─────────────────────────────────────────────┐
│  My Events Dashboard                         │
├──────┬──────┬──────┬──────────────────────┤
│Total │Active│Tickets│Revenue              │
│Events│Events│Sold   │                     │
│  12  │   5  │ 1,234 │ $15,450            │
└──────┴──────┴──────┴──────────────────────┘

┌─────────────────────────────────────────────┐
│  Upcoming Events                             │
│  ┌──────────────────────────────────────┐  │
│  │ Summer Festival 2025                  │  │
│  │ 📅 Jun 15, 2025 • 👥 245/500        │  │
│  │ [Manage] [View Stats]                │  │
│  └──────────────────────────────────────┘  │
└─────────────────────────────────────────────┘

┌──────────────────┐ ┌─────────────────────┐
│  Recent Orders   │ │ Sales by Type       │
│  #ORD-001        │ │ [Pie Chart]        │
│  #ORD-002        │ │                     │
└──────────────────┘ └─────────────────────┘
```

**Features**:
- My events only (filtered)
- Sales analytics
- Recent orders
- Quick create event button

---

#### ScannerDashboard

**Route**: `/dashboard/scanner`

**Layout**:
```
┌─────────────────────────────────────────────┐
│  Scanner Dashboard                           │
│                                              │
│  Current Event: Summer Festival 2025        │
│  Location: Main Entrance                    │
├──────┬──────┬──────┬──────────────────────┤
│Scans │Valid │Invalid│Current              │
│Today │      │       │Attendance           │
│ 142  │ 136  │   6   │ 127/500            │
└──────┴──────┴──────┴──────────────────────┘

┌─────────────────────────────────────────────┐
│  [SCAN QR CODE]                             │
│  ┌────────────────────────────────────┐    │
│  │                                     │    │
│  │    [Camera Viewfinder]             │    │
│  │                                     │    │
│  └────────────────────────────────────┘    │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│  Recent Scans                                │
│  ✓ 8:45 PM - John Doe (VIP)                │
│  ✓ 8:43 PM - Jane Smith (Regular)          │
│  ✗ 8:41 PM - Invalid ticket                │
└─────────────────────────────────────────────┘
```

**Features**:
- Large scan button
- Real-time stats
- Scan history (last 20)
- Sound/vibration on scan
- Offline mode indicator

---

### Event Pages

#### EventsListView

**Route**: `/events`

**UI Layout**:
```
┌─────────────────────────────────────────────┐
│  Events                      [+ New Event]  │
├─────────────────────────────────────────────┤
│  [Search...] [Status ▼] [Date ▼] [Filter] │
├─────────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐   │
│  │ [Banner] Event 1          [Active]  │   │
│  │ 📍 Venue • 📅 Jun 15, 2025         │   │
│  │ 👥 245/500 tickets                  │   │
│  │ [View] [Edit] [Delete]             │   │
│  └─────────────────────────────────────┘   │
│                                              │
│  ┌─────────────────────────────────────┐   │
│  │ [Banner] Event 2          [Draft]   │   │
│  │ 📍 Venue • 📅 Jul 20, 2025         │   │
│  │ 👥 0/1000 tickets                   │   │
│  │ [View] [Edit] [Publish]            │   │
│  └─────────────────────────────────────┘   │
├─────────────────────────────────────────────┤
│  [◀ Prev]  Page 1 of 5  [Next ▶]          │
└─────────────────────────────────────────────┘
```

**Features**:
- Event cards with thumbnail
- Filter by status, date, venue
- Search by name/description
- Bulk actions (select multiple)
- Pagination
- Create event button (role-based)
- Sort options

---

#### EventDetailView

**Route**: `/events/:id`

**Tabs Layout**:
```
┌─────────────────────────────────────────────┐
│  [◀ Back]  Summer Festival 2025  [Edit]    │
├─────────────────────────────────────────────┤
│  [Overview] [Tickets] [Sales] [Scans]      │
├─────────────────────────────────────────────┤
│                                              │
│  OVERVIEW TAB:                              │
│                                              │
│  [Event Banner - Full Width]               │
│                                              │
│  📅 Date: June 15, 2025                    │
│  ⏰ Time: 7:00 PM - 11:00 PM               │
│  📍 Venue: Central Park, New York          │
│  👥 Capacity: 500                           │
│  👔 Dress Code: Casual                     │
│                                              │
│  Description:                               │
│  Lorem ipsum dolor sit amet...             │
│                                              │
│  Status: [Published]                       │
│                                              │
└─────────────────────────────────────────────┘
```

**Tickets Tab**:
```
┌─────────────────────────────────────────────┐
│  Ticket Types                [+ Add Type]   │
├─────────────────────────────────────────────┤
│  ┌────────────────────────────────────┐    │
│  │ VIP                    $100.00     │    │
│  │ Available: 45/100                  │    │
│  │ Benefits: Front row, meet & greet  │    │
│  │ [Edit] [Deactivate]               │    │
│  └────────────────────────────────────┘    │
│                                              │
│  ┌────────────────────────────────────┐    │
│  │ Regular                 $50.00     │    │
│  │ Available: 355/400                 │    │
│  │ [Edit] [Deactivate]               │    │
│  └────────────────────────────────────┘    │
└─────────────────────────────────────────────┘
```

**Sales Tab**:
```
┌─────────────────────────────────────────────┐
│  Sales Overview                             │
├─────────────────────────────────────────────┤
│  Total Revenue: $15,450                     │
│  Tickets Sold: 245/500 (49%)               │
│                                              │
│  [Chart: Sales by Date]                    │
│                                              │
│  Recent Orders:                             │
│  #ORD-001  John Doe     $100  Paid         │
│  #ORD-002  Jane Smith   $50   Paid         │
│                                              │
│  [Export to CSV]                           │
└─────────────────────────────────────────────┘
```

**Scans Tab**:
```
┌─────────────────────────────────────────────┐
│  Scan Activity                              │
├─────────────────────────────────────────────┤
│  Current Attendance: 127/245 (51.8%)       │
│                                              │
│  Entries: 142  |  Exits: 15               │
│                                              │
│  [Chart: Scans by Hour]                    │
│                                              │
│  Recent Scans:                              │
│  ✓ 8:45 PM  TK-001  John Doe  Entry       │
│  ✓ 8:43 PM  TK-002  Jane Smith Entry      │
│  ✗ 8:41 PM  TK-XXX  Invalid               │
│                                              │
│  [Export Scan History]                     │
└─────────────────────────────────────────────┘
```

---

#### EventPublicView

**Route**: `/events/:slug` (Public)

**UI Layout**:
```
┌─────────────────────────────────────────────┐
│  [Event Banner - Hero Section]             │
│                                              │
│  Summer Festival 2025                       │
│  📅 June 15, 2025 • ⏰ 7:00 PM            │
│  📍 Central Park, New York                 │
│                                              │
│  [Get Tickets]                             │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│  About This Event                           │
│  Lorem ipsum dolor sit amet consectetur...  │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│  Available Tickets                          │
│  ┌────────────────────────────────────┐    │
│  │ VIP Ticket          $100.00        │    │
│  │ • Front row seating                │    │
│  │ • Meet & greet                     │    │
│  │ 45 available                       │    │
│  │ [Select] [Qty: 1 ▼]               │    │
│  └────────────────────────────────────┘    │
│                                              │
│  ┌────────────────────────────────────┐    │
│  │ Regular Ticket       $50.00        │    │
│  │ • General admission                │    │
│  │ 355 available                      │    │
│  │ [Select] [Qty: 1 ▼]               │    │
│  └────────────────────────────────────┘    │
│                                              │
│  [Continue to Checkout]                    │
└─────────────────────────────────────────────┘
```

**Features**:
- Hero section with banner
- Event details
- Ticket type selection
- Quantity selector
- Add to cart
- Responsive design
- Social sharing buttons

---

### Checkout & Payment Pages

#### CheckoutView

**Route**: `/checkout/:eventId/:ticketTypeId`

**UI Layout**:
```
┌─────────────────────────────────────────────┐
│  Checkout                                   │
├──────────────────────┬──────────────────────┤
│                      │  Order Summary       │
│  Customer Info       │  ────────────────   │
│                      │  Event: Summer...    │
│  Name:               │  Type: VIP          │
│  [____________]      │  Qty: 2             │
│                      │  Price: $100 × 2    │
│  Email:              │  ────────────────   │
│  [____________]      │  Subtotal: $200     │
│                      │  Fees: $10          │
│  Phone:              │  ────────────────   │
│  [____________]      │  Total: $210        │
│                      │                      │
│  Payment Method      │  [Complete Order]   │
│  ○ PayDunya         │                      │
│  ○ CinetPay         │                      │
│  ○ MTN Momo         │                      │
│                      │                      │
└──────────────────────┴──────────────────────┘
```

**Steps**:
1. **Customer Information**
   - Name (required)
   - Email (required)
   - Phone (optional)

2. **Payment Method Selection**
   - Radio buttons for gateways
   - Gateway logos

3. **Order Summary** (Sidebar)
   - Event details
   - Ticket type
   - Quantity
   - Price breakdown
   - Total

4. **Complete Order Button**
   - Redirects to payment gateway
   - Shows loading state

**Validation**:
- Email format check
- Phone number format (if provided)
- Payment method selection required

---

#### PaymentCallbackView

**Route**: `/payment/callback`

**Success State**:
```
┌─────────────────────────────────────────────┐
│                                              │
│         ✓                                   │
│                                              │
│     Payment Successful!                     │
│                                              │
│  Your tickets have been sent to:           │
│  john.doe@example.com                      │
│                                              │
│  Order #: ORD-12345                        │
│  Amount: $210.00                           │
│                                              │
│  [View Tickets] [Download PDF]             │
│                                              │
└─────────────────────────────────────────────┘
```

**Failed State**:
```
┌─────────────────────────────────────────────┐
│                                              │
│         ✗                                   │
│                                              │
│     Payment Failed                          │
│                                              │
│  Your payment could not be processed.      │
│                                              │
│  Reason: Insufficient funds                │
│                                              │
│  [Try Again] [Contact Support]             │
│                                              │
└─────────────────────────────────────────────┘
```

---

### Ticket Pages

#### TicketPublicView

**Route**: `/tickets/:code?token=xxx` (Public)

**UI Layout**:
```
┌─────────────────────────────────────────────┐
│  Your Ticket                                │
├─────────────────────────────────────────────┤
│                                              │
│  Summer Festival 2025                       │
│  VIP Ticket                                 │
│                                              │
│  ┌────────────────────────┐                │
│  │                         │                │
│  │    [QR Code]           │                │
│  │                         │                │
│  │    TK-ABC123          │                │
│  │                         │                │
│  └────────────────────────┘                │
│                                              │
│  Ticket Holder: John Doe                   │
│  Email: john@example.com                   │
│                                              │
│  Event Details:                             │
│  📅 June 15, 2025                          │
│  ⏰ 7:00 PM                                │
│  📍 Central Park, New York                 │
│                                              │
│  Status: ✓ Valid (Not yet used)           │
│                                              │
│  [Download PDF] [Add to Wallet]            │
│                                              │
└─────────────────────────────────────────────┘
```

**Features**:
- Large QR code (printable)
- Ticket details
- Event information
- Status indicator
- Download PDF button
- Add to Apple/Google Wallet
- Print-friendly styling

**Status States**:
- ✓ Valid (green)
- ⏳ Pending Payment (yellow)
- ✓ Used (gray) - shows entry time
- ✗ Cancelled (red)
- ⏰ Expired (gray)

---

### Scanner Pages

#### ScannerView

**Route**: `/scanner`

**UI Layout** (Mobile-First):
```
┌─────────────────────────────────────────────┐
│  ◀ Scanner                        [⚙️]      │
├─────────────────────────────────────────────┤
│  Event: Summer Festival 2025                │
│  Location: Main Entrance                    │
├─────────────────────────────────────────────┤
│                                              │
│  ┌──────────────────────────────────────┐  │
│  │                                       │  │
│  │                                       │  │
│  │      [Camera Viewfinder]             │  │
│  │           ┌────────┐                 │  │
│  │           │        │                 │  │
│  │           │  QR    │                 │  │
│  │           │ Target │                 │  │
│  │           │        │                 │  │
│  │           └────────┘                 │  │
│  │                                       │  │
│  │  Position QR code within frame       │  │
│  │                                       │  │
│  └──────────────────────────────────────┘  │
│                                              │
│  [📷 Switch Camera] [🔦 Flash]             │
│                                              │
│  Or enter code manually:                    │
│  [____________________] [Submit]           │
│                                              │
├─────────────────────────────────────────────┤
│  Stats Today:                               │
│  Valid: 142  Invalid: 6  Current: 127      │
└─────────────────────────────────────────────┘
```

**Scan Result Modal - Success**:
```
┌─────────────────────────────────────────────┐
│  ✓ VALID TICKET                             │
├─────────────────────────────────────────────┤
│                                              │
│  Ticket: TK-ABC123                          │
│  Type: VIP                                  │
│  Holder: John Doe                           │
│                                              │
│  Entry Time: 8:45 PM                        │
│                                              │
│  [✓ Confirm Entry]                         │
│  [✗ Deny Entry]                            │
│                                              │
└─────────────────────────────────────────────┘
```

**Scan Result Modal - Error**:
```
┌─────────────────────────────────────────────┐
│  ✗ INVALID TICKET                           │
├─────────────────────────────────────────────┤
│                                              │
│  Ticket: TK-XYZ999                          │
│                                              │
│  Reason: Already Used                      │
│  Entry Time: 8:15 PM                        │
│  Scanner: Gate B                            │
│                                              │
│  Contact supervisor if needed.             │
│                                              │
│  [Scan Next Ticket]                        │
│                                              │
└─────────────────────────────────────────────┘
```

**Features**:
- Full-screen camera mode
- Auto-scan on QR detection
- Vibration feedback on scan
- Sound effects (configurable)
- Manual code entry fallback
- Offline mode with sync
- Large, touch-friendly buttons
- Real-time stats

---

## Database Schema

### Users Table

```sql
users
- id (PK)
- name
- email (unique)
- phone (nullable)
- password (hashed)
- type (SUPER_ADMIN, ORGANIZER, SCANNER, CASHIER, PARTICIPANT)
- organization_id (FK, nullable)
- avatar (nullable)
- created_at
- updated_at
- last_login_at
```

### Organizations Table

```sql
organizations
- id (PK)
- name
- slug (unique)
- logo (nullable)
- description (nullable)
- email
- phone
- address (nullable)
- website (nullable)
- status (active, suspended, inactive)
- owner_id (FK → users)
- created_at
- updated_at
```

### Events Table

```sql
events
- id (PK)
- organization_id (FK)
- name
- slug (unique)
- description (nullable)
- banner (nullable)
- venue
- address (nullable)
- city (nullable)
- country (nullable)
- start_date
- end_date
- start_time
- end_time (nullable)
- capacity
- dress_code (nullable)
- status (draft, published, ongoing, completed, cancelled)
- is_published (boolean)
- created_by (FK → users)
- created_at
- updated_at
```

### Ticket Types Table

```sql
ticket_types
- id (PK)
- event_id (FK)
- name
- description (nullable)
- price
- currency
- quantity
- quantity_sold
- color (nullable)
- benefits (JSON, nullable)
- is_active (boolean)
- sale_start_date (nullable)
- sale_end_date (nullable)
- created_at
- updated_at
```

### Tickets Table

```sql
tickets
- id (PK)
- ticket_type_id (FK)
- event_id (FK)
- order_id (FK, nullable)
- code (unique)
- qr_code (text)
- holder_name
- holder_email
- holder_phone (nullable)
- price
- currency
- status (pending, paid, cancelled, refunded, used, expired)
- payment_method (nullable)
- payment_provider (nullable)
- payment_reference (nullable)
- payment_url (nullable)
- paid_at (nullable)
- used_at (nullable)
- scanned_by (FK → users, nullable)
- entry_time (nullable)
- exit_time (nullable)
- notes (nullable)
- created_at
- updated_at
```

### Orders Table

```sql
orders
- id (PK)
- order_number (unique)
- event_id (FK)
- customer_name
- customer_email
- customer_phone (nullable)
- total_amount
- currency
- payment_method
- payment_provider
- payment_reference (nullable)
- payment_url (nullable)
- status (pending, completed, failed, cancelled, refunded)
- paid_at (nullable)
- created_at
- updated_at
```

### Scans Table

```sql
scans
- id (PK)
- ticket_id (FK)
- event_id (FK)
- scanner_id (FK → users)
- scan_type (entry, exit)
- result (valid, invalid, already_used, expired, wrong_event)
- scanned_at
- location (nullable)
- device_info (nullable)
- notes (nullable)
```

### Roles & Permissions Tables

```sql
roles
- id (PK)
- name
- slug (unique)
- description (nullable)
- created_at
- updated_at

permissions
- id (PK)
- name
- slug (unique)
- description (nullable)
- created_at
- updated_at

role_permission (pivot)
- role_id (FK)
- permission_id (FK)

user_role (pivot)
- user_id (FK)
- role_id (FK)
```

---

## Development Guide

### Environment Setup

1. **Clone/Create Project**:
```bash
cd /home/unknow/Ticketing/ticketing-app
npm install
```

2. **Configure Environment**:
```bash
cp .env.example .env
```

Edit `.env`:
```env
VITE_API_URL=http://localhost:8000/api
VITE_PAYDUNYA_API_KEY=your_key
VITE_CINETPAY_API_KEY=your_key
VITE_CINETPAY_SITE_ID=your_site_id
VITE_MTN_MOMO_API_KEY=your_key
```

3. **Start Development Server**:
```bash
npm run dev
```

### Code Organization Best Practices

1. **Components**:
   - Keep components small and focused
   - Use PascalCase naming
   - Extract reusable logic to composables
   - Props should be typed with TypeScript

2. **Composables**:
   - One composable per domain (useEvents, useTickets)
   - Return readonly refs to prevent external mutations
   - Use `try/catch` for error handling
   - Show notifications for user feedback

3. **Services**:
   - One service per API resource
   - Use class-based pattern
   - Build query params in private methods
   - Handle FormData for file uploads

4. **Types**:
   - All types in `src/types/api.ts`
   - Use interfaces for objects
   - Use type unions for status fields
   - Export all types

5. **Styling**:
   - Use Tailwind utility classes
   - Follow design system colors
   - Mobile-first responsive design
   - Consistent spacing (p-4, p-6, etc.)

### Testing

```bash
# Run tests
npm run test

# Type check
npm run type-check
```

### Building for Production

```bash
# Build
npm run build

# Preview build
npm run preview
```

Output: `dist/` folder

---

## Deployment

### Prerequisites
- Node.js 16+ on server
- Backend API running
- Payment gateway credentials

### Build Process

```bash
# Install dependencies
npm install

# Build for production
npm run build
```

### Deployment Checklist

- [ ] Set `VITE_API_URL` to production API
- [ ] Configure payment gateway credentials
- [ ] Enable production monitoring
- [ ] Set up HTTPS
- [ ] Configure CORS on backend
- [ ] Test payment flows
- [ ] Test QR scanning
- [ ] Deploy `dist/` folder to static hosting

### Hosting Options

- **Netlify**: Drag & drop `dist/` folder
- **Vercel**: Connect Git repository
- **AWS S3 + CloudFront**: Static website hosting
- **nginx**: Serve `dist/` folder

### nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/ticketing-app/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

---

## API Endpoints Reference

### Authentication
```
POST   /api/auth/login              - Email/password login
POST   /api/auth/otp/request        - Request OTP code
POST   /api/auth/otp/verify         - Verify OTP
GET    /api/auth/me                 - Get current user
POST   /api/auth/logout             - Logout
POST   /api/auth/change-password    - Change password
```

### Events
```
GET    /api/events                  - List events
GET    /api/events/:id              - Get event
GET    /api/events/slug/:slug       - Get public event
POST   /api/events                  - Create event
PUT    /api/events/:id              - Update event
DELETE /api/events/:id              - Delete event
POST   /api/events/:id/publish      - Publish event
GET    /api/events/:id/statistics   - Event stats
```

### Tickets
```
GET    /api/tickets                 - List tickets
GET    /api/tickets/:id             - Get ticket
GET    /api/tickets/code/:code      - Get by code (public)
POST   /api/tickets                 - Create ticket
PUT    /api/tickets/:id             - Update ticket
POST   /api/tickets/:id/cancel      - Cancel ticket
POST   /api/tickets/:id/mark-paid   - Mark as paid (cashier)
GET    /api/tickets/:id/download    - Download PDF
POST   /api/tickets/:id/send-email  - Send via email
POST   /api/tickets/validate        - Validate ticket
```

### Ticket Types
```
GET    /api/events/:id/ticket-types - List types
GET    /api/ticket-types/:id        - Get type
POST   /api/ticket-types            - Create type
PUT    /api/ticket-types/:id        - Update type
DELETE /api/ticket-types/:id        - Delete type
```

### Orders
```
GET    /api/orders                  - List orders
GET    /api/orders/:id              - Get order
POST   /api/orders                  - Create order
POST   /api/orders/:id/payment/initialize - Init payment
POST   /api/orders/payment/callback - Payment callback
GET    /api/orders/:id/receipt      - Download receipt
```

### Scans
```
POST   /api/scans                   - Scan ticket
GET    /api/scans                   - List scans
GET    /api/scans/my-scans          - My scans (scanner)
GET    /api/events/:id/scans/summary - Scan summary
GET    /api/events/:id/scans/export  - Export CSV
```

### Organizations
```
GET    /api/organizations           - List orgs
GET    /api/organizations/:id       - Get org
GET    /api/organizations/me        - My org
POST   /api/organizations           - Create org
PUT    /api/organizations/:id       - Update org
DELETE /api/organizations/:id       - Delete org
```

### Users
```
GET    /api/users                   - List users
GET    /api/users/:id               - Get user
POST   /api/users                   - Create user
PUT    /api/users/:id               - Update user
DELETE /api/users/:id               - Delete user
POST   /api/users/:id/roles         - Assign role
```

### Dashboard
```
GET    /api/dashboard/super-admin   - Super admin stats
GET    /api/dashboard/organizer     - Organizer stats
GET    /api/dashboard/scanner       - Scanner stats
GET    /api/dashboard/analytics     - Analytics
```

---

## Contributing

### Workflow

1. Create feature branch: `git checkout -b feature/my-feature`
2. Make changes following code style
3. Run tests: `npm run test`
4. Type check: `npm run type-check`
5. Commit: `git commit -m "feat: add feature"`
6. Push: `git push origin feature/my-feature`
7. Create Pull Request

### Code Style

- Use TypeScript for all files
- Follow Vue 3 Composition API patterns
- Use `<script setup>` syntax
- Keep components focused
- Write self-documenting code
- Add comments for complex logic

---

## Support

For issues and questions:
- **Technical Issues**: Create GitHub issue
- **Feature Requests**: Discuss with team
- **Documentation**: Update this README

---

## License

[Specify License Here]

---

**Documentation Generated**: 2025-11-26
**Project Version**: 1.0.0
**Architecture**: Based on Sirene Vue3 Pattern
**Maintained By**: Development Team
**Project Location**: `/home/unknow/Ticketing/ticketing-app/`

---

## Next Steps for AI Development

This project is ready to be developed further by any LLM AI. To continue development:

1. **Create Missing Views**: Implement all views listed in `src/views/`
2. **Create Feature Components**: Build components in `src/components/`
3. **Add Form Validation**: Implement validation in forms
4. **Add Charts**: Integrate chart library for analytics
5. **Implement QR Scanner**: Complete scanner component
6. **Add Tests**: Write unit tests for composables and components
7. **Optimize Performance**: Implement lazy loading, caching
8. **Add Internationalization**: i18n support
9. **PWA Support**: Make app installable
10. **Offline Mode**: Implement service workers

All architecture patterns, types, services, and composables are complete and ready to use. Follow the patterns established in existing files when creating new components.
