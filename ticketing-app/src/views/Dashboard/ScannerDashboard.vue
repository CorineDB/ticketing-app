<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Scanner Dashboard</h1>
      <p class="mt-2 text-gray-600">
        Quick access to scanning and today's activity
      </p>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <RouterLink
        to="/scanner"
        class="group bg-gradient-to-br from-blue-600 to-purple-600 text-white rounded-xl p-8 hover:shadow-lg transition-all"
      >
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-bold mb-2">Scan Tickets</h3>
            <p class="text-blue-100">Start scanning QR codes</p>
          </div>
          <ScanIcon class="w-12 h-12 opacity-80 group-hover:scale-110 transition-transform" />
        </div>
      </RouterLink>

      <RouterLink
        to="/scanner/history"
        class="bg-white border border-gray-200 rounded-xl p-8 hover:shadow-lg transition-all group"
      >
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Scan History</h3>
            <p class="text-gray-600">View your past scans</p>
          </div>
          <HistoryIcon class="w-12 h-12 text-gray-400 group-hover:text-gray-600 transition-colors" />
        </div>
      </RouterLink>
    </div>

    <!-- Today's Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <StatCard
        title="Scans Today"
        :value="stats?.total_scans_today || 0"
        :icon="ScanIcon"
        color="blue"
        :loading="loading"
      />
      <StatCard
        title="Valid Scans"
        :value="stats?.valid_scans || 0"
        :icon="CheckCircleIcon"
        color="green"
        :loading="loading"
      />
      <StatCard
        title="Invalid Scans"
        :value="stats?.invalid_scans || 0"
        :icon="XCircleIcon"
        color="red"
        :loading="loading"
      />
      <StatCard
        title="Current Inside"
        :value="stats?.current_attendance || 0"
        :icon="UsersIcon"
        color="purple"
        :loading="loading"
      />
    </div>

    <!-- Current Event Info -->
    <div v-if="currentEvent" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Current Event</h2>
        <StatusBadge :status="currentEvent.status" type="event" />
      </div>

      <div class="flex flex-col md:flex-row gap-6">
        <div v-if="currentEvent.banner" class="w-full md:w-48 h-32 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
          <img :src="currentEvent.banner" :alt="currentEvent.name" class="w-full h-full object-cover" />
        </div>

        <div class="flex-1">
          <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ currentEvent.name }}</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="flex items-center gap-2 text-gray-600">
              <CalendarIcon class="w-5 h-5" />
              <span>{{ formatDate(currentEvent.start_date) }}</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <MapPinIcon class="w-5 h-5" />
              <span>{{ currentEvent.venue }}</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <TicketIcon class="w-5 h-5" />
              <span>{{ currentEvent.tickets_sold }} tickets sold</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <UsersIcon class="w-5 h-5" />
              <span>{{ currentEvent.current_in }}/{{ currentEvent.capacity }} inside</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Scans -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Recent Scans</h2>

      <div v-if="loading" class="space-y-3">
        <div v-for="i in 5" :key="i" class="animate-pulse">
          <div class="h-16 bg-gray-200 rounded"></div>
        </div>
      </div>

      <div v-else-if="recentScans.length > 0" class="space-y-3">
        <div
          v-for="scan in recentScans"
          :key="scan.id"
          class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50"
        >
          <div class="flex items-center gap-4">
            <div :class="[
              'w-12 h-12 rounded-full flex items-center justify-center',
              scan.result === 'valid' ? 'bg-green-100' : 'bg-red-100'
            ]">
              <component
                :is="scan.result === 'valid' ? CheckCircleIcon : XCircleIcon"
                :class="[
                  'w-6 h-6',
                  scan.result === 'valid' ? 'text-green-600' : 'text-red-600'
                ]"
              />
            </div>
            <div>
              <div class="font-medium text-gray-900">{{ scan.ticket?.buyer_name }}</div>
              <div class="text-sm text-gray-500">
                {{ scan.ticket?.ticket_type?.name }} • {{ formatTime(scan.scanned_at) }}
              </div>
            </div>
          </div>
          <div class="text-right">
            <Badge
              :variant="scan.result === 'valid' ? 'success' : 'danger'"
            >
              {{ scan.result }}
            </Badge>
            <div class="text-sm text-gray-500 mt-1">
              {{ scan.scan_type }}
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-8 text-gray-500">
        No scans yet today
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import dashboardService from '@/services/dashboardService'
import { formatDate, formatTime } from '@/utils/formatters'
import StatCard from '@/components/dashboard/StatCard.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import Badge from '@/components/common/Badge.vue'
import {
  ScanIcon,
  HistoryIcon,
  CheckCircleIcon,
  XCircleIcon,
  UsersIcon,
  CalendarIcon,
  MapPinIcon,
  TicketIcon
} from 'lucide-vue-next'

const stats = ref<any>(null)
const currentEvent = ref<any>(null)
const recentScans = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const data = await dashboardService.getScannerDashboard()
    stats.value = data.stats
    currentEvent.value = data.current_event
    recentScans.value = data.recent_scans || []
  } catch (error) {
    console.error('Failed to fetch dashboard:', error)
  } finally {
    loading.value = false
  }
})
</script>
