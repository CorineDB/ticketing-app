<template>
  <DashboardLayout>
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">
        {{ isEditing ? 'Edit Event' : 'Create New Event' }}
      </h1>

      <form @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-lg p-6 space-y-6">
        <!-- Event Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Event Name</label>
          <input
            id="name"
            v-model="formData.name"
            type="text"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Event Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2"
            >Description</label
          >
          <textarea
            id="description"
            v-model="formData.description"
            rows="4"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
        </div>

        <!-- Event Date (Placeholder) -->
        <div>
          <label for="date" class="block text-sm font-medium text-gray-700 mb-2"
            >Event Date</label
          >
          <input
            id="date"
            v-model="formData.date"
            type="date"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Event Location -->
        <div>
          <label for="location" class="block text-sm font-medium text-gray-700 mb-2"
            >Location</label
          >
          <input
            id="location"
            v-model="formData.location"
            type="text"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
          <div class="flex gap-3">
            <AlertCircleIcon class="w-5 h-5 text-red-600 flex-shrink-0" />
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>
        </div>

        <div class="flex space-x-4">
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <LoaderIcon v-if="loading" class="w-5 h-5 animate-spin" />
            <span>{{ isEditing ? 'Update Event' : 'Create Event' }}</span>
          </button>
          <RouterLink
            :to="{ name: 'events' }"
            class="flex-1 py-3 px-4 text-center border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </RouterLink>
        </div>
      </form>

      <!-- Ticket Type Management (only for editing existing events) -->
      <div v-if="isEditing && event" class="mt-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Ticket Types</h2>
        <button
          @click="openTicketTypeModal"
          class="mb-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
        >
          Add Ticket Type
        </button>
        <!-- Display existing ticket types here -->
      </div>
      <EventFormModal
        :show="showTicketTypeModal"
        :eventId="event?.id"
        @close="showTicketTypeModal = false"
      />
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import EventFormModal from '@/components/events/EventFormModal.vue' // This component needs to be created
import { useEvents } from '@/composables/useEvents'
import { AlertCircleIcon, LoaderIcon } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const { event, loading, error, fetchEventById, createEvent, updateEvent } = useEvents()

const isEditing = ref(false)
const formData = ref({
  name: '',
  description: '',
  date: '', // Placeholder, will need proper date handling
  location: ''
  // Add other event fields as needed
})
const showTicketTypeModal = ref(false)

onMounted(() => {
  if (route.params.id) {
    isEditing.value = true
    const eventId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id
    fetchEventById(eventId)
  }
})

watch(event, (newEvent) => {
  if (newEvent && isEditing.value) {
    formData.value = {
      name: newEvent.name,
      description: newEvent.description,
      date: newEvent.date, // Assuming date is in a format compatible with input type="date"
      location: newEvent.location
    }
  }
})

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  try {
    if (isEditing.value && event.value?.id) {
      await updateEvent(event.value.id, formData.value)
    } else {
      await createEvent(formData.value)
    }
    if (!error.value) {
      router.push({ name: 'events' }) // Redirect to events list after successful save
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'An error occurred.'
  } finally {
    loading.value = false
  }
}

const openTicketTypeModal = () => {
  showTicketTypeModal.value = true
}
</script>

<style scoped>
/* Scoped styles for this component */
</style>
