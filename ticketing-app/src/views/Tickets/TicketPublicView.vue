<template>
  <PublicLayout>
    <div v-if="loading" class="max-w-2xl mx-auto px-4 py-12">
      <div class="animate-pulse space-y-6">
        <div class="h-96 bg-gray-200 rounded-xl"></div>
        <div class="h-24 bg-gray-200 rounded"></div>
      </div>
    </div>

    <div v-else-if="ticket" class="max-w-2xl mx-auto px-4 py-12">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Your Ticket</h1>
        <p class="text-gray-600">Show this QR code at the event entrance</p>
      </div>

      <!-- Ticket QR Code Display -->
      <TicketQRCode :ticket="ticket" @download="downloadTicket" />

      <!-- Event Info -->
      <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Event Information</h2>

        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <CalendarIcon class="w-5 h-5 text-gray-400" />
            <div>
              <div class="text-sm text-gray-600">Date & Time</div>
              <div class="font-medium text-gray-900">
                {{ formatDateTime(ticket.event?.start_date) }}
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <MapPinIcon class="w-5 h-5 text-gray-400" />
            <div>
              <div class="text-sm text-gray-600">Venue</div>
              <div class="font-medium text-gray-900">{{ ticket.event?.venue }}</div>
              <div v-if="ticket.event?.address" class="text-sm text-gray-600">
                {{ ticket.event.address }}
              </div>
            </div>
          </div>

          <div v-if="ticket.event?.dress_code" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center gap-2 text-blue-900">
              <InfoIcon class="w-5 h-5" />
              <span class="font-medium">Dress Code: {{ ticket.event.dress_code }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Important Notes -->
      <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h3 class="font-semibold text-yellow-900 mb-2">Important Notes</h3>
        <ul class="space-y-2 text-sm text-yellow-800">
          <li class="flex items-start gap-2">
            <span class="mt-0.5">•</span>
            <span>Please arrive 30 minutes before the event starts</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="mt-0.5">•</span>
            <span>Bring a valid ID for verification</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="mt-0.5">•</span>
            <span>This QR code is unique - do not share it with others</span>
          </li>
          <li v-if="ticket.event?.allow_reentry" class="flex items-start gap-2">
            <span class="mt-0.5">•</span>
            <span>Re-entry is allowed for this event</span>
          </li>
        </ul>
      </div>

      <!-- Help Section -->
      <div class="mt-8 text-center">
        <p class="text-gray-600 mb-4">Need help?</p>
        <a
          href="mailto:support@ticketing.com"
          class="text-blue-600 hover:text-blue-700 font-medium"
        >
          Contact Support
        </a>
      </div>
    </div>

    <div v-else class="max-w-2xl mx-auto px-4 py-24 text-center">
      <XCircleIcon class="w-16 h-16 text-red-500 mx-auto mb-4" />
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Ticket Not Found</h1>
      <p class="text-gray-600 mb-6">
        The ticket you're trying to access doesn't exist or the link is invalid.
      </p>
      <RouterLink to="/" class="text-blue-600 hover:text-blue-700 font-medium">
        Browse events
      </RouterLink>
    </div>
  </PublicLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import ticketService from '@/services/ticketService'
import { formatDateTime } from '@/utils/formatters'
import type { Ticket } from '@/types/api'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import TicketQRCode from '@/components/tickets/TicketQRCode.vue'
import {
  CalendarIcon,
  MapPinIcon,
  InfoIcon,
  XCircleIcon
} from 'lucide-vue-next'

const route = useRoute()
const ticket = ref<Ticket | null>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const code = route.params.code as string
    // Fetch ticket by code (magic link token)
    ticket.value = await ticketService.getByCode(code)
  } catch (error) {
    console.error('Failed to fetch ticket:', error)
  } finally {
    loading.value = false
  }
})

function downloadTicket() {
  // TODO: Implement ticket download as PDF
  console.log('Download ticket')
}
</script>
