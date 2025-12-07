<template>
  <DashboardLayout>
    <div class="container mx-auto px-4 py-8">
      <!-- Animated Header -->
      <div class="mb-8 animate-fade-in">
        <h1 class="text-4xl font-bold text-gray-900 mb-2 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
          Reports & Analytics
        </h1>
        <p class="text-gray-600">Analyze your event performance and track key metrics</p>
      </div>

      <!-- Filter Bar with slide animation -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-8 transform transition-all duration-300 hover:shadow-xl animate-slide-up">
        <div class="flex items-center gap-2 mb-4">
          <FilterIcon class="w-5 h-5 text-blue-600" />
          <h2 class="text-xl font-semibold text-gray-900">Filters</h2>
        </div>
        <FilterBar />
      </div>

      <!-- Stats Cards Grid with staggered animation -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Revenue Card -->
        <div 
          class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-2xl animate-fade-in-up"
          style="animation-delay: 0.1s"
        >
          <div class="flex items-center justify-between mb-4">
            <DollarSignIcon class="w-8 h-8 opacity-80" />
            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-1">
              <TrendingUpIcon class="w-4 h-4" />
            </div>
          </div>
          <div class="space-y-1">
            <p class="text-sm opacity-90">Total Revenue</p>
            <p class="text-3xl font-bold animate-count-up">
              {{ formatCurrency(salesData?.totalRevenue || 0) }}
            </p>
          </div>
        </div>

        <!-- Tickets Sold Card -->
        <div 
          class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-2xl animate-fade-in-up"
          style="animation-delay: 0.2s"
        >
          <div class="flex items-center justify-between mb-4">
            <TicketIcon class="w-8 h-8 opacity-80" />
            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-1">
              <TrendingUpIcon class="w-4 h-4" />
            </div>
          </div>
          <div class="space-y-1">
            <p class="text-sm opacity-90">Tickets Sold</p>
            <p class="text-3xl font-bold animate-count-up">
              {{ salesData?.totalTicketsSold || 0 }}
            </p>
          </div>
        </div>

        <!-- Total Scans Card -->
        <div 
          class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-2xl animate-fade-in-up"
          style="animation-delay: 0.3s"
        >
          <div class="flex items-center justify-between mb-4">
            <ScanIcon class="w-8 h-8 opacity-80" />
            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-1">
              <ActivityIcon class="w-4 h-4" />
            </div>
          </div>
          <div class="space-y-1">
            <p class="text-sm opacity-90">Total Scans</p>
            <p class="text-3xl font-bold animate-count-up">
              {{ scanActivityData?.totalScans || 0 }}
            </p>
          </div>
        </div>

        <!-- Success Rate Card -->
        <div 
          class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-2xl animate-fade-in-up"
          style="animation-delay: 0.4s"
        >
          <div class="flex items-center justify-between mb-4">
            <CheckCircleIcon class="w-8 h-8 opacity-80" />
            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-1">
              <PercentIcon class="w-4 h-4" />
            </div>
          </div>
          <div class="space-y-1">
            <p class="text-sm opacity-90">Success Rate</p>
            <p class="text-3xl font-bold animate-count-up">
              {{ calculateSuccessRate() }}%
            </p>
          </div>
        </div>
      </div>

      <!-- Reports Display Area -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Event Sales Summary -->
        <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl animate-fade-in-up" style="animation-delay: 0.5s">
          <div class="flex items-center gap-2 mb-6">
            <BarChartIcon class="w-6 h-6 text-blue-600" />
            <h2 class="text-xl font-semibold text-gray-900">Event Sales Summary</h2>
          </div>
          
          <!-- Loading State with skeleton -->
          <div v-if="loadingSales" class="space-y-4">
            <div class="animate-pulse space-y-3">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
              <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            </div>
          </div>
          
          <!-- Data Display with fade-in -->
          <div v-else-if="salesData" class="space-y-4 animate-fade-in">
            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
              <span class="text-gray-700 font-medium">Total Tickets Sold</span>
              <span class="text-2xl font-bold text-blue-600">{{ salesData.totalTicketsSold }}</span>
            </div>
            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
              <span class="text-gray-700 font-medium">Total Revenue</span>
              <span class="text-2xl font-bold text-green-600">{{ formatCurrency(salesData.totalRevenue) }}</span>
            </div>
            <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
              <span class="text-gray-700 font-medium">Average Ticket Price</span>
              <span class="text-2xl font-bold text-purple-600">
                {{ formatCurrency(salesData.totalRevenue / salesData.totalTicketsSold || 0) }}
              </span>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <InboxIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">No sales data available for selected filters.</p>
          </div>
        </div>

        <!-- Scan Activity -->
        <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl animate-fade-in-up" style="animation-delay: 0.6s">
          <div class="flex items-center gap-2 mb-6">
            <ActivityIcon class="w-6 h-6 text-purple-600" />
            <h2 class="text-xl font-semibold text-gray-900">Scan Activity</h2>
          </div>
          
          <!-- Loading State -->
          <div v-if="loadingScans" class="space-y-4">
            <div class="animate-pulse space-y-3">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
              <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            </div>
          </div>
          
          <!-- Data Display -->
          <div v-else-if="scanActivityData" class="space-y-4 animate-fade-in">
            <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
              <span class="text-gray-700 font-medium">Total Scans</span>
              <span class="text-2xl font-bold text-purple-600">{{ scanActivityData.totalScans }}</span>
            </div>
            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
              <span class="text-gray-700 font-medium">Successful Scans</span>
              <span class="text-2xl font-bold text-green-600">{{ scanActivityData.successfulScans }}</span>
            </div>
            <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
              <span class="text-gray-700 font-medium">Failed Scans</span>
              <span class="text-2xl font-bold text-red-600">{{ scanActivityData.failedScans }}</span>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-6">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600">Success Rate</span>
                <span class="text-sm font-semibold text-gray-900">{{ calculateSuccessRate() }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div 
                  class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-1000 ease-out animate-progress"
                  :style="{ width: `${calculateSuccessRate()}%` }"
                ></div>
              </div>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <InboxIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">No scan activity data available.</p>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import FilterBar from '@/components/common/FilterBar.vue'
import { formatCurrency } from '@/utils/currency'
import { useDashboard } from '@/composables/useDashboard'
import {
  FilterIcon,
  DollarSignIcon,
  TicketIcon,
  ScanIcon,
  CheckCircleIcon,
  PercentIcon,
  TrendingUpIcon,
  ActivityIcon,
  BarChartIcon,
  InboxIcon
} from 'lucide-vue-next'

const {
  salesData,
  loadingSales,
  errorSales,
  fetchSalesData,
  scanActivityData,
  loadingScans,
  errorScans,
  fetchScanActivityData
} = useDashboard()

onMounted(() => {
  fetchSalesData()
  fetchScanActivityData()
})

function calculateSuccessRate(): number {
  if (!scanActivityData.value) return 0
  const total = scanActivityData.value.totalScans
  if (total === 0) return 0
  const successful = scanActivityData.value.successfulScans
  return Math.round((successful / total) * 100)
}
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes progress {
  from {
    width: 0;
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}

.animate-slide-up {
  animation: slide-up 0.6s ease-out;
}

.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out;
  animation-fill-mode: both;
}

.animate-progress {
  animation: progress 1.5s ease-out;
}

.animate-count-up {
  animation: fade-in 0.8s ease-out;
}
</style>
