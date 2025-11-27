<template>
  <DashboardLayout>
    <div v-if="loading" class="space-y-6">
      <div class="animate-pulse">
        <div class="h-48 bg-gray-200 rounded-xl mb-6"></div>
        <div class="h-96 bg-gray-200 rounded-xl"></div>
      </div>
    </div>

    <div v-else-if="event" class="space-y-6">
      <!-- Event Header -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Banner -->
        <div class="h-48 bg-gradient-to-br from-blue-500 to-purple-500 relative">
          <img
            v-if="event.banner"
            :src="event.banner"
            :alt="event.name"
            class="w-full h-full object-cover"
          />
          <div class="absolute top-4 right-4 flex gap-2">
            <button
              v-if="canUpdateEvents"
              @click="editEvent"
              class="px-4 py-2 bg-white text-gray-700 rounded-lg hover:bg-gray-50 flex items-center gap-2 shadow-lg"
            >
              <EditIcon class="w-4 h-4" />
              Edit
            </button>
            <button
              v-if="canDeleteEvents"
              @click="confirmDelete"
              class="px-4 py-2 bg-white text-red-600 rounded-lg hover:bg-red-50 flex items-center gap-2 shadow-lg"
            >
              <TrashIcon class="w-4 h-4" />
              Delete
            </button>
          </div>
        </div>

        <!-- Event Info -->
        <div class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h1 class="text-3xl font-bold text-gray-900">{{ event.name }}</h1>
                <StatusBadge :status="event.status" type="event" />
              </div>
              <p v-if="event.description" class="text-gray-600 mb-4">{{ event.description }}</p>
              <div class="flex items-center gap-2 text-sm text-gray-500">
                <BuildingIcon class="w-4 h-4" />
                <span>{{ event.organization?.name }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-blue-50 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                  <CalendarIcon class="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <div class="text-sm text-gray-600">Start Date</div>
                  <div class="text-lg font-semibold text-gray-900">
                    {{ formatDate(event.start_date) }}
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-green-50 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 rounded-lg">
                  <TicketIcon class="w-5 h-5 text-green-600" />
                </div>
                <div>
                  <div class="text-sm text-gray-600">Tickets Sold</div>
                  <div class="text-lg font-semibold text-gray-900">
                    {{ event.tickets_sold || 0 }} / {{ event.capacity }}
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-purple-50 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 rounded-lg">
                  <DollarSignIcon class="w-5 h-5 text-purple-600" />
                </div>
                <div>
                  <div class="text-sm text-gray-600">Revenue</div>
                  <div class="text-lg font-semibold text-gray-900">
                    {{ formatCurrency(event.revenue || 0) }}
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-orange-50 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-orange-100 rounded-lg">
                  <UsersIcon class="w-5 h-5 text-orange-600" />
                </div>
                <div>
                  <div class="text-sm text-gray-600">Attendance</div>
                  <div class="text-lg font-semibold text-gray-900">
                    {{ event.current_attendance || 0 }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <Tabs v-model="activeTab" :tabs="tabs">
        <!-- Overview Tab -->
        <template #overview>
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Event Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Date & Time -->
              <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Date & Time</h3>
                <div class="space-y-2">
                  <div class="flex items-center gap-2 text-gray-900">
                    <CalendarIcon class="w-4 h-4 text-gray-400" />
                    <span>{{ formatDate(event.start_date) }} - {{ formatDate(event.end_date) }}</span>
                  </div>
                  <div class="flex items-center gap-2 text-gray-900">
                    <ClockIcon class="w-4 h-4 text-gray-400" />
                    <span>{{ event.start_time }} - {{ event.end_time }}</span>
                  </div>
                </div>
              </div>

              <!-- Location -->
              <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Location</h3>
                <div class="space-y-2">
                  <div class="flex items-center gap-2 text-gray-900">
                    <MapPinIcon class="w-4 h-4 text-gray-400" />
                    <span>{{ event.venue }}</span>
                  </div>
                  <div class="text-gray-600">
                    {{ event.address }}<br />
                    {{ event.city }}, {{ event.country }}
                  </div>
                </div>
              </div>

              <!-- Capacity -->
              <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Capacity</h3>
                <div class="text-gray-900">
                  {{ event.capacity }} persons
                </div>
                <div class="mt-2 bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-600 h-2 rounded-full"
                    :style="{ width: `${(event.tickets_sold || 0) / event.capacity * 100}%` }"
                  ></div>
                </div>
                <div class="text-sm text-gray-500 mt-1">
                  {{ ((event.tickets_sold || 0) / event.capacity * 100).toFixed(1) }}% filled
                </div>
              </div>

              <!-- Additional Info -->
              <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Additional Information</h3>
                <div class="space-y-2">
                  <div v-if="event.dress_code" class="flex items-center gap-2 text-gray-900">
                    <ShirtIcon class="w-4 h-4 text-gray-400" />
                    <span>Dress Code: {{ event.dress_code }}</span>
                  </div>
                  <div class="flex items-center gap-2 text-gray-900">
                    <RepeatIcon class="w-4 h-4 text-gray-400" />
                    <span>Re-entry: {{ event.allow_reentry ? 'Allowed' : 'Not Allowed' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- Ticket Types Tab -->
        <template #ticket-types>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900">Ticket Types</h2>
              <button
                v-if="canManageEventTicketTypes"
                @click="showTicketTypeModal = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
              >
                <PlusIcon class="w-5 h-5" />
                Add Ticket Type
              </button>
            </div>

            <div v-if="ticketTypes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="ticketType in ticketTypes"
                :key="ticketType.id"
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
              >
                <div class="flex items-start justify-between mb-4">
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ ticketType.name }}</h3>
                    <p v-if="ticketType.description" class="text-sm text-gray-600 mt-1">
                      {{ ticketType.description }}
                    </p>
                  </div>
                  <StatusBadge :status="ticketType.status" type="ticket-type" />
                </div>

                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Price</span>
                    <span class="text-lg font-semibold text-gray-900">
                      {{ formatCurrency(ticketType.price) }}
                    </span>
                  </div>

                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Available</span>
                    <span class="text-sm font-medium text-gray-900">
                      {{ ticketType.quantity_available }} / {{ ticketType.quantity_total }}
                    </span>
                  </div>

                  <div class="bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-green-600 h-2 rounded-full"
                      :style="{ width: `${(ticketType.quantity_available / ticketType.quantity_total * 100)}%` }"
                    ></div>
                  </div>

                  <div v-if="ticketType.sale_start_date" class="text-xs text-gray-500">
                    Sale: {{ formatDate(ticketType.sale_start_date) }} - {{ formatDate(ticketType.sale_end_date) }}
                  </div>
                </div>

                <div v-if="canManageEventTicketTypes" class="flex gap-2 mt-4 pt-4 border-t border-gray-200">
                  <button
                    @click="editTicketType(ticketType)"
                    class="flex-1 px-3 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDeleteTicketType(ticketType)"
                    class="flex-1 px-3 py-2 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
              <TicketIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-semibold text-gray-900 mb-2">No ticket types yet</h3>
              <p class="text-gray-600 mb-6">Create ticket types to start selling tickets</p>
              <button
                v-if="canManageEventTicketTypes"
                @click="showTicketTypeModal = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center gap-2"
              >
                <PlusIcon class="w-5 h-5" />
                Add Ticket Type
              </button>
            </div>
          </div>
        </template>

        <!-- Gates Tab -->
        <template #gates>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900">Gates</h2>
              <button
                v-if="canManageGates"
                @click="showGateModal = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
              >
                <PlusIcon class="w-5 h-5" />
                Add Gate
              </button>
            </div>

            <div v-if="gates.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <GateCard
                v-for="gate in gates"
                :key="gate.id"
                :gate="gate"
                @edit="editGate"
                @delete="confirmDeleteGate"
              />
            </div>

            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
              <DoorOpenIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-semibold text-gray-900 mb-2">No gates configured</h3>
              <p class="text-gray-600 mb-6">Add gates to manage entry and exit points</p>
              <button
                v-if="canManageGates"
                @click="showGateModal = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center gap-2"
              >
                <PlusIcon class="w-5 h-5" />
                Add Gate
              </button>
            </div>
          </div>
        </template>

        <!-- Statistics Tab -->
        <template #statistics>
          <div class="space-y-6">
            <EventStats :event-id="event.id" />
          </div>
        </template>
      </Tabs>
    </div>

    <!-- Error State -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
      <AlertCircleIcon class="w-16 h-16 text-red-400 mx-auto mb-4" />
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Event not found</h3>
      <p class="text-gray-600 mb-6">The event you're looking for doesn't exist</p>
      <RouterLink
        to="/events"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center gap-2"
      >
        <ArrowLeftIcon class="w-5 h-5" />
        Back to Events
      </RouterLink>
    </div>

    <!-- Modals -->
    <EventFormModal
      v-model="showEventModal"
      :event="event"
      @submit="handleEventUpdate"
    />

    <TicketTypeFormModal
      v-model="showTicketTypeModal"
      :event-id="event?.id"
      :ticket-type="selectedTicketType"
      @submit="handleTicketTypeSubmit"
    />

    <GateFormModal
      v-model="showGateModal"
      :event-id="event?.id"
      :gate="selectedGate"
      @submit="handleGateSubmit"
    />

    <ConfirmModal
      v-model="showDeleteModal"
      title="Delete Event"
      message="Are you sure you want to delete this event? This action cannot be undone."
      variant="danger"
      confirm-text="Delete"
      @confirm="handleDelete"
    />

    <ConfirmModal
      v-model="showDeleteTicketTypeModal"
      title="Delete Ticket Type"
      message="Are you sure you want to delete this ticket type? This action cannot be undone."
      variant="danger"
      confirm-text="Delete"
      @confirm="handleDeleteTicketType"
    />

    <ConfirmModal
      v-model="showDeleteGateModal"
      title="Delete Gate"
      message="Are you sure you want to delete this gate? This action cannot be undone."
      variant="danger"
      confirm-text="Delete"
      @confirm="handleDeleteGate"
    />
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useEvents } from '@/composables/useEvents'
import { useTicketTypes } from '@/composables/useTicketTypes'
import { useGates } from '@/composables/useGates'
import { usePermissions } from '@/composables/usePermissions'
import { formatDate, formatCurrency } from '@/utils/formatters'
import type { Event, TicketType, Gate } from '@/types/api'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Tabs from '@/components/common/Tabs.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import EventFormModal from '@/components/events/EventFormModal.vue'
import EventStats from '@/components/events/EventStats.vue'
import TicketTypeFormModal from '@/components/tickets/TicketTypeFormModal.vue'
import GateCard from '@/components/gates/GateCard.vue'
import GateFormModal from '@/components/gates/GateFormModal.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import {
  CalendarIcon,
  ClockIcon,
  MapPinIcon,
  TicketIcon,
  DollarSignIcon,
  UsersIcon,
  BuildingIcon,
  EditIcon,
  TrashIcon,
  PlusIcon,
  AlertCircleIcon,
  ArrowLeftIcon,
  DoorOpenIcon,
  ShirtIcon,
  RepeatIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const { fetchEvent, updateEvent, deleteEvent } = useEvents()
const { ticketTypes, fetchTicketTypes, createTicketType, updateTicketType, deleteTicketType } = useTicketTypes()
const { gates, fetchGates, createGate, updateGate, deleteGate } = useGates()
const {
  canUpdateEvents,
  canDeleteEvents,
  canManageEventTicketTypes,
  canManageGates
} = usePermissions()

const event = ref<Event | null>(null)
const loading = ref(true)
const activeTab = ref('overview')

const showEventModal = ref(false)
const showTicketTypeModal = ref(false)
const showGateModal = ref(false)
const showDeleteModal = ref(false)
const showDeleteTicketTypeModal = ref(false)
const showDeleteGateModal = ref(false)

const selectedTicketType = ref<TicketType | null>(null)
const selectedGate = ref<Gate | null>(null)
const ticketTypeToDelete = ref<TicketType | null>(null)
const gateToDelete = ref<Gate | null>(null)

const tabs = computed(() => [
  { id: 'overview', label: 'Overview', icon: CalendarIcon },
  { id: 'ticket-types', label: 'Ticket Types', icon: TicketIcon },
  { id: 'gates', label: 'Gates', icon: DoorOpenIcon },
  { id: 'statistics', label: 'Statistics', icon: UsersIcon }
])

onMounted(async () => {
  await loadEvent()
})

async function loadEvent() {
  loading.value = true
  try {
    const eventId = Number(route.params.id)
    event.value = await fetchEvent(eventId)

    if (event.value) {
      // Load related data
      await Promise.all([
        fetchTicketTypes({ event_id: eventId }),
        fetchGates({ event_id: eventId })
      ])
    }
  } catch (error) {
    console.error('Failed to load event:', error)
  } finally {
    loading.value = false
  }
}

function editEvent() {
  showEventModal.value = true
}

function confirmDelete() {
  showDeleteModal.value = true
}

async function handleEventUpdate(data: any) {
  if (event.value) {
    await updateEvent(event.value.id, data)
    await loadEvent()
  }
}

async function handleDelete() {
  if (event.value) {
    await deleteEvent(event.value.id)
    router.push('/dashboard/events')
  }
}

// Ticket Type handlers
function editTicketType(ticketType: TicketType) {
  selectedTicketType.value = ticketType
  showTicketTypeModal.value = true
}

function confirmDeleteTicketType(ticketType: TicketType) {
  ticketTypeToDelete.value = ticketType
  showDeleteTicketTypeModal.value = true
}

async function handleTicketTypeSubmit(data: any) {
  if (selectedTicketType.value) {
    await updateTicketType(selectedTicketType.value.id, data)
  } else {
    await createTicketType({ ...data, event_id: event.value?.id })
  }
  selectedTicketType.value = null
  await fetchTicketTypes({ event_id: event.value?.id })
}

async function handleDeleteTicketType() {
  if (ticketTypeToDelete.value) {
    await deleteTicketType(ticketTypeToDelete.value.id)
    ticketTypeToDelete.value = null
    await fetchTicketTypes({ event_id: event.value?.id })
  }
}

// Gate handlers
function editGate(gate: Gate) {
  selectedGate.value = gate
  showGateModal.value = true
}

function confirmDeleteGate(gate: Gate) {
  gateToDelete.value = gate
  showDeleteGateModal.value = true
}

async function handleGateSubmit(data: any) {
  if (selectedGate.value) {
    await updateGate(selectedGate.value.id, data)
  } else {
    await createGate({ ...data, event_id: event.value?.id })
  }
  selectedGate.value = null
  await fetchGates({ event_id: event.value?.id })
}

async function handleDeleteGate() {
  if (gateToDelete.value) {
    await deleteGate(gateToDelete.value.id)
    gateToDelete.value = null
    await fetchGates({ event_id: event.value?.id })
  }
}
</script>
