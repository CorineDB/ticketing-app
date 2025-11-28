<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Tickets</h1>
          <p class="mt-2 text-gray-600">Manage all tickets across events</p>
        </div>
        <div class="flex gap-2">
          <button
            v-if="canCreateTickets"
            @click="showTicketModal = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
          >
            <PlusIcon class="w-5 h-5" />
            Create Ticket
          </button>
          <button
            @click="exportTickets"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 flex items-center gap-2"
          >
            <DownloadIcon class="w-5 h-5" />
            Export
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- Search -->
          <div class="lg:col-span-2">
            <div class="relative">
              <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search by ticket ID, buyer email, or name..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                @input="debouncedSearch"
              />
            </div>
          </div>

          <!-- Event Filter -->
          <select
            v-model="filters.event_id"
            @change="fetchTickets"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Events</option>
            <option v-for="event in events" :key="event.id" :value="event.id">
              {{ event.title }}
            </option>
          </select>

          <!-- Status Filter -->
          <select
            v-model="filters.status"
            @change="fetchTickets"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="valid">Valid</option>
            <option value="used">Used</option>
            <option value="expired">Expired</option>
            <option value="cancelled">Cancelled</option>
          </select>

          <!-- Payment Status Filter -->
          <select
            v-model="filters.paid"
            @change="fetchTickets"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option :value="undefined">All Payments</option>
            <option :value="true">Paid</option>
            <option :value="false">Unpaid</option>
          </select>
        </div>
      </div>

      <!-- Results Count -->
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
          {{ tickets.length }} ticket(s) found
        </div>
        <div class="flex gap-2">
          <button
            @click="viewMode = 'grid'"
            :class="[
              'p-2 rounded-lg',
              viewMode === 'grid' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'
            ]"
          >
            <LayoutGridIcon class="w-5 h-5" />
          </button>
          <button
            @click="viewMode = 'table'"
            :class="[
              'p-2 rounded-lg',
              viewMode === 'table' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'
            ]"
          >
            <ListIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="animate-pulse">
          <div class="h-64 bg-gray-200 rounded-xl"></div>
        </div>
      </div>

      <!-- Grid View -->
      <div v-else-if="viewMode === 'grid' && tickets.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <TicketCard
          v-for="ticket in tickets"
          :key="ticket.id"
          :ticket="ticket"
          @view="viewTicket"
          @edit="editTicket"
          @mark-paid="markTicketPaid"
          @delete="confirmDelete"
        />
      </div>

      <!-- Table View -->
      <div v-else-if="viewMode === 'table' && tickets.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Ticket ID</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Event</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Buyer</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Type</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Price</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Payment</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Status</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="ticket in tickets"
                :key="ticket.id"
                class="border-b border-gray-100 hover:bg-gray-50"
              >
                <td class="py-3 px-4 text-sm font-mono text-gray-900">
                  #{{ ticket.id }}
                </td>
                <td class="py-3 px-4 text-sm text-gray-900">
                  {{ getEventName(ticket.event_id) }}
                </td>
                <td class="py-3 px-4">
                  <div class="text-sm text-gray-900">{{ ticket.buyer_name }}</div>
                  <div class="text-xs text-gray-500">{{ ticket.buyer_email }}</div>
                </td>
                <td class="py-3 px-4 text-sm text-gray-600">
                  {{ ticket.ticket_type?.name }}
                </td>
                <td class="py-3 px-4 text-sm font-medium text-gray-900">
                  {{ formatCurrency(ticket.price) }}
                </td>
                <td class="py-3 px-4">
                  <StatusBadge :status="getPaymentStatus(ticket)" type="ticket" />
                </td>
                <td class="py-3 px-4">
                  <StatusBadge :status="ticket.status" type="ticket" />
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <button
                      @click="viewTicket(ticket)"
                      class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                      title="View ticket"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="canUpdateTickets"
                      @click="editTicket(ticket)"
                      class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                      title="Edit"
                    >
                      <EditIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="canMarkTicketsPaid && getPaymentStatus(ticket) === 'pending'"
                      @click="markTicketPaid(ticket)"
                      class="p-2 text-green-600 hover:bg-green-50 rounded-lg"
                      title="Mark as paid"
                    >
                      <CheckCircleIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="canGenerateQRCodes"
                      @click="generateQR(ticket)"
                      class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg"
                      title="Generate QR Code"
                    >
                      <QrCodeIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="canDeleteTickets"
                      @click="confirmDelete(ticket)"
                      class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                      title="Delete"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <TicketIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No tickets found</h3>
        <p class="text-gray-600 mb-6">No tickets match your current filters</p>
        <button
          @click="clearFilters"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
        >
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Modals -->
    <TicketFormModal
      v-model:show="showTicketModal"
      :ticket="selectedTicket"
      @submit="handleTicketSubmit"
    />

    <TicketQRCodeModal
      v-model:show="showQRModal"
      :ticket="selectedTicket"
    />

    <ConfirmModal
      v-model="showDeleteModal"
      title="Delete Ticket"
      message="Are you sure you want to delete this ticket? This action cannot be undone."
      variant="danger"
      confirm-text="Delete"
      @confirm="handleDelete"
    />
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useTickets } from '@/composables/useTickets'
import { useEvents } from '@/composables/useEvents'
import { usePermissions } from '@/composables/usePermissions'
import { formatCurrency } from '@/utils/formatters'
import type { Ticket, TicketFilters } from '@/types/api'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import TicketCard from '@/components/tickets/TicketCard.vue'
import TicketFormModal from '@/components/tickets/TicketFormModal.vue'
import TicketQRCodeModal from '@/components/tickets/TicketQRCodeModal.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import {
  PlusIcon,
  DownloadIcon,
  SearchIcon,
  LayoutGridIcon,
  ListIcon,
  TicketIcon,
  EyeIcon,
  EditIcon,
  CheckCircleIcon,
  QrCodeIcon,
  TrashIcon
} from 'lucide-vue-next'

