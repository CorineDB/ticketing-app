<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Organizer Dashboard</h1>
        <p class="mt-2 text-gray-600">
          Manage your events and track performance
        </p>
      </div>
      <RouterLink
        :to="{ name: 'event-create' }"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
      >
        <PlusIcon class="w-5 h-5" />
        Create Event
      </RouterLink>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatCard
        title="Total Events"
        :value="stats?.total_events || 0"
        :icon="CalendarIcon"
        color="blue"
        :loading="loading"
      />
      <StatCard
        title="Active Events"
        :value="stats?.active_events || 0"
        :icon="PlayCircleIcon"
        color="green"
        :loading="loading"
      />
      <StatCard
        title="Tickets Sold"
        :value="stats?.total_tickets_sold || 0"
        :icon="TicketIcon"
        color="purple"
        :loading="loading"
      />
      <StatCard
        title="Total Revenue"
        :value="formatCurrency(stats?.total_revenue || 0)"
        :icon="DollarSignIcon"
        color="emerald"
        :loading="loading"
      />
    </div>

    <!-- Upcoming Events -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Upcoming Events</h2>
        <RouterLink :to="{ name: 'events' }" class="text-sm text-blue-600 hover:text-blue-700">
          View all
        </RouterLink>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="i in 3" :key="i" class="animate-pulse">
          <div class="h-48 bg-gray-200 rounded-lg"></div>
        </div>
      </div>

      <div v-else-if="upcomingEvents.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <EventCard
          v-for="event in upcomingEvents"
          :key="event.id"
          :event="event"
        />
      </div>

      <div v-else class="text-center py-12">
        <CalendarIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
        <h3 class="text-lg font-medium text-gray-900 mb-1">No upcoming events</h3>
        <p class="text-gray-600 mb-4">Create your first event to get started</p>
        <RouterLink
          :to="{ name: 'event-create' }"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="w-5 h-5" />
          Create Event
        </RouterLink>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Recent Orders</h2>
        <RouterLink to="/orders" class="text-sm text-blue-600 hover:text-blue-700">
          View all
        </RouterLink>
      </div>

      <div v-if="loading" class="space-y-3">
        <div v-for="i in 5" :key="i" class="animate-pulse">
          <div class="h-16 bg-gray-200 rounded"></div>
        </div>
      </div>

      <div v-else-if="recentOrders.length > 0" class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Order #</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Event</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Customer</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Amount</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Status</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="order in recentOrders"
              :key="order.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="py-3 px-4 text-sm text-gray-900">#{{ order.order_number }}</td>
              <td class="py-3 px-4 text-sm text-gray-900">{{ order.event?.name }}</td>
              <td class="py-3 px-4 text-sm text-gray-600">{{ order.customer_name }}</td>
              <td class="py-3 px-4 text-sm font-medium text-gray-900">
                {{ formatCurrency(order.total_amount) }}
              </td>
              <td class="py-3 px-4">
                <StatusBadge :status="order.status" type="order" />
              </td>
              <td class="py-3 px-4 text-sm text-gray-600">
                {{ formatDate(order.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-8 text-gray-500">
        No recent orders
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import dashboardService from '@/services/dashboardService'
import { formatCurrency, formatDate } from '@/utils/formatters'
import StatCard from '@/components/dashboard/StatCard.vue'
import EventCard from '@/components/events/EventCard.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import {
  CalendarIcon,
  PlayCircleIcon,
  TicketIcon,
  DollarSignIcon,
  PlusIcon
} from 'lucide-vue-next'

const stats = ref<any>(null)
const upcomingEvents = ref<any[]>([])
const recentOrders = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const data = await dashboardService.getOrganizerDashboard()
    stats.value = data.stats
    upcomingEvents.value = data.upcoming_events || []
    recentOrders.value = data.recent_orders || []
  } catch (error) {
    console.error('Failed to fetch dashboard:', error)
  } finally {
    loading.value = false
  }
})
</script>
