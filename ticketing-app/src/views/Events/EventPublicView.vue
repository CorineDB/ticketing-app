<template>
  <PublicLayout>
    <div v-if="loading" class="max-w-6xl mx-auto px-4 py-12">
      <div class="animate-pulse space-y-6">
        <div class="h-96 bg-gray-200 rounded-xl"></div>
        <div class="h-12 bg-gray-200 rounded w-3/4"></div>
        <div class="h-64 bg-gray-200 rounded-xl"></div>
      </div>
    </div>

    <div v-else-if="event" class="max-w-6xl mx-auto px-4 py-12">
      <!-- Event Banner -->
      <div class="relative h-96 rounded-xl overflow-hidden mb-8">
        <img
          v-if="event.banner"
          :src="event.banner"
          :alt="event.name"
          class="w-full h-full object-cover"
        />
        <div v-else class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
          <CalendarIcon class="w-32 h-32 text-white opacity-50" />
        </div>

        <!-- Status Badge Overlay -->
        <div class="absolute top-6 right-6">
          <StatusBadge :status="event.status" type="event" class="text-lg" />
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Event Header -->
          <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ event.name }}</h1>
            <div class="flex items-center gap-4 text-gray-600">
              <div class="flex items-center gap-2">
                <BuildingIcon class="w-5 h-5" />
                <span>{{ event.organization?.name }}</span>
              </div>
            </div>
          </div>

          <!-- Event Description -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">About This Event</h2>
            <p class="text-gray-700 whitespace-pre-wrap">{{ event.description || 'No description available.' }}</p>

            <div v-if="event.dress_code" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
              <div class="flex items-center gap-2 text-blue-900">
                <InfoIcon class="w-5 h-5" />
                <span class="font-medium">Dress Code: {{ event.dress_code }}</span>
              </div>
            </div>
          </div>

          <!-- Available Tickets -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Ticket Types</h2>

            <div v-if="event.ticket_types && event.ticket_types.length > 0" class="space-y-4">
              <div
                v-for="ticketType in event.ticket_types.filter(t => t.is_active)"
                :key="ticketType.id"
                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-blue-500 transition-colors"
              >
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900">{{ ticketType.name }}</h3>
                  <p v-if="ticketType.description" class="text-sm text-gray-600 mt-1">
                    {{ ticketType.description }}
                  </p>
                  <div class="mt-2 text-sm text-gray-500">
                    {{ ticketType.quantity_available }} available
                  </div>
                </div>
                <div class="text-right ml-4">
                  <div class="text-2xl font-bold text-gray-900">
                    {{ formatCurrency(ticketType.price) }}
                  </div>
                  <button
                    v-if="event.status === 'published' && ticketType.quantity_available > 0"
                    @click="selectTicketType(ticketType)"
                    class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                  >
                    Select
                  </button>
                  <span v-else class="text-sm text-gray-500">Unavailable</span>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              No tickets available at this time
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Event Details Card -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
            <h3 class="font-semibold text-gray-900 mb-4">Event Details</h3>

            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <CalendarIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                <div>
                  <div class="text-sm text-gray-600">Date & Time</div>
                  <div class="font-medium text-gray-900">{{ formatDate(event.start_date) }}</div>
                  <div class="text-sm text-gray-600">{{ event.start_time }}</div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <MapPinIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                <div>
                  <div class="text-sm text-gray-600">Location</div>
                  <div class="font-medium text-gray-900">{{ event.venue }}</div>
                  <div v-if="event.address" class="text-sm text-gray-600">{{ event.address }}</div>
                  <div v-if="event.city" class="text-sm text-gray-600">{{ event.city }}, {{ event.country }}</div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <UsersIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                <div>
                  <div class="text-sm text-gray-600">Capacity</div>
                  <div class="font-medium text-gray-900">{{ event.capacity }} people</div>
                  <div class="text-sm text-gray-600">{{ event.tickets_sold }} tickets sold</div>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
              <button
                v-if="event.status === 'published'"
                @click="scrollToTickets"
                class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"
              >
                Get Tickets
              </button>
              <div v-else class="text-center text-gray-500">
                Tickets not available
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="max-w-2xl mx-auto px-4 py-24 text-center">
      <XCircleIcon class="w-16 h-16 text-red-500 mx-auto mb-4" />
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Event Not Found</h1>
      <p class="text-gray-600 mb-6">The event you're looking for doesn't exist or has been removed.</p>
      <RouterLink to="/" class="text-blue-600 hover:text-blue-700 font-medium">
        Browse all events
      </RouterLink>
    </div>
  </PublicLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import eventService from '@/services/eventService'
import { formatDate, formatCurrency } from '@/utils/formatters'
import type { Event, TicketType } from '@/types/api'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import {
  CalendarIcon,
  MapPinIcon,
  BuildingIcon,
  UsersIcon,
  InfoIcon,
  XCircleIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const event = ref<Event | null>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const slug = route.params.slug as string
    event.value = await eventService.getPublicBySlug(slug)
  } catch (error) {
    console.error('Failed to fetch event:', error)
  } finally {
    loading.value = false
  }
})

function selectTicketType(ticketType: TicketType) {
  if (event.value) {
    router.push({ name: 'checkout', params: { eventId: event.value.id, ticketTypeId: ticketType.id } })
  }
}

function scrollToTickets() {
  // Scroll to tickets section
  const ticketsSection = document.querySelector('.ticket-types')
  ticketsSection?.scrollIntoView({ behavior: 'smooth' })
}
</script>
