<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Events</h1>
          <p class="mt-2 text-gray-600">Manage all your events</p>
        </div>
        <button
          @click="showEventModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
        >
          <PlusIcon class="w-5 h-5" />
          Create Event
        </button>
      </div>

      <!-- Filters -->
      <FilterBar
        v-model="filters"
        :filter-labels="{
          status: 'Status',
          city: 'City',
          start_date: 'Start Date'
        }"
        @change="fetchEvents"
      >
        <template #filters="{ filters: f, updateFilter }">
          <!-- Status Filter -->
          <select
            :value="f.status"
            @change="updateFilter('status', ($event.target as HTMLSelectElement).value)"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="ongoing">Ongoing</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>

          <!-- City Filter -->
          <input
            :value="f.city"
            @input="updateFilter('city', ($event.target as HTMLInputElement).value)"
            type="text"
            placeholder="Filter by city"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />

          <!-- Date Filter -->
          <input
            :value="f.start_date"
            @input="updateFilter('start_date', ($event.target as HTMLInputElement).value)"
            type="date"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </template>
      </FilterBar>

      <!-- Events Grid/Table Toggle -->
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
          {{ events.length }} event(s) found
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
      <div v-else-if="viewMode === 'grid' && events.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <EventCard
          v-for="event in events"
          :key="event.id"
          :event="event"
          @publish="handlePublish"
          @delete="handleDelete"
        />
      </div>

      <!-- Table View -->
      <div v-else-if="viewMode === 'table' && events.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Event</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Date</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Location</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Capacity</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Tickets Sold</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Revenue</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Status</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="event in events"
                :key="event.id"
                class="border-b border-gray-100 hover:bg-gray-50"
              >
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex-shrink-0">
                      <img
                        v-if="event.image_url"
                        :src="getImageUrl(event.image_url)"
                        :alt="event.title"
                        class="w-full h-full object-cover rounded-lg"
                      />
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">{{ event.title }}</div>
                      <div class="text-sm text-gray-500">{{ event.organisateur?.name }}</div>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4 text-sm text-gray-600">
                  {{ formatDate(event.start_datetime) }}
                </td>
                <td class="py-3 px-4 text-sm text-gray-600">
                  {{ event.venue }}, {{ event.city }}
                </td>
                <td class="py-3 px-4 text-sm text-gray-600">
                  {{ event.capacity }}
                </td>
                <td class="py-3 px-4 text-sm font-medium text-gray-900">
                  {{ event.tickets_sold || 0 }}
                </td>
                <td class="py-3 px-4 text-sm font-medium text-gray-900">
                  {{ formatCurrency(event.revenue || 0) }}
                </td>
                <td class="py-3 px-4">
                  <StatusBadge :status="event.status" type="event" />
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <RouterLink
                      :to="`/dashboard/events/${event.id}`"
                      class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                      title="View details"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </RouterLink>
                    <button
                      @click="editEvent(event)"
                      class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                      title="Edit"
                    >
                      <EditIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="confirmDelete(event)"
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
        <CalendarIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No events found</h3>
        <p class="text-gray-600 mb-6">Get started by creating your first event</p>
        <button
          @click="showEventModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center gap-2"
        >
          <PlusIcon class="w-5 h-5" />
          Create Event
        </button>
      </div>
    </div>

    <!-- Event Form Modal -->
    <EventFormModal
      v-model="showEventModal"
      :event="selectedEvent"
      @submit="handleEventSubmit"
    />

    <!-- Delete Confirmation -->
    <ConfirmModal
      v-model="showDeleteModal"
      title="Delete Event"
      message="Are you sure you want to delete this event? This action cannot be undone."
      variant="danger"
      confirm-text="Delete"
      @confirm="handleDeleteConfirm"
    />
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useEvents } from '@/composables/useEvents'
import { useNotificationStore } from '@/stores/notifications'
import eventService from '@/services/eventService'
import { formatDate, formatCurrency, getImageUrl } from '@/utils/formatters'
import type { Event, EventFilters as EventFiltersType } from '@/types/api'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import EventCard from '@/components/events/EventCard.vue'
import EventFormModal from '@/components/events/EventFormModal.vue'
import FilterBar from '@/components/common/FilterBar.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import {
  PlusIcon,
  LayoutGridIcon,
  ListIcon,
  CalendarIcon,
  EyeIcon,
  EditIcon,
  TrashIcon
} from 'lucide-vue-next'

const { events, loading, fetchEvents, createEvent, updateEvent, deleteEvent } = useEvents()
const notifications = useNotificationStore()

const viewMode = ref<'grid' | 'table'>('grid')
const filters = ref<EventFiltersType>({})

const showEventModal = ref(false)
const showDeleteModal = ref(false)
const selectedEvent = ref<Event | null>(null)
const eventToDelete = ref<Event | null>(null)

onMounted(() => {
  loadEvents()
})

async function loadEvents() {
  await fetchEvents(filters.value)
}

function editEvent(event: Event) {
  selectedEvent.value = event
  showEventModal.value = true
}

function confirmDelete(event: Event) {
  eventToDelete.value = event
  showDeleteModal.value = true
}

async function handleEventSubmit(data: any) {
  let result
  if (selectedEvent.value) {
    result = await updateEvent(selectedEvent.value.id, data)
  } else {
    result = await createEvent(data)
  }

  // Only close modal if operation succeeded
  if (result) {
    showEventModal.value = false
    selectedEvent.value = null
    await loadEvents()
  }
}

async function handleDeleteConfirm() {
  if (eventToDelete.value) {
    await deleteEvent(eventToDelete.value.id)
    eventToDelete.value = null
    loadEvents()
  }
}

async function handlePublish(eventId: string) {
  try {
    await eventService.publish(eventId)
    notifications.success('Succès', 'Événement publié avec succès !')
    await loadEvents()
  } catch (error: any) {
    const message = error.response?.data?.message || 'Impossible de publier l\'événement'
    notifications.error('Erreur', message)
    console.error('Error publishing event:', error)
  }
}

function handleDelete(eventId: string) {
  const event = events.value.find(e => e.id === eventId)
  if (event) {
    confirmDelete(event)
  }
}

</script>
