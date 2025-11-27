<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-lg border border-gray-200 p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Tickets Sold</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">
            {{ stats?.total_tickets || 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            {{ calculatePercentage(stats?.tickets_sold, stats?.total_tickets) }}% of capacity
          </p>
        </div>
        <div class="p-3 bg-blue-100 rounded-lg">
          <TicketIcon class="w-6 h-6 text-blue-600" />
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Revenue</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">
            {{ formatCurrency(stats?.revenue || 0) }}
          </p>
          <p class="text-xs text-green-600 mt-1">
            {{ formatCurrency(stats?.revenue_pending || 0) }} pending
          </p>
        </div>
        <div class="p-3 bg-green-100 rounded-lg">
          <DollarSignIcon class="w-6 h-6 text-green-600" />
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Attendance</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">
            {{ stats?.tickets_used || 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            {{ calculatePercentage(stats?.tickets_used, stats?.tickets_sold) }}% attendance rate
          </p>
        </div>
        <div class="p-3 bg-purple-100 rounded-lg">
          <UsersIcon class="w-6 h-6 text-purple-600" />
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Currently Inside</p>
          <p class="text-2xl font-bold text-gray-900 mt-1">
            {{ event?.current_in || 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            {{ event?.capacity - (event?.current_in || 0) }} remaining
          </p>
        </div>
        <div class="p-3 bg-orange-100 rounded-lg">
          <ActivityIcon class="w-6 h-6 text-orange-600" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Event, EventStatistics } from '@/types/api'
import { formatCurrency } from '@/utils/formatters'
import {
  TicketIcon,
  DollarSignIcon,
  UsersIcon,
  ActivityIcon
} from 'lucide-vue-next'

defineProps<{
  event?: Event
  stats?: EventStatistics
}>()

function calculatePercentage(value?: number, total?: number): number {
  if (!value || !total || total === 0) return 0
  return Math.round((value / total) * 100)
}
</script>
