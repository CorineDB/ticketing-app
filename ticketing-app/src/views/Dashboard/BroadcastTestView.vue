<template>
  <DashboardLayout>
    <div class="max-w-6xl mx-auto p-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">🎯 Broadcasting Test Dashboard</h1>
        <p class="text-gray-600">Test real-time broadcasting events</p>
      </div>

      <!-- Connection Status -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Connection Status</h2>
        <div class="flex items-center gap-3">
          <div :class="[
            'w-3 h-3 rounded-full',
            isConnected ? 'bg-green-500 animate-pulse' : 'bg-red-500'
          ]"></div>
          <span class="font-medium">{{ isConnected ? 'Connected' : 'Disconnected' }}</span>
        </div>
      </div>

      <!-- Trigger Broadcasts -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Trigger Broadcasts</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <button
            @click="triggerTicketPurchased"
            :disabled="loading"
            class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <TicketIcon class="w-5 h-5" />
            Ticket Purchased
          </button>
          <button
            @click="triggerTicketScanned"
            :disabled="loading"
            class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <ScanIcon class="w-5 h-5" />
            Ticket Scanned
          </button>
          <button
            @click="triggerCapacityAlert"
            :disabled="loading"
            class="px-4 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <AlertTriangleIcon class="w-5 h-5" />
            Capacity Alert
          </button>
          <button
            @click="triggerAll"
            :disabled="loading"
            class="px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <ZapIcon class="w-5 h-5" />
            Trigger All
          </button>
        </div>
      </div>

      <!-- Events Log -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Events Log</h2>
          <button
            @click="clearEvents"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            Clear
          </button>
        </div>

        <div v-if="events.length === 0" class="text-center py-12 text-gray-500">
          <RadioIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
          <p>No events received yet. Trigger a broadcast to see events here.</p>
        </div>

        <div v-else class="space-y-3 max-h-96 overflow-y-auto">
          <TransitionGroup name="slide-fade">
            <div
              v-for="event in events"
              :key="event.id"
              :class="[
                'p-4 rounded-lg border-l-4 transition-all',
                event.type === 'ticket.purchased' ? 'bg-blue-50 border-blue-500' :
                event.type === 'ticket.scanned' ? 'bg-green-50 border-green-500' :
                event.type === 'event.capacity.alert' ? 'bg-orange-50 border-orange-500' :
                'bg-gray-50 border-gray-500'
              ]"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-gray-900">{{ event.type }}</span>
                    <span class="text-xs text-gray-500">{{ event.channel }}</span>
                  </div>
                  <pre class="text-sm text-gray-700 overflow-x-auto">{{ JSON.stringify(event.data, null, 2) }}</pre>
                </div>
                <span class="text-xs text-gray-500 ml-4">{{ formatTime(event.timestamp) }}</span>
              </div>
            </div>
          </TransitionGroup>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import { echo } from '@/services/websocket'
import api from '@/services/api'
import {
  TicketIcon,
  ScanIcon,
  AlertTriangleIcon,
  ZapIcon,
  RadioIcon
} from 'lucide-vue-next'

interface BroadcastEvent {
  id: string
  type: string
  channel: string
  data: any
  timestamp: Date
}

const isConnected = ref(false)
const loading = ref(false)
const events = ref<BroadcastEvent[]>([])

onMounted(() => {
  setupListeners()
  
  // Check connection status
  if (echo.connector && echo.connector.pusher) {
    echo.connector.pusher.connection.bind('connected', () => {
      isConnected.value = true
      console.log('✅ WebSocket connected')
    })
    
    echo.connector.pusher.connection.bind('disconnected', () => {
      isConnected.value = false
      console.log('❌ WebSocket disconnected')
    })
    
    // Check initial state
    isConnected.value = echo.connector.pusher.connection.state === 'connected'
  }
})

onUnmounted(() => {
  // Leave all channels
  echo.leave('events')
  echo.leave('scans')
  echo.leave('alerts')
})

function setupListeners() {
  // Listen to public events channel
  echo.channel('events')
    .listen('ticket.purchased', (e: any) => {
      console.log('📨 TicketPurchased received:', e)
      addEvent('ticket.purchased', 'events', e)
    })

  // Listen to scans channel
  echo.channel('scans')
    .listen('ticket.scanned', (e: any) => {
      console.log('📨 TicketScanned received:', e)
      addEvent('ticket.scanned', 'scans', e)
    })

  // Listen to alerts channel
  echo.channel('alerts')
    .listen('event.capacity.alert', (e: any) => {
      console.log('📨 EventCapacityAlert received:', e)
      addEvent('event.capacity.alert', 'alerts', e)
    })
}

function addEvent(type: string, channel: string, data: any) {
  events.value.unshift({
    id: `${Date.now()}-${Math.random()}`,
    type,
    channel,
    data,
    timestamp: new Date()
  })

  // Keep only last 50 events
  if (events.value.length > 50) {
    events.value.pop()
  }
}

async function triggerTicketPurchased() {
  loading.value = true
  try {
    const response = await api.post('/broadcast/test/ticket-purchased')
    console.log('✅ Trigger response:', response.data)
  } catch (error) {
    console.error('❌ Trigger error:', error)
  } finally {
    loading.value = false
  }
}

async function triggerTicketScanned() {
  loading.value = true
  try {
    const response = await api.post('/broadcast/test/ticket-scanned')
    console.log('✅ Trigger response:', response.data)
  } catch (error) {
    console.error('❌ Trigger error:', error)
  } finally {
    loading.value = false
  }
}

async function triggerCapacityAlert() {
  loading.value = true
  try {
    const response = await api.post('/broadcast/test/capacity-alert', {
      level: 'warning'
    })
    console.log('✅ Trigger response:', response.data)
  } catch (error) {
    console.error('❌ Trigger error:', error)
  } finally {
    loading.value = false
  }
}

async function triggerAll() {
  loading.value = true
  try {
    const response = await api.post('/broadcast/test/all')
    console.log('✅ Trigger all response:', response.data)
  } catch (error) {
    console.error('❌ Trigger error:', error)
  } finally {
    loading.value = false
  }
}

function clearEvents() {
  events.value = []
}

function formatTime(date: Date): string {
  return date.toLocaleTimeString('fr-FR')
}
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from {
  transform: translateY(-20px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(20px);
  opacity: 0;
}
</style>
