<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Scan History</h1>
          <p class="mt-2 text-gray-600">View all ticket scans and access logs</p>
        </div>
        <button
          @click="exportScans"
          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 flex items-center gap-2"
        >
          <DownloadIcon class="w-5 h-5" />
          Export
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- Search -->
          <div class="lg:col-span-2">
            <div class="relative">
              <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search by ticket ID or user..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                @input="debouncedSearch"
              />
            </div>
          </div>

          <!-- Event Filter -->
          <select
            v-model="filters.event_id"
            @change="fetchScans"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Events</option>
            <option v-for="event in events" :key="event.id" :value="event.id">
              {{ event.name }}
            </option>
          </select>

          <!-- Gate Filter -->
          <select
            v-model="filters.gate_id"
            @change="fetchScans"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Gates</option>
            <option v-for="gate in gates" :key="gate.id" :value="gate.id">
              {{ gate.name }}
            </option>
          </select>

          <!-- Type Filter -->
          <select
            v-model="filters.type"
            @change="fetchScans"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Types</option>
            <option value="entry">Entry</option>
            <option value="exit">Exit</option>
          </select>
        </div>

        <!-- Date Range -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          <div>
            <label for="start_date" class="block text-xs text-gray-600 mb-1">From Date</label>
            <input
              id="start_date"
              v-model="filters.start_date"
              type="date"
              @change="fetchScans"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="end_date" class="block text-xs text-gray-600 mb-1">To Date</label>
            <input
              id="end_date"
              v-model="filters.end_date"
              type="date"
              @change="fetchScans"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-100 rounded-lg">
              <ScanIcon class="w-5 h-5 text-blue-600" />
            </div>
            <div>
              <div class="text-sm text-gray-600">Total Scans</div>
              <div class="text-2xl font-semibold text-gray-900">{{ scans.length }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-green-100 rounded-lg">
              <CheckCircleIcon class="w-5 h-5 text-green-600" />
            </div>
            <div>
              <div class="text-sm text-gray-600">Successful</div>
              <div class="text-2xl font-semibold text-gray-900">{{ successfulScans }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-red-100 rounded-lg">
              <XCircleIcon class="w-5 h-5 text-red-600" />
            </div>
            <div>
              <div class="text-sm text-gray-600">Failed</div>
              <div class="text-2xl font-semibold text-gray-900">{{ failedScans }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-purple-100 rounded-lg">
              <UsersIcon class="w-5 h-5 text-purple-600" />
            </div>
            <div>
              <div class="text-sm text-gray-600">Unique Visitors</div>
              <div class="text-2xl font-semibold text-gray-900">{{ uniqueVisitors }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Results Count -->
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
          {{ scans.length }} scan(s) found
        </div>
        <button
          @click="clearFilters"
          class="text-sm text-blue-600 hover:text-blue-700"
        >
          Clear Filters
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-12 text-center text-gray-500">
          <LoaderIcon class="w-8 h-8 animate-spin mx-auto mb-2" />
          Loading scan history...
        </div>
      </div>

      <!-- Scans Table -->
      <div v-else-if="scans.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Time</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Ticket</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Event</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Gate</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Scanner</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Type</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="scan in scans"
                :key="scan.id"
                class="border-b border-gray-100 hover:bg-gray-50"
              >
                <td class="py-3 px-4 text-sm text-gray-900">
                  {{ formatDateTime(scan.scanned_at) }}
                </td>
                <td class="py-3 px-4">
                  <div class="text-sm font-mono text-gray-900">#{{ scan.ticket?.id }}</div>
                  <div class="text-xs text-gray-500">{{ scan.ticket?.buyer_email }}</div>
                </td>
                <td class="py-3 px-4 text-sm text-gray-600">
                  {{ scan.event?.name }}
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <DoorOpenIcon class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-900">{{ scan.gate?.name }}</span>
                  </div>
                </td>
                <td class="py-3 px-4 text-sm text-gray-600">
                  {{ scan.scanner_user?.name || '-' }}
                </td>
                <td class="py-3 px-4">
                  <Badge :variant="scan.type === 'entry' ? 'success' : 'warning'">
                    {{ scan.type }}
                  </Badge>
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <CheckCircleIcon v-if="scan.status === 'success'" class="w-4 h-4 text-green-600" />
                    <XCircleIcon v-else class="w-4 h-4 text-red-600" />
                    <span :class="[
                      'text-sm',
                      scan.status === 'success' ? 'text-green-600' : 'text-red-600'
                    ]">
                      {{ scan.status }}
                    </span>
                  </div>
                  <div v-if="scan.error_message" class="text-xs text-red-500 mt-1">
                    {{ scan.error_message }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <HistoryIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No scans found</h3>
        <p class="text-gray-600 mb-6">No scans match your current filters</p>
        <button
          @click="clearFilters"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
        >
          Clear Filters
        </button>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useScans } from '@/composables/useScans'
import { useEvents } from '@/composables/useEvents'
import { useGates } from '@/composables/useGates'
import { formatDateTime } from '@/utils/formatters'
import type { ScanFilters } from '@/types/api'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Badge from '@/components/common/Badge.vue'
import {
  DownloadIcon,
  SearchIcon,
  ScanIcon,
  CheckCircleIcon,
  XCircleIcon,
  UsersIcon,
  LoaderIcon,
  HistoryIcon,
  DoorOpenIcon
} from 'lucide-vue-next'

const { scans, loading, fetchScans, exportScans: exportScansData } = useScans()
const { events, fetchEvents } = useEvents()
const { gates, fetchGates } = useGates()

const filters = ref<ScanFilters>({
  search: '',
  event_id: '',
  gate_id: '',
  type: '',
  start_date: '',
  end_date: ''
})

let searchTimeout: any = null

const successfulScans = computed(() => {
  return scans.value.filter(s => s.status === 'success').length
})

const failedScans = computed(() => {
  return scans.value.filter(s => s.status !== 'success').length
})

const uniqueVisitors = computed(() => {
  const uniqueTickets = new Set(scans.value.map(s => s.ticket_id))
  return uniqueTickets.size
})

onMounted(() => {
  loadData()
})

async function loadData() {
  await Promise.all([
    fetchScans(filters.value),
    fetchEvents({}),
    fetchGates({})
  ])
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchScans(filters.value)
  }, 500)
}

function clearFilters() {
  filters.value = {
    search: '',
    event_id: '',
    gate_id: '',
    type: '',
    start_date: '',
    end_date: ''
  }
  fetchScans(filters.value)
}

async function exportScans() {
  await exportScansData(filters.value)
}
</script>
