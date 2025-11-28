<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-gray-900">My Tickets</h1>
      <p class="mt-2 text-gray-600">
        View and manage your event tickets
      </p>
    </div>

    <!-- Browse Events CTA -->
    <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl p-8 text-white">
      <div class="flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
          <h2 class="text-2xl font-bold mb-2">Discover Amazing Events</h2>
          <p class="text-blue-100">
            Browse upcoming events and book your tickets
          </p>
        </div>
        <RouterLink
          :to="{ name: 'events' }"
          class="px-6 py-3 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 flex items-center gap-2"
        >
          <SearchIcon class="w-5 h-5" />
          Browse Events
        </RouterLink>
      </div>
    </div>

    <!-- My Tickets -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">My Tickets</h2>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="i in 3" :key="i" class="animate-pulse">
          <div class="h-48 bg-gray-200 rounded-lg"></div>
        </div>
      </div>

      <div v-else-if="myTickets.length > 0">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <TicketCard
            v-for="ticket in myTickets"
            :key="ticket.id"
            :ticket="ticket"
          />
        </div>
      </div>

      <div v-else class="text-center py-12">
        <TicketIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
        <h3 class="text-lg font-medium text-gray-900 mb-1">No tickets yet</h3>
        <p class="text-gray-600 mb-4">Start by browsing available events</p>
        <RouterLink
          :to="{ name: 'events' }"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <SearchIcon class="w-5 h-5" />
          Browse Events
        </RouterLink>
      </div>
    </div>

    <!-- Upcoming Events I'm Attending -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Upcoming Events</h2>

      <div v-if="loading" class="space-y-3">
        <div v-for="i in 3" :key="i" class="animate-pulse">
          <div class="h-20 bg-gray-200 rounded"></div>
        </div>
      </div>

      <div v-else-if="upcomingEvents.length > 0" class="space-y-3">
        <RouterLink
          v-for="event in upcomingEvents"
          :key="event.id"
          :to="`/events/${event.slug}`"
          class="flex items-center gap-4 p-4 rounded-lg border border-gray-200 hover:bg-gray-50"
        >
          <div v-if="event.img_url" class="w-20 h-20 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
            <img :src="event.img_url" :alt="event.title" class="w-full h-full object-cover" />
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">{{ event.title }}</h3>
            <div class="flex items-center gap-4 mt-1 text-sm text-gray-600">
              <div class="flex items-center gap-1">
                <CalendarIcon class="w-4 h-4" />
                {{ formatDate(event.start_datetime) }}
              </div>
              <div class="flex items-center gap-1">
                <MapPinIcon class="w-4 h-4" />
                {{ event.venue }}
              </div>
            </div>
          </div>
          <Badge :variant="getEventStatus(event)">
            {{ event.tickets_count }} {{ event.tickets_count === 1 ? 'ticket' : 'tickets' }}
          </Badge>
        </RouterLink>
      </div>

      <div v-else class="text-center py-8 text-gray-500">
        No upcoming events
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import dashboardService from '@/services/dashboardService'
import { formatDate } from '@/utils/formatters'
import TicketCard from '@/components/tickets/TicketCard.vue'
import Badge from '@/components/common/Badge.vue'
import {
  SearchIcon,
  TicketIcon,
  CalendarIcon,
  MapPinIcon
} from 'lucide-vue-next'

const myTickets = ref<any[]>([])
const upcomingEvents = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const data = await dashboardService.getParticipantDashboard()
    myTickets.value = data.my_tickets || []
    upcomingEvents.value = data.upcoming_events || []
  } catch (error) {
    console.error('Failed to fetch dashboard:', error)
  } finally {
    loading.value = false
  }
})

function getEventStatus(event: any) {
  const now = new Date()
  const startDate = new Date(event.start_datetime)

  if (startDate > now) {
    return 'primary'
  } else {
    return 'success'
  }
}
</script>
