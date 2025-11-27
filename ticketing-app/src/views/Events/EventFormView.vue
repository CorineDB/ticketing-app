<template>
  <DashboardLayout>
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">
        {{ isEditing ? 'Edit Event' : 'Create New Event' }}
      </h1>

      <form @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-lg p-6 space-y-6">
        <!-- Event Title -->
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
            Event Title <span class="text-red-500">*</span>
          </label>
          <input
            id="title"
            v-model="formData.title"
            type="text"
            required
            placeholder="Enter event name"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Event Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            Description
          </label>
          <textarea
            id="description"
            v-model="formData.description"
            rows="4"
            placeholder="Describe your event..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
        </div>

        <!-- Event Image -->
        <div>
          <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">
            Event Banner/Image
          </label>
          <input
            id="image_url"
            type="file"
            accept="image/*"
            @change="handleImageChange"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
          <p class="mt-1 text-sm text-gray-500">Upload an image for your event (JPG, PNG)</p>
        </div>

        <!-- Date & Time -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Start Date & Time -->
          <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
              Start Date <span class="text-red-500">*</span>
            </label>
            <input
              id="start_date"
              v-model="formData.start_date"
              type="date"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
              Start Time <span class="text-red-500">*</span>
            </label>
            <input
              id="start_time"
              v-model="formData.start_time"
              type="time"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- End Date & Time -->
          <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
              End Date <span class="text-red-500">*</span>
            </label>
            <input
              id="end_date"
              v-model="formData.end_date"
              type="date"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
              End Time <span class="text-red-500">*</span>
            </label>
            <input
              id="end_time"
              v-model="formData.end_time"
              type="time"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Location & Capacity -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
            <input
              id="location"
              v-model="formData.location"
              type="text"
              placeholder="Event venue or address"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
              Capacity <span class="text-red-500">*</span>
            </label>
            <input
              id="capacity"
              v-model.number="formData.capacity"
              type="number"
              min="1"
              required
              placeholder="Maximum attendees"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Dress Code -->
        <div>
          <label for="dress_code" class="block text-sm font-medium text-gray-700 mb-2">Dress Code (Optional)</label>
          <input
            id="dress_code"
            v-model="formData.dress_code"
            type="text"
            placeholder="e.g., Casual, Formal, Business"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Allow Re-entry -->
        <div class="flex items-center">
          <input
            id="allow_reentry"
            v-model="formData.allow_reentry"
            type="checkbox"
            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
          />
          <label for="allow_reentry" class="ml-2 block text-sm text-gray-900">Allow Re-entry</label>
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
      <div v-if="isEditing && currentEvent" class="mt-8">
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
        :eventId="currentEvent?.id"
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
const { currentEvent, loading, error, fetchEvent, createEvent, updateEvent } = useEvents()

const isEditing = ref(false)
const formData = ref({
  title: '',
  description: '',
  location: '',
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  capacity: 1,
  allow_reentry: false,
  dress_code: '',
  timezone: ''
})
const imageFile = ref<File | null>(null)
const showTicketTypeModal = ref(false)

onMounted(() => {
  if (route.params.id) {
    isEditing.value = true
    const eventId = (Array.isArray(route.params.id) ? route.params.id[0] : route.params.id)
    fetchEvent(eventId)
  }
})

watch(currentEvent, (newEvent) => {
  if (newEvent && isEditing.value) {
    const startDateObj = newEvent.start_date ? new Date(newEvent.start_date) : null;
    const endDateObj = newEvent.end_date ? new Date(newEvent.end_date) : null;

    formData.value = {
      title: newEvent.name || '',
      description: newEvent.description || '',
      location: newEvent.venue || '',
      start_date: startDateObj ? startDateObj.toISOString().split('T')[0] : '',
      end_date: endDateObj ? endDateObj.toISOString().split('T')[0] : '',
      start_time: newEvent.start_time || '',
      end_time: newEvent.end_time || '',
      capacity: newEvent.capacity || 1,
      allow_reentry: newEvent.allow_reentry || false,
      dress_code: newEvent.dress_code || '',
      timezone: ''
    }
  }
})

function handleImageChange(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    imageFile.value = target.files[0]
  }
}

const handleSubmit = async () => {
  try {
    // Construct the payload to match backend CreateEventData
    const payload: any = {
      title: formData.value.title,
      description: formData.value.description,
      location: formData.value.location,
      start_datetime: `${formData.value.start_date} ${formData.value.start_time}:00`,
      end_datetime: `${formData.value.end_date} ${formData.value.end_time}:00`,
      capacity: formData.value.capacity,
      allow_reentry: formData.value.allow_reentry,
      dress_code: formData.value.dress_code,
      timezone: formData.value.timezone
    }

    // Add image file if selected
    if (imageFile.value) {
      payload.image_url = imageFile.value
    }

    if (isEditing.value && currentEvent.value?.id) {
      await updateEvent(currentEvent.value.id, payload)
    } else {
      await createEvent(payload)
    }
    if (!error.value) {
      router.push({ name: 'events' })
    }
  } catch (err: any) {
    console.error('Error submitting event:', err)
  }
}

const openTicketTypeModal = () => {
  showTicketTypeModal.value = true
}
</script>

<style scoped>
/* Scoped styles for this component */
</style>
