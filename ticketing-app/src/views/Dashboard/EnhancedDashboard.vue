<template>
  <DashboardLayout>
    <div class="max-w-7xl mx-auto p-6 space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">📊 Enhanced Dashboard Demo</h1>
        <p class="text-gray-600">Real-time animated components with broadcasting integration</p>
      </div>

      <!-- Stats Cards Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <AnimatedStatCard
          label="Total Events"
          :value="stats.totalEvents"
          :icon="CalendarIcon"
          color="blue"
          :trend="12.5"
        />
        <AnimatedStatCard
          label="Revenue"
          :value="stats.revenue"
          :icon="DollarSignIcon"
          color="green"
          prefix="XOF "
          format="currency"
          :trend="8.3"
        />
        <AnimatedStatCard
          label="Tickets Sold"
          :value="stats.ticketsSold"
          :icon="TicketIcon"
          color="purple"
          :trend="15.7"
        />
        <AnimatedStatCard
          label="Active Users"
          :value="stats.activeUsers"
          :icon="UsersIcon"
          color="orange"
          :trend="5.2"
        />
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <RealtimeChart
          title="Sales Today"
          :data="salesData"
          :is-live="true"
          color="#3B82F6"
        />
        <div class="grid grid-cols-2 gap-4">
          <CapacityGauge
            title="Event Capacity"
            :current="capacityData.current"
            :max="capacityData.max"
          />
          <div class="space-y-4">
            <ProgressBar
              label="VIP Tickets"
              :current="150"
              :max="200"
            />
            <ProgressBar
              label="Standard Tickets"
              :current="450"
              :max="500"
            />
            <ProgressBar
              label="Student Tickets"
              :current="95"
              :max="100"
            />
          </div>
        </div>
      </div>

      <!-- Activity Feed -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <ActivityFeed
          title="Recent Activity"
          :activities="activities"
        />
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div class="space-y-3">
            <button
              @click="simulateTicketPurchase"
              class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2"
            >
              <TicketIcon class="w-5 h-5" />
              Simulate Ticket Purchase
            </button>
            <button
              @click="simulateTicketScan"
              class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2"
            >
              <ScanIcon class="w-5 h-5" />
              Simulate Ticket Scan
            </button>
            <button
              @click="simulateCapacityAlert"
              class="w-full px-4 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center justify-center gap-2"
            >
              <AlertTriangleIcon class="w-5 h-5" />
              Simulate Capacity Alert
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import AnimatedStatCard from '@/components/dashboard/AnimatedStatCard.vue'
import ActivityFeed from '@/components/dashboard/ActivityFeed.vue'
import RealtimeChart from '@/components/dashboard/RealtimeChart.vue'
import ProgressBar from '@/components/dashboard/ProgressBar.vue'
import CapacityGauge from '@/components/dashboard/CapacityGauge.vue'
import { echo } from '@/services/websocket'
import {
  CalendarIcon,
  DollarSignIcon,
  TicketIcon,
  UsersIcon,
  ScanIcon,
  AlertTriangleIcon
} from 'lucide-vue-next'
import api from '@/services/api'

const stats = ref({
  totalEvents: 1234,
  revenue: 2500000,
  ticketsSold: 5678,
  activeUsers: 234
})

const salesData = ref<Array<{ label: string; value: number }>>([
  { label: '08:00', value: 120 },
  { label: '09:00', value: 150 },
  { label: '10:00', value: 180 },
  { label: '11:00', value: 220 },
  { label: '12:00', value: 250 }
])

const capacityData = ref({
  current: 425,
  max: 500
})

const activities = ref<Array<{
  id: string
  type: 'purchase' | 'scan' | 'alert' | 'event' | 'other'
  icon: string
  message: string
  timestamp: Date
  amount?: number
}>>([])

onMounted(() => {
  setupBroadcastListeners()
  startSimulation()
})

onUnmounted(() => {
  echo.leave('events')
  echo.leave('scans')
  echo.leave('alerts')
})

function setupBroadcastListeners() {
  // Listen to ticket purchases
  echo.channel('events')
    .listen('.ticket.purchased', (e: any) => {
      stats.value.ticketsSold++
      stats.value.revenue += e.amount
      
      addActivity({
        type: 'purchase',
        icon: '🎫',
        message: `${e.buyer_name} purchased a ticket for ${e.event_title}`,
        amount: e.amount
      })
      
      addSalesDataPoint(e.amount)
    })

  // Listen to ticket scans
  echo.channel('scans')
    .listen('.ticket.scanned', (e: any) => {
      capacityData.value.current = e.current_attendance
      
      addActivity({
        type: 'scan',
        icon: '✅',
        message: `Ticket scanned: ${e.buyer_name} (${e.result})`
      })
    })

  // Listen to capacity alerts
  echo.channel('alerts')
    .listen('.event.capacity.alert', (e: any) => {
      addActivity({
        type: 'alert',
        icon: '⚠️',
        message: e.message
      })
    })
}

function addActivity(activity: Omit<typeof activities.value[0], 'id' | 'timestamp'>) {
  activities.value.unshift({
    ...activity,
    id: `${Date.now()}-${Math.random()}`,
    timestamp: new Date()
  })

  // Keep only last 20 activities
  if (activities.value.length > 20) {
    activities.value.pop()
  }
}

function addSalesDataPoint(value: number) {
  const now = new Date()
  const label = `${now.getHours()}:${now.getMinutes().toString().padStart(2, '0')}`
  
  salesData.value.push({ label, value })
  
  // Keep only last 20 points
  if (salesData.value.length > 20) {
    salesData.value.shift()
  }
}

function startSimulation() {
  // Simulate some initial activity
  addActivity({
    type: 'purchase',
    icon: '🎫',
    message: 'Jean Dupont purchased a ticket for Concert de Jazz',
    amount: 50000
  })
  
  addActivity({
    type: 'scan',
    icon: '✅',
    message: 'Ticket scanned: Marie Martin (valid)'
  })
}

async function simulateTicketPurchase() {
  try {
    await api.post('/broadcast/test/ticket-purchased')
  } catch (error) {
    console.error('Error:', error)
  }
}

async function simulateTicketScan() {
  try {
    await api.post('/broadcast/test/ticket-scanned')
  } catch (error) {
    console.error('Error:', error)
  }
}

async function simulateCapacityAlert() {
  try {
    await api.post('/broadcast/test/capacity-alert', { level: 'warning' })
  } catch (error) {
    console.error('Error:', error)
  }
}
</script>
