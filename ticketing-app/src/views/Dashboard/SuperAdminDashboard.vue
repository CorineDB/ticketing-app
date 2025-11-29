<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Super Admin Dashboard</h1>
      <p class="mt-2 text-gray-600">
        Overview of all organisateurs, events, and system metrics
      </p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatCard
        title="Total Organizations"
        :value="stats?.total_organisateurs || 0"
        :icon="BuildingIcon"
        color="blue"
        :loading="loading"
      />
      <StatCard
        title="Total Events"
        :value="stats?.total_events || 0"
        :icon="CalendarIcon"
        color="purple"
        :loading="loading"
      />
      <StatCard
        title="Tickets Sold"
        :value="stats?.total_tickets_sold || 0"
        :icon="TicketIcon"
        color="green"
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

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Revenue by Month -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend</h3>
        <div v-if="loading" class="h-64 flex items-center justify-center">
          <LoaderIcon class="w-8 h-8 animate-spin text-gray-400" />
        </div>
        <div v-else-if="stats?.revenue_by_month" class="h-64">
          <div class="space-y-2">
            <div
              v-for="item in stats.revenue_by_month.slice(0, 6)"
              :key="item.month"
              class="flex items-center justify-between"
            >
              <span class="text-sm text-gray-600">{{ item.month }}</span>
              <span class="text-sm font-medium text-gray-900">
                {{ formatCurrency(item.revenue) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Events Distribution -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Status</h3>
        <div v-if="loading" class="h-64 flex items-center justify-center">
          <LoaderIcon class="w-8 h-8 animate-spin text-gray-400" />
        </div>
        <div v-else class="grid grid-cols-2 gap-4">
          <div class="text-center p-4 bg-blue-50 rounded-lg">
            <div class="text-3xl font-bold text-blue-600">
              {{ stats?.active_events || 0 }}
            </div>
            <div class="text-sm text-gray-600 mt-1">Active</div>
          </div>
          <div class="text-center p-4 bg-green-50 rounded-lg">
            <div class="text-3xl font-bold text-green-600">
              {{ stats?.upcoming_events || 0 }}
            </div>
            <div class="text-sm text-gray-600 mt-1">Upcoming</div>
          </div>
          <div class="text-center p-4 bg-purple-50 rounded-lg">
            <div class="text-3xl font-bold text-purple-600">
              {{ stats?.completed_events || 0 }}
            </div>
            <div class="text-sm text-gray-600 mt-1">Completed</div>
          </div>
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-3xl font-bold text-gray-600">
              {{ stats?.draft_events || 0 }}
            </div>
            <div class="text-sm text-gray-600 mt-1">Draft</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top Organizations -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Top Organizations</h3>
          <RouterLink :to="{ name: 'organisateurs' }" class="text-sm text-blue-600 hover:text-blue-700">
            View all
          </RouterLink>
        </div>
        <div v-if="loading" class="space-y-3">
          <div v-for="i in 5" :key="i" class="animate-pulse">
            <div class="h-12 bg-gray-200 rounded"></div>
          </div>
        </div>
        <div v-else-if="stats?.top_organisateurs" class="space-y-3">
          <div
            v-for="org in stats.top_organisateurs"
            :key="org.id"
            class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-semibold text-sm">
                  {{ org.name.substring(0, 2).toUpperCase() }}
                </span>
              </div>
              <div>
                <div class="font-medium text-gray-900">{{ org.name }}</div>
                <div class="text-sm text-gray-500">{{ org.events_count }} events</div>
              </div>
            </div>
            <div class="text-right">
              <div class="font-semibold text-gray-900">
                {{ formatCurrency(org.revenue) }}
              </div>
              <div class="text-sm text-gray-500">revenue</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Events -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Recent Events</h3>
          <RouterLink :to="{ name: 'events' }" class="text-sm text-blue-600 hover:text-blue-700">
            View all
          </RouterLink>
        </div>
        <div v-if="loading" class="space-y-3">
          <div v-for="i in 5" :key="i" class="animate-pulse">
            <div class="h-12 bg-gray-200 rounded"></div>
          </div>
        </div>
        <div v-else-if="stats?.recent_events" class="space-y-3">
          <RouterLink
            v-for="event in stats.recent_events"
            :key="event.id"
            :to="`dashboard/events/${event.id}`"
            class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50"
          >
            <div>
              <div class="font-medium text-gray-900">{{ event.title }}</div>
              <div class="text-sm text-gray-500">
                {{ formatDate(event.start_datetime) }} • {{ event.organisateur?.name }}
              </div>
            </div>
            <StatusBadge :status="event.status" type="event" />
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import dashboardService from '@/services/dashboardService'
import { formatCurrency, formatDate } from '@/utils/formatters'
import StatusBadge from '@/components/common/StatusBadge.vue'
import StatCard from '@/components/dashboard/StatCard.vue'
import {
  BuildingIcon,
  CalendarIcon,
  TicketIcon,
  DollarSignIcon,
  LoaderIcon
} from 'lucide-vue-next'

const stats = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    stats.value = await dashboardService.getSuperAdminStats()
  } catch (error) {
    console.error('Failed to fetch dashboard stats:', error)
  } finally {
    loading.value = false
  }
})
</script>
