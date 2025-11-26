import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { requiresAuth: false },
    },
    {
      path: '/',
      name: 'home',
      component: () => import('../views/HomeView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/ticket/:id',
      name: 'ticket-view',
      component: () => import('../views/TicketView.vue'),
      props: true,
      meta: { requiresAuth: false },
    },
    {
      path: '/admin',
      component: () => import('../views/admin/AdminLayout.vue'),
      meta: { requiresAuth: true, role: 'super_admin' },
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore()
        if (authStore.superAdmin) {
          next()
        } else {
          next({ name: 'home' }) // Or a specific 'access-denied' page
        }
      },
      children: [
        {
          path: 'events',
          name: 'admin-manage-events',
          component: () => import('../views/admin/ManageEvents.vue'),
        },
        {
          path: 'organizers',
          name: 'admin-manage-organizers',
          component: () => import('../views/admin/ManageOrganizers.vue'),
        },
        {
          path: 'stats',
          name: 'admin-stats',
          component: () => import('../views/admin/GlobalStats.vue'),
        },
      ],
    },
    {
      path: '/organizer',
      component: () => import('../views/organizer/OrganizerLayout.vue'),
      meta: { requiresAuth: true, role: 'organizer' },
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore()
        if (authStore.organizer) {
          next()
        } else {
          next({ name: 'home' })
        }
      },
      children: [
        {
          path: 'dashboard',
          name: 'organizer-dashboard',
          component: () => import('../views/organizer/OrganizerDashboard.vue'),
        },
        {
          path: 'events/new',
          name: 'organizer-create-event',
          component: () => import('../views/organizer/EventEditor.vue'),
        },
        {
          path: 'events/edit/:id',
          name: 'organizer-edit-event',
          component: () => import('../views/organizer/EventEditor.vue'),
          props: true,
        },
        {
          path: 'staff',
          name: 'organizer-manage-staff',
          component: () => import('../views/organizer/ManageStaff.vue'),
        },
        {
          path: 'events/:id',
          name: 'organizer-event-detail',
          component: () => import('../views/organizer/EventDetail.vue'),
          props: true,
        },
      ],
    },
     {
      path: '/scan',
      name: 'scan',
      component: () => import('../views/controller/ScanView.vue'),
      meta: { requiresAuth: true, role: 'controller' },
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore()
        if (authStore.controller) {
          next()
        } else {
          next({ name: 'home' })
        }
      },
    },
  ],
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const requiresAuth = to.meta.requiresAuth

  if (requiresAuth && !authStore.isLoggedIn) {
    next({ name: 'login' })
  } else if (to.name === 'login' && authStore.isLoggedIn) {
    next({ name: 'home' })
  } else {
    next()
  }
})

export default router