const { tickets, loading, fetchTickets, updateTicket, deleteTicket, exportTickets: exportTicketsData } = useTickets()
const { events, fetchEvents } = useEvents()
const {
  canCreateTickets,
  canUpdateTickets,
  canDeleteTickets,
  canMarkTicketsPaid,
  canGenerateQRCodes
} = usePermissions()

const viewMode = ref<'grid' | 'table'>('table')
const filters = ref<TicketFilters>({
  search: '',
  event_id: '',
  status: '',
  paid: undefined // Use undefined for "All" state
})

const showTicketModal = ref(false)
const showQRModal = ref(false)
const showDeleteModal = ref(false)
const selectedTicket = ref<Ticket | null>(null)
const ticketToDelete = ref<Ticket | null>(null)

let searchTimeout: any = null

onMounted(() => {
  loadData()
})

async function loadData() {
  await Promise.all([
    fetchTickets(filters.value),
    fetchEvents({})
  ])
  console.log("TicketsListView: loading:", loading.value);
  console.log("TicketsListView: tickets.length:", tickets.value.length);
  console.log("TicketsListView: tickets:", tickets.value);
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchTickets(filters.value)
  }, 500)
}

function clearFilters() {
  filters.value = {
    search: '',
    event_id: '',
    status: '',
    paid: undefined
  }
  fetchTickets(filters.value)
}

const getEventName = (eventId: string) => {
  return events.value.find(event => event.id === eventId)?.title || 'N/A'
}

const getPaymentStatus = (ticket: Ticket): 'paid' | 'pending' | 'unpaid' => {
  if (ticket.status === 'paid' || ticket.paid_at) return 'paid';
  if (ticket.status === 'reserved') return 'pending';
  return 'unpaid'; // Or 'issued'
}

function viewTicket(ticket: Ticket) {
  selectedTicket.value = ticket
}

function editTicket(ticket: Ticket) {
  selectedTicket.value = ticket
  showTicketModal.value = true
}

async function markTicketPaid(ticket: Ticket) {
  if (confirm('Mark this ticket as paid?')) {
    await updateTicket(ticket.id, { status: 'paid' })
    await fetchTickets(filters.value)
  }
}

function generateQR(ticket: Ticket) {
  selectedTicket.value = ticket
  showQRModal.value = true
}

function confirmDelete(ticket: Ticket) {
  ticketToDelete.value = ticket
  showDeleteModal.value = true
}

async function handleTicketSubmit(data: any) {
  if (selectedTicket.value) {
    await updateTicket(selectedTicket.value.id, data)
  }
  selectedTicket.value = null
  await fetchTickets(filters.value)
}

async function handleDelete() {
  if (ticketToDelete.value) {
    await deleteTicket(ticketToDelete.value.id)
    ticketToDelete.value = null
    await fetchTickets(filters.value)
  }
}

async function exportTickets() {
  await exportTicketsData(filters.value)
}
</script>
