# Sirene Automatique - Frontend Documentation

## Overview

**Sirene Automatique** is a comprehensive school alarm siren management system built with Vue 3, TypeScript, and Vite. The application manages the entire lifecycle of school siren systems including subscriptions, technical interventions, breakdown management, and multi-role user access.

**Project Location**: `/home/unknow/Celerite Holding/Sirene Automatique/frontend/sirene-vue3/`

**Tech Stack**: Vue 3 + TypeScript + Vite + Pinia + Vue Router + Tailwind CSS + Axios

---

## Table of Contents

- [Quick Start](#quick-start)
- [Project Architecture](#project-architecture)
- [Directory Structure](#directory-structure)
- [Core Technologies](#core-technologies)
- [Architecture Patterns](#architecture-patterns)
- [State Management](#state-management)
- [Routing & Navigation](#routing--navigation)
- [Authentication & Authorization](#authentication--authorization)
- [API Integration](#api-integration)
- [Component System](#component-system)
- [Styling & Design](#styling--design)
- [Development](#development)
- [Build & Deployment](#build--deployment)

---

## Quick Start

### Prerequisites
- Node.js 16+
- npm or yarn

### Installation

```bash
# Navigate to project
cd "/home/unknow/Celerite Holding/Sirene Automatique/frontend/sirene-vue3/"

# Install dependencies
npm install

# Configure environment
cp .env.example .env
# Edit .env with your API URL and credentials

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
┌─────────────────────────────────────────────────────────┐
│                    Vue 3 Application                     │
├─────────────────────────────────────────────────────────┤
│  Views (52 pages) → User Interface & Page Components    │
│         ↓                                                │
│  Composables (9) → Business Logic & Reusable Hooks      │
│         ↓                                                │
│  Services (23) → API Communication Layer                 │
│         ↓                                                │
│  Axios Client → HTTP Requests with Interceptors         │
│         ↓                                                │
│  Backend API (Laravel) → Data & Business Logic          │
├─────────────────────────────────────────────────────────┤
│  State: Pinia Stores (auth, notifications)              │
│  Routing: Vue Router (40+ routes)                       │
│  Styling: Tailwind CSS                                  │
└─────────────────────────────────────────────────────────┘
```

### Design Principles

1. **Separation of Concerns**: Clear layers (Views → Composables → Services → API)
2. **Composition API First**: Modern Vue 3 patterns with `<script setup>`
3. **Type Safety**: Comprehensive TypeScript types for all API contracts
4. **Reusability**: Composables for shared business logic
5. **Single Responsibility**: Each component/service has one clear purpose

---

## Directory Structure

```
src/
├── components/          # Reusable UI components (45+ files)
│   ├── common/         # Shared utilities (Modal, Badge, FilterBar, etc.)
│   ├── layout/         # Layout wrappers (DashboardLayout, Sidebar, Header)
│   ├── permissions/    # Permission control (<Can>, <Cannot>)
│   ├── notifications/  # Toast notification system
│   └── [feature]/      # Feature-specific components
│       ├── interventions/
│       ├── pannes/
│       ├── roles/
│       ├── schools/
│       ├── sirens/
│       ├── technicians/
│       ├── users/
│       ├── missions/
│       └── calendrier/
│
├── composables/         # Vue 3 Composition API hooks (9 files)
│   ├── useAbonnements.ts       # Subscription management
│   ├── useAbonnementRules.ts   # Business rules for subscriptions
│   ├── useAsyncAction.ts       # Generic async handler
│   ├── useInterventions.ts     # Intervention logic
│   ├── useOrdresMission.ts     # Work order management
│   ├── usePannes.ts            # Breakdown management (480 lines!)
│   ├── usePermissions.ts       # Permission checking
│   ├── useSirens.ts            # Siren device management
│   └── useUsers.ts             # User management
│
├── services/            # API service layer (23 files)
│   ├── api.ts                        # Axios instance with interceptors
│   ├── authService.ts                # Authentication (OTP, login)
│   ├── abonnementService.ts          # Subscriptions
│   ├── panneService.ts               # Breakdowns
│   ├── interventionService.ts        # Interventions
│   ├── ordreMissionService.ts        # Work orders
│   ├── ecoleService.ts               # Schools
│   ├── siteService.ts                # Sites
│   ├── sireneService.ts              # Siren models
│   ├── sirenService.ts               # Siren instances
│   ├── userService.ts                # Users
│   ├── roleService.ts                # Roles & permissions
│   ├── technicienService.ts          # Technicians
│   ├── programmationService.ts       # Siren scheduling
│   ├── calendrierService.ts          # Calendars
│   ├── jourFerieService.ts           # Holidays
│   ├── cinetpayService.ts            # Payment gateway
│   ├── dashboardService.ts           # Statistics
│   ├── cityService.ts / villeService.ts  # Cities
│   └── paysService.ts                # Countries
│
├── stores/              # Pinia state management (2 stores)
│   ├── auth.ts                       # Authentication state
│   └── notifications.ts              # Toast notifications
│
├── views/               # Page components (52 files)
│   ├── Dashboards/                   # Role-specific dashboards
│   ├── Authentication/               # Login, OTP
│   ├── [Feature]View.vue             # Feature pages
│   └── [Feature]DetailPage.vue       # Detail pages
│
├── router/              # Vue Router configuration
│   └── index.ts                      # 40+ routes with guards
│
├── types/               # TypeScript type definitions
│   └── api.ts                        # 1084 lines of API types
│
├── utils/               # Utility functions
│   ├── dateFormatter.ts              # Date formatting
│   ├── logger.ts                     # Debug logging
│   ├── validation.ts                 # Form validation
│   └── userPayloadTransformer.ts     # Data transformation
│
├── config/              # Application configuration
│   ├── api.ts                        # API base URL
│   └── features.ts                   # Feature flags
│
├── App.vue              # Root component
├── main.ts              # Application entry point
└── style.css            # Global styles (Tailwind imports)
```

---

## Core Technologies

### Production Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| **vue** | ^3.5.22 | Progressive JavaScript framework |
| **vue-router** | ^4.6.3 | Official Vue.js router |
| **pinia** | ^3.0.4 | Vue 3 state management |
| **axios** | ^1.13.2 | HTTP client for API calls |
| **lucide-vue-next** | ^0.553.0 | Modern icon library |

### Development Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| **vite** | ^7.1.7 | Build tool and dev server |
| **typescript** | ~5.9.3 | Type checking |
| **tailwindcss** | ^3.4.1 | Utility-first CSS framework |
| **vitest** | ^4.0.13 | Unit testing framework |
| **@vue/test-utils** | ^2.4.6 | Vue component testing |
| **@testing-library/vue** | ^8.1.0 | User-centric testing |

---

## Architecture Patterns

### 1. Composition API with Composables

**Purpose**: Encapsulate reusable stateful logic

```typescript
// Example: usePannes.ts
export function usePannes() {
  const pannes = ref<Panne[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchPannes = async (filters?: PanneFilters) => {
    loading.value = true
    try {
      const data = await panneService.getAll(filters)
      pannes.value = data
    } catch (e) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  return {
    pannes: readonly(pannes),
    loading: readonly(loading),
    error: readonly(error),
    fetchPannes,
    // ... more methods
  }
}
```

**Usage in Components**:
```vue
<script setup lang="ts">
import { usePannes } from '@/composables/usePannes'

const { pannes, loading, fetchPannes } = usePannes()

onMounted(() => {
  fetchPannes()
})
</script>
```

### 2. Service Layer Pattern

**Purpose**: Abstract API calls with typed responses

```typescript
// Example: panneService.ts
class PanneService {
  async getAll(filters?: PanneFilters): Promise<Panne[]> {
    const queryString = this.buildQueryString(filters)
    const response = await api.get(`/pannes${queryString}`)
    return response.data.data
  }

  async declarer(sireneId: number, data: PanneDeclarationData): Promise<Panne> {
    const response = await api.post(`/sirens/${sireneId}/pannes`, data)
    return response.data.data
  }

  private buildQueryString(filters?: object): string {
    // Convert filters to URL params
  }
}

export default new PanneService()
```

### 3. Axios Interceptor Pattern

**Purpose**: Centralized request/response handling

```typescript
// Request Interceptor: Add auth token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Response Interceptor: Handle errors globally
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Token expired - redirect to login
      router.push('/login')
    }
    return Promise.reject(error)
  }
)
```

### 4. Role-Based Access Control (RBAC)

**Purpose**: Permission-based UI rendering and route protection

```vue
<!-- Conditional rendering with permissions -->
<Can permission="manage_users">
  <button @click="editUser">Edit User</button>
</Can>

<Cannot permission="delete_users">
  <p class="text-gray-500">You cannot delete users</p>
</Cannot>
```

**Route Guards**:
```typescript
{
  path: '/users',
  component: UsersView,
  meta: {
    requiresAuth: true,
    requiresPermission: 'view_users',
    requiresRole: 'admin'
  }
}
```

### 5. Modal-Driven Forms

**Purpose**: Consistent UX for CRUD operations

```vue
<!-- Parent Component -->
<template>
  <button @click="showModal = true">Create User</button>
  <UserFormModal
    v-model:show="showModal"
    :user="selectedUser"
    @saved="handleUserSaved"
  />
</template>
```

### 6. Polymorphic User Relationships

**Concept**: Users can have different account types (School, Technician, Admin)

**Implementation**:
- Fields: `user_account_type_id`, `user_account_type_type`
- Types: `App\Models\Ecole`, `App\Models\Technicien`
- Frontend transforms backend `type` field to role slugs

---

## State Management

### Pinia Stores

#### 1. Auth Store (`stores/auth.ts`)

**Responsibilities**:
- User authentication (OTP, email/password)
- Token management
- User data persistence
- Permission/role checking

**State**:
```typescript
{
  user: User | null,
  isAuthenticated: boolean,
  loading: boolean,
  error: string | null,
  otpRequested: boolean,
  phoneNumber: string
}
```

**Key Actions**:
- `requestOtp(telephone)` - Send OTP code
- `verifyOtp(telephone, otp)` - Verify and authenticate
- `login(email, password)` - Email/password login
- `logout()` - Clear session
- `fetchUser()` - Load current user from `/api/auth/me`
- `changerMotDePasse(oldPassword, newPassword)` - Change password

**Persistence**: Uses `localStorage` for `auth_token` and `auth_user`

#### 2. Notifications Store (`stores/notifications.ts`)

**Responsibilities**:
- Toast notification management
- Auto-dismiss timers

**Usage**:
```typescript
import { useNotificationStore } from '@/stores/notifications'

const notifications = useNotificationStore()

// Type-specific methods
notifications.success('User created successfully')
notifications.error('Failed to save data')
notifications.warning('Session expiring soon')
notifications.info('New features available')
```

### Local Component State

Components use Vue 3 Composition API primitives:
- `ref()` - Reactive primitive values
- `reactive()` - Reactive objects
- `computed()` - Derived state
- `watch()` - Side effects

---

## Routing & Navigation

### Router Configuration

**File**: `src/router/index.ts`
**Total Routes**: 40+

### Route Categories

| Category | Routes | Example |
|----------|--------|---------|
| **Public** | Authentication, Payment | `/login`, `/auth/otp`, `/checkout/:ecoleId/:abonnementId` |
| **Dashboards** | Role-specific dashboards | `/dashboard`, `/dashboard/technicien`, `/dashboard/pannes` |
| **Breakdowns** | Breakdown management | `/pannes`, `/pannes/:id` |
| **Interventions** | Technical interventions | `/interventions`, `/interventions/:id/rapport` |
| **Work Orders** | Mission orders | `/ordres-mission`, `/ordres-mission/:id` |
| **Schools** | School management | `/schools`, `/schools/:id`, `/my-school` |
| **Users & Roles** | User administration | `/users`, `/roles` |
| **Sirens** | Device management | `/sirens`, `/siren-models`, `/programmations` |
| **Subscriptions** | Subscription management | `/abonnements`, `/subscriptions` |
| **Technicians** | Technician management | `/technicians`, `/my-missions` |
| **Admin** | System administration | `/countries`, `/calendar`, `/settings` |

### Navigation Guards

**Authentication Guard**:
```typescript
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})
```

**Permission Guard**:
```typescript
if (to.meta.requiresPermission) {
  const hasPermission = authStore.user?.permissions?.some(
    p => p.slug === to.meta.requiresPermission
  )

  if (!hasPermission) {
    notifications.error('Insufficient permissions')
    next('/dashboard')
  }
}
```

**Role Guard**:
```typescript
if (to.meta.requiresRole) {
  if (authStore.user?.role?.slug !== to.meta.requiresRole) {
    next('/dashboard')
  }
}
```

---

## Authentication & Authorization

### Authentication Methods

#### 1. OTP (One-Time Password)
```typescript
// Step 1: Request OTP
await authStore.requestOtp('0123456789')

// Step 2: Verify OTP
await authStore.verifyOtp('0123456789', '123456')
```

#### 2. Email/Password
```typescript
await authStore.login('user@example.com', 'password')
```

### Token Management

- **Storage**: `localStorage.setItem('auth_token', token)`
- **Injection**: Axios interceptor adds `Authorization: Bearer <token>`
- **Expiry**: 401 responses trigger logout and redirect

### Authorization Model

#### User Roles
- **admin**: Full system access
- **user**: Standard user access
- **ecole**: School-specific access
- **technicien**: Technician-specific access

#### Permission System

**Backend Structure**:
```typescript
{
  user: {
    role: {
      name: "Administrator",
      slug: "admin",
      permissions: [
        { name: "Manage Users", slug: "manage_users" },
        { name: "View Reports", slug: "view_reports" }
      ]
    }
  }
}
```

**Frontend Usage**:
```vue
<Can permission="manage_users">
  <button>Edit User</button>
</Can>
```

**Composable**:
```typescript
const { can, cannot, hasRole } = usePermissions()

if (can('delete_users')) {
  // Show delete button
}

if (hasRole('admin')) {
  // Show admin panel
}
```

---

## API Integration

### Base Configuration

**Axios Instance** (`services/api.ts`):
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

### Service Pattern

Each API resource has a dedicated service class:

```typescript
// Example: userService.ts
class UserService {
  async getAll(filters?: UserFilters): Promise<User[]> {
    const response = await api.get('/users', { params: filters })
    return response.data.data
  }

  async getById(id: number): Promise<User> {
    const response = await api.get(`/users/${id}`)
    return response.data.data
  }

  async create(data: CreateUserData): Promise<User> {
    const response = await api.post('/users', data)
    return response.data.data
  }

  async update(id: number, data: UpdateUserData): Promise<User> {
    const response = await api.put(`/users/${id}`, data)
    return response.data.data
  }

  async delete(id: number): Promise<void> {
    await api.delete(`/users/${id}`)
  }
}

export default new UserService()
```

### Available Services (23)

| Service | Purpose |
|---------|---------|
| `authService` | Authentication (OTP, login, logout, me) |
| `userService` | User CRUD operations |
| `roleService` | Role and permission management |
| `ecoleService` | School management |
| `siteService` | School site management |
| `sireneService` | Siren model management |
| `sirenService` | Individual siren instances |
| `programmationService` | Siren scheduling |
| `abonnementService` | Subscription management |
| `panneService` | Breakdown declaration and management |
| `interventionService` | Technical intervention management |
| `ordreMissionService` | Work order management |
| `technicienService` | Technician data |
| `cinetpayService` | Payment gateway integration |
| `dashboardService` | Statistics and metrics |
| `calendrierService` | Calendar management |
| `calendrierScolaireService` | School calendar |
| `jourFerieService` | Holiday management |
| `cityService` / `villeService` | City lookups |
| `paysService` | Country data |

### Error Handling

**Global Error Interceptor**:
```typescript
api.interceptors.response.use(
  response => response,
  error => {
    const status = error.response?.status

    if (status === 401) {
      // Unauthorized - redirect to login
      localStorage.removeItem('auth_token')
      router.push('/login')
    } else if (status === 403) {
      // Forbidden
      notifications.error('Access denied')
    } else if (status === 500) {
      // Server error
      notifications.error('Server error occurred')
    }

    return Promise.reject(error)
  }
)
```

**Service-Level Handling**:
```typescript
// Composables catch errors from services
const { loading, error, execute } = useAsyncAction(async () => {
  return await userService.create(formData)
})

await execute()
if (!error.value) {
  notifications.success('User created successfully')
}
```

---

## Component System

### Component Categories

#### 1. Layout Components (`components/layout/`)
- **DashboardLayout.vue** - Main authenticated app wrapper
- **PublicLayout.vue** - Unauthenticated pages wrapper
- **Header.vue** - Top navigation bar
- **Sidebar.vue** - Left navigation menu

#### 2. Common Components (`components/common/`)
- **Modal.vue** - Generic modal dialog
- **ConfirmModal.vue** - Confirmation dialog
- **FilterBar.vue** - Dynamic data filtering
- **StatusBadge.vue** - Status indicators with colors
- **Badge.vue** - Generic badge component
- **LocationPicker.vue** - Geographic selection (country/city)
- **Tabs.vue** - Tab navigation

#### 3. Feature Components
Organized by domain (interventions, pannes, roles, schools, sirens, users, etc.)

Example: `components/users/`
- **UserFormModal.vue** - Create/edit user
- **UserRolesModal.vue** - Assign roles to user
- **UserCard.vue** - User display card

#### 4. Permission Components (`components/permissions/`)
```vue
<!-- Can.vue - Render if user has permission -->
<template>
  <slot v-if="hasPermission" />
</template>

<script setup lang="ts">
const props = defineProps<{ permission: string }>()
const { can } = usePermissions()
const hasPermission = computed(() => can(props.permission))
</script>
```

### Component Patterns

#### Script Setup Syntax
All components use modern Vue 3 `<script setup>`:
```vue
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

const props = defineProps<{
  user: User
  readonly?: boolean
}>()

const emit = defineEmits<{
  save: [user: User]
  cancel: []
}>()

const formData = ref({ ...props.user })

const isValid = computed(() => {
  return formData.value.name && formData.value.email
})

const handleSubmit = () => {
  emit('save', formData.value)
}
</script>
```

#### Props with TypeScript
```typescript
interface Props {
  show: boolean
  user?: User
  mode?: 'create' | 'edit'
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'create'
})
```

#### Emits Declaration
```typescript
const emit = defineEmits<{
  'update:show': [value: boolean]
  saved: [user: User]
  cancelled: []
}>()
```

---

## Styling & Design

### Tailwind CSS

**Configuration**: Default Tailwind setup with minimal customization

**Global Styles** (`src/style.css`):
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
```

### Design System

#### Color Palette
- **Primary**: Blue (`bg-blue-600`, `text-blue-600`)
- **Success**: Green (`bg-green-600`, `text-green-700`)
- **Warning**: Orange/Yellow (`bg-yellow-50`, `text-orange-600`)
- **Danger**: Red (`bg-red-600`, `text-red-700`)
- **Neutral**: Gray scale (`text-gray-900`, `bg-gray-50`)

#### Common Patterns

**Cards**:
```html
<div class="bg-white rounded-xl shadow-sm p-6">
  <!-- Content -->
</div>
```

**Buttons**:
```html
<!-- Primary -->
<button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
  Submit
</button>

<!-- Secondary -->
<button class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
  Cancel
</button>
```

**Inputs**:
```html
<input
  type="text"
  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
/>
```

**Status Badges**:
```html
<span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
  Active
</span>
```

### Icons

**Library**: Lucide Vue Next

**Usage**:
```vue
<script setup>
import { UserIcon, PlusIcon, TrashIcon } from 'lucide-vue-next'
</script>

<template>
  <UserIcon :size="20" class="text-gray-600" />
</template>
```

### Responsive Design

Mobile-first with Tailwind breakpoints:
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <!-- Auto-responsive grid -->
</div>
```

---

## Development

### Environment Variables

Create `.env` file:
```bash
# API Configuration
VITE_API_URL=http://localhost:8000/api

# Payment Gateway (CinetPay)
VITE_CINETPAY_API_KEY=your_api_key
VITE_CINETPAY_SITE_ID=your_site_id

# Feature Flags
VITE_BETA_FEATURES=false
VITE_ENABLE_MOCK_DATA=false

# Optional: Monitoring
VITE_SENTRY_DSN=
VITE_GA_TRACKING_ID=

# Node Environment
NODE_ENV=development
```

### Development Server

```bash
npm run dev
```

**Features**:
- Hot Module Replacement (HMR)
- Fast refresh for Vue components
- TypeScript type checking
- Port: 5173 (default)

### Code Organization Best Practices

1. **Keep composables focused**: Each composable should handle one domain
2. **Use TypeScript types**: Import from `@/types/api.ts`
3. **Follow naming conventions**:
   - Components: PascalCase (UserFormModal.vue)
   - Composables: camelCase with "use" prefix (useUsers.ts)
   - Services: camelCase with "Service" suffix (userService.ts)
4. **Emit events for parent communication**: Don't mutate props
5. **Use `readonly()` for composable return values**: Prevent external mutations

### Testing

**Framework**: Vitest with Vue Test Utils

**Run Tests**:
```bash
npm run test
```

**Example Test**:
```typescript
import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import UserCard from '@/components/users/UserCard.vue'

describe('UserCard', () => {
  it('renders user name', () => {
    const wrapper = mount(UserCard, {
      props: {
        user: {
          id: 1,
          name: 'John Doe',
          email: 'john@example.com'
        }
      }
    })

    expect(wrapper.text()).toContain('John Doe')
  })
})
```

---

## Build & Deployment

### Production Build

```bash
# Type check + build
npm run build

# Output: dist/
```

**Build Configuration** (`vite.config.ts`):
```typescript
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  build: {
    outDir: 'dist',
    sourcemap: false,
    minify: 'esbuild'
  }
})
```

### Preview Production Build

```bash
npm run preview
```

### Deployment Checklist

- [ ] Set `VITE_API_URL` to production API endpoint
- [ ] Configure `VITE_CINETPAY_API_KEY` and `VITE_CINETPAY_SITE_ID`
- [ ] Disable `VITE_ENABLE_MOCK_DATA`
- [ ] Enable production monitoring (Sentry, Google Analytics)
- [ ] Run `npm run build`
- [ ] Deploy `dist/` folder to static hosting (Netlify, Vercel, S3, etc.)
- [ ] Configure CORS on backend to allow frontend domain
- [ ] Set up HTTPS for production domain

### Performance Optimization

- **Code Splitting**: Vite automatically splits routes
- **Tree Shaking**: Unused code removed in production
- **Asset Optimization**: Images and fonts optimized
- **Lazy Loading**: Routes loaded on-demand

---

## Key Features

### Multi-Role Dashboards
- **Admin Dashboard**: System-wide statistics and management
- **School Dashboard**: School-specific siren status and subscriptions
- **Technician Dashboard**: Assigned missions and work orders
- **Breakdown Dashboard**: Comprehensive breakdown tracking

### Subscription Management
- **QR Code Checkout**: Generate QR codes for subscription payments
- **CinetPay Integration**: Seamless payment processing
- **Multiple Subscription Plans**: Different tiers with varying features
- **Status Tracking**: Pending, Active, Expired, Cancelled

### Breakdown Lifecycle
1. **Declaration**: School declares a breakdown
2. **Validation**: Admin validates the breakdown
3. **Order Creation**: Work order generated
4. **Technician Assignment**: Assign technician to mission
5. **Execution**: Technician performs intervention
6. **Reporting**: Intervention report submitted
7. **Closure**: Breakdown marked as resolved

### Permission-Based UI
- Dynamic menu items based on user role
- Conditional rendering of actions
- Route-level access control
- Granular permission checks

### Calendar Management
- School academic calendars
- Holiday management
- Siren programming schedules
- Recurring event patterns

---

## Troubleshooting

### Common Issues

**1. API Connection Refused**
- Verify `VITE_API_URL` in `.env`
- Ensure backend server is running
- Check CORS configuration on backend

**2. 401 Unauthorized After Login**
- Check token storage in localStorage
- Verify token format (should start with Bearer)
- Check token expiry on backend

**3. Build Errors**
- Run `npm run type-check` to identify TypeScript errors
- Clear `node_modules` and reinstall: `rm -rf node_modules && npm install`
- Check for missing dependencies

**4. HMR Not Working**
- Restart dev server
- Clear browser cache
- Check for syntax errors in Vue files

---

## Architecture Highlights

### Strengths

1. **Clear Separation of Concerns**: Views → Composables → Services → API
2. **Strong Type Safety**: Comprehensive TypeScript types (1084 lines) for all API contracts
3. **Scalable Component Structure**: 45+ components organized by feature domain
4. **Modern Vue 3**: Composition API with `<script setup>` syntax throughout
5. **Reusable Business Logic**: 9 composables promote DRY principles
6. **Robust Authentication**: Dual auth methods (OTP + email/password) with RBAC
7. **No External UI Library Bloat**: Custom components built with Tailwind
8. **Comprehensive Service Layer**: 23 services covering all API resources

### Potential Enhancements

1. **Expand Pinia Usage**: Consider moving more composable state to Pinia stores
2. **Add Unit Tests**: Increase test coverage for critical composables and services
3. **Implement Error Boundaries**: Better error recovery for component failures
4. **Add Validation Library**: Consider Vuelidate or VeeValidate for complex forms
5. **API Response Caching**: Implement request caching for frequently accessed data
6. **Optimistic Updates**: Improve UX with optimistic UI updates

---

## File Statistics

| Category | Count | Notable |
|----------|-------|---------|
| **Views** | 52 | Multiple dashboards, full CRUD pages |
| **Components** | 45+ | Organized by feature domain |
| **Composables** | 9 | `usePannes.ts` is 480 lines |
| **Services** | 23 | One per API resource |
| **TypeScript Types** | 1 file | 1084 lines of interfaces |
| **Stores** | 2 | Auth + Notifications |
| **Routes** | 40+ | With auth/permission guards |
| **Total TS/Vue Files** | 127 | - |

---

## Contributing

### Development Workflow

1. **Create Feature Branch**: `git checkout -b feature/my-feature`
2. **Make Changes**: Follow code style and patterns
3. **Test Changes**: Run `npm run test` and `npm run type-check`
4. **Commit**: Use conventional commits (feat:, fix:, docs:, etc.)
5. **Push**: `git push origin feature/my-feature`
6. **Create PR**: Submit pull request for review

### Code Style

- Use TypeScript for all new files
- Follow Vue 3 Composition API patterns
- Use `<script setup>` syntax
- Keep components small and focused
- Write self-documenting code with clear variable names
- Add JSDoc comments for complex functions

---

## Support

For issues and questions:
- **Technical Issues**: Create an issue in the project repository
- **Feature Requests**: Discuss with the team before implementation
- **Documentation**: Update this document as features evolve

---

**Documentation Generated**: 2025-11-26
**Project Version**: 1.0.0
**Maintained By**: Celerite Holding - Sirene Automatique Team
**Project Location**: `/home/unknow/Celerite Holding/Sirene Automatique/frontend/sirene-vue3/`
