<template>
  <DashboardLayout>
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Scan History</h1>

      <!-- Filter/Search Bar -->
      <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <FilterBar /> <!-- This component needs to be created or adapted -->
      </div>

      <!-- Scan History List -->
      <div class="bg-white rounded-xl shadow-lg">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Timestamp
                </th>
                <th
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Ticket ID
                </th>
                <th
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Event
                </th>
                <th
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Outcome
                </th>
                <th
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Scanned By
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading">
                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                  Loading scan history...
                </td>
              </tr>
              <tr v-else-if="scanHistory.length === 0">
                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                  No scan history found.
                </td>
              </tr>
              <tr v-else v-for="scan in scanHistory" :key="scan.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDateTime(scan.timestamp) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ scan.ticketId }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ scan.eventTitle }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <StatusBadge :status="scan.outcome" /> <!-- This component needs to be created or adapted -->
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ scan.scannerName }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination will go here -->
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import FilterBar from '@/components/common/FilterBar.vue' // This component needs to be created or adapted
import StatusBadge from '@/components/common/StatusBadge.vue' // This component needs to be created or adapted
import { useScanner } from '@/composables/useScanner'
import { formatDateTime } from '@/utils/dateFormatter' // Assuming a datetime formatter utility

const { scanHistory, loading, error, fetchScanHistory } = useScanner()

onMounted(() => {
  fetchScanHistory()
})

// Pagination and filter logic will be added here later
</script>

<style scoped>
/* Scoped styles for this component */
</style>
