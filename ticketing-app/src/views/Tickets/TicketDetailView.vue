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
              <p class="font-medium">{{ ticket.event?.name || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Ticket Type:</p>
              <p class="font-medium">{{ ticket.ticketType?.name || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Owner:</p>
              <p class="font-medium">{{ ticket.owner?.name || 'N/A' }}</p>
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
          <TicketQRCode :code="ticket.code" :size="250" />
          <p class="mt-4 text-gray-600">Scan this QR code for entry.</p>
        </div>
      </div>
      <div v-else class="text-center text-gray-500">Ticket not found.</div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Can from '@/components/permissions/Can.vue'
import TicketQRCode from '@/components/tickets/TicketQRCode.vue' // This component needs to be created
import TicketStatusBadge from '@/components/tickets/TicketStatusBadge.vue' // This component needs to be created
import { useTickets } from '@/composables/useTickets'

const route = useRoute()
const { ticket, loading, error, fetchTicketById, resendTicketEmail, updateTicketStatus } = useTickets()

onMounted(() => {
  const ticketId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id
  if (ticketId) {
    fetchTicketById(ticketId)
  }
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
</script>

<style scoped>
/* Scoped styles for this component */
</style>
