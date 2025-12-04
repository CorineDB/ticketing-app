<template>
  <DashboardLayout>
    <div class="container mx-auto px-4 py-8">
      <div v-if="loading" class="text-center text-gray-500">Loading ticket details...</div>
      <div v-else-if="error" class="text-center text-red-500">Error: {{ error }}</div>
      <div v-else-if="ticket">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Ticket #{{ ticket.id }}</h1>
          <div class="flex space-x-2">
            <Can permission="manage_tickets">
              <button
                @click="resendTicket"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Resend Ticket
              </button>
              <button
                @click="regenerateQR"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
              >
                Regenerate QR Code
              </button>
              <button
                @click="invalidateTicket"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
              >
                Invalidate Ticket
              </button>
            </Can>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
          <h2 class="text-xl font-semibold mb-4">Ticket Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-gray-500">Event:</p>
              <p class="font-medium">{{ getEventName(ticket.event_id) }}</p>
            </div>
            <div>
              <p class="text-gray-500">Ticket Type:</p>
              <p class="font-medium">{{ ticket.ticket_type?.name || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Owner:</p>
              <p class="font-medium">{{ ticket.buyer_name || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Status:</p>
              <p class="font-medium">
                <TicketStatusBadge :status="ticket.status" />
              </p>
            </div>
            <!-- Add more ticket details here -->
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 text-center">
          <h2 class="text-xl font-semibold mb-4">Ticket QR Code</h2>
          <TicketQRCode :ticket="ticket" @download="downloadTicket" />
          <p class="mt-4 text-gray-600">Scan this QR code for entry.</p>
        </div>
      </div>
      <div v-else class="text-center text-gray-500">Ticket not found.</div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Can from '@/components/permissions/Can.vue'
import TicketQRCode from '@/components/tickets/TicketQRCode.vue' // This component needs to be created
import TicketStatusBadge from '@/components/tickets/TicketStatusBadge.vue' // This component needs to be created
import { useTickets } from '@/composables/useTickets'
import { useEvents } from '@/composables/useEvents' // Import useEvents
import ticketService from '@/services/ticketService'

const route = useRoute()
const { ticket, loading, error, fetchTicketById, resendTicketEmail, updateTicketStatus, regenerateQRCode } = useTickets()
const { events, fetchEvents } = useEvents() // Destructure events and fetchEvents

onMounted(() => {
  const ticketId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id
  if (ticketId) {
    fetchTicketById(ticketId)
    fetchEvents({}) // Fetch all events to populate 'events' ref
  }
})

const getEventName = computed(() => (eventId: string) => {
  return events.value.find(event => event.id === eventId)?.title || 'N/A'
})

const resendTicket = async () => {
  if (ticket.value?.id && confirm('Are you sure you want to resend this ticket email?')) {
    await resendTicketEmail(ticket.value.id)
    // Handle success/error notification
  }
}

const invalidateTicket = async () => {
  if (ticket.value?.id && confirm('Are you sure you want to invalidate this ticket?')) {
    await updateTicketStatus(ticket.value.id, 'invalidated') // Assuming 'invalidated' is a valid status
    // Handle success/error notification and refresh ticket data
    if (!error.value) {
      fetchTicketById(ticket.value.id) // Refresh data
    }
  }
}

const regenerateQR = async () => {
  if (ticket.value?.id && confirm('Are you sure you want to regenerate the QR code for this ticket?')) {
    try {
      await regenerateQRCode(ticket.value.id)
      if (!error.value) {
        alert('QR code regenerated successfully!')
      } else {
        alert(`Error: ${error.value}`)
      }
    } catch (e: any) {
      alert(`Failed to regenerate QR code: ${e.message || 'Unknown error'}`)
    }
  }
}

async function downloadTicket() {
  if (!ticket.value?.id || !ticket.value?.magic_link_token) return
  
  try {
    // Download QR code image
    const blob = await ticketService.downloadQR(ticket.value.id, ticket.value.magic_link_token)
    
    // Create download link
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `ticket-${ticket.value.code || ticket.value.id}-qr.png`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Failed to download ticket:', error)
    alert('Failed to download ticket. Please try again.')
  }
}
</script>

<style scoped>
/* Scoped styles for this component */
</style>
