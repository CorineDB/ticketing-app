import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notifications'

// Lazy load views
const LoginView = () => import('@/views/Authentication/LoginView.vue')
const OTPView = () => import('@/views/Authentication/OTPView.vue')
const DashboardView = () => import("@/views/Dashboard/DashboardView.vue");
const SuperAdminDashboard = () => import('@/views/Dashboard/SuperAdminDashboard.vue')
const OrganizerDashboard = () => import('@/views/Dashboard/OrganizerDashboard.vue')
const ScannerDashboard = () => import('@/views/Dashboard/ScannerDashboard.vue')

const EventsListView = () => import('@/views/Events/EventsListView.vue')
const EventDetailView = () => import('@/views/Events/EventDetailView.vue')
const EventFormView = () => import('@/views/Events/EventFormView.vue')
const EventPublicView = () => import('@/views/Events/EventPublicView.vue')
const EventsPublicListView = () => import('@/views/Events/EventsPublicListView.vue')

const TicketsListView = () => import('@/views/Tickets/TicketsListView.vue')
const TicketDetailView = () => import('@/views/Tickets/TicketDetailView.vue')
const TicketPublicView = () => import('@/views/Tickets/TicketPublicView.vue')
const CheckoutView = () => import('@/views/Payments/CheckoutView.vue')
const PaymentCallbackView = () => import('@/views/Payments/PaymentCallbackView.vue')

const ScannerView = () => import('@/views/Scanners/ScannerView.vue')
const ScanHistoryView = () => import('@/views/Scanners/ScanHistoryView.vue')

const OrganizationsListView = () => import('@/views/Organizations/OrganizationsListView.vue')
const OrganizationDetailView = () => import('@/views/Organizations/OrganizationDetailView.vue')

const UsersListView = () => import('@/views/Users/UsersListView.vue')
const ProfileView = () => import('@/views/Users/ProfileView.vue')

const ReportsView = () => import('@/views/Reports/ReportsView.vue')

const routes: RouteRecordRaw[] = [
  // Public routes
  {
    path: '/',
    name: 'home',
    component: EventsPublicListView,
    meta: { public: true }
  },
  {
    path: '/events',
    name: 'events-public',
    component: EventsPublicListView,
    meta: { public: true }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guest: true }
  },
  {
    path: '/auth/otp',
    name: 'otp',
    component: OTPView,
    meta: { guest: true }
  },
  {
    path: '/otp-login',
    name: 'otp-login',
    component: OTPView,
    meta: { guest: true }
  },
  {
    path: '/events/:slug',
    name: 'event-public',
    component: EventPublicView,
    meta: { public: true }
  },
  {
    path: '/checkout/:eventId/:ticketTypeId',
    name: 'checkout',
    component: CheckoutView,
    meta: { public: true }
  },
  {
    path: '/payment/callback',
    name: 'payment-callback',
    component: PaymentCallbackView,
    meta: { public: true }
  },
    // --- PAYMENT RESULT ROUTE (ADDED) ---
    {
      path: '/payment/result',
      name: 'payment-result',
      component: () => import('../views/Payments/PaymentResultView.vue'),
      meta: { public: true, requiresAuth: false } // Can be public as it just shows status based on URL params
    },
  {
    path: '/tickets/:code',
    name: 'ticket-public',
    component: TicketPublicView,
    meta: { public: true }
  },

  // Authenticated routes - All prefixed with /dashboard
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard/super-admin',
    name: 'super-admin-dashboard',
    component: SuperAdminDashboard,
    meta: {
      requiresAuth: true,
      requiresUserType: 'super-admin'
    }
  },
  {
    path: '/dashboard/organizer',
    name: 'organizer-dashboard',
    component: OrganizerDashboard,
    meta: {
      requiresAuth: true,
      requiresUserType: 'organizer'
    }
  },
  {
    path: '/dashboard/scanner',
    name: 'scanner-dashboard',
    component: ScannerDashboard,
    meta: {
      requiresAuth: true,
      requiresUserType: 'agent-de-controle'
    }
  },

  // Events
  {
    path: '/dashboard/events',
    name: 'events',
    component: EventsListView,
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard/events/new',
    name: 'event-create',
    component: EventFormView,
    meta: {
      requiresAuth: true,
      requiresPermission: 'manage_events'
    }
  },
  {
    path: '/dashboard/events/:id',
    name: 'event-detail',
    component: EventDetailView,
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard/events/:id/edit',
    name: 'event-edit',
    component: EventFormView,
    meta: {
      requiresAuth: true,
      requiresPermission: 'manage_events'
    }
  },

  // Tickets
  {
    path: '/dashboard/tickets',
    name: 'tickets',
    component: TicketsListView,
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard/tickets/:id',
    name: 'ticket-detail',
    component: TicketDetailView,
    meta: { requiresAuth: true }
  },

  // Scanner
  {
    path: '/dashboard/scanner',
    name: 'scanner',
    component: ScannerView,
    meta: {
      requiresAuth: true,
      requiresUserType: 'agent-de-controle'
    }
  },
  {
    path: '/dashboard/scanner/history',
    name: 'scan-history',
    component: ScanHistoryView,
    meta: {
      requiresAuth: true,
      requiresUserType: 'agent-de-controle'
    }
  },

  // Organizations (Super Admin only)
  {
    path: '/dashboard/organisateurs',
    name: 'organisateurs',
    component: OrganizationsListView,
    meta: {
      requiresAuth: true,
      requiresUserType: 'super-admin'
    }
  },
  {
    path: '/dashboard/organisateurs/:id',
    name: 'organisateur-detail',
    component: OrganizationDetailView,
    meta: {
      requiresAuth: true,
      requiresUserType: 'super-admin'
    }
  },

  // Users
  {
    path: '/dashboard/users',
    name: 'users',
    component: UsersListView,
    meta: {
      requiresAuth: true,
      requiresPermission: 'manage_users'
    }
  },
  {
    path: '/dashboard/profile',
    name: 'profile',
    component: ProfileView,
    meta: { requiresAuth: true }
  },

  // Reports
  {
    path: '/dashboard/reports',
    name: 'reports',
    component: ReportsView,
    meta: {
      requiresAuth: true,
      requiresPermission: 'view_reports'
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const notifications = useNotificationStore()

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  // Redirect authenticated users away from guest routes
  if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
    return
  }

  // Check user type requirement
  // Check user type requirement
  if (to.meta.requiresUserType && authStore.user) {
    // If the user's role slug does not match the required user type, deny access
    if (authStore.user.role.slug !== to.meta.requiresUserType) {
      notifications.error('Access Denied', 'You do not have permission to access this page')
      next({ name: 'dashboard' })
      return
    }
  }

  // Check permission requirement
  // Check permission requirement
  if (to.meta.requiresPermission && authStore.user) {
    const hasPermission = authStore.user.role.permissions?.some(
      (p) => p.slug === to.meta.requiresPermission
    )

    if (!hasPermission && !authStore.isSuperAdmin) {
      notifications.error('Access Denied', 'You do not have the required permission')
      next({ name: 'dashboard' })
      return
    }
  }

  next()
})

export default router
