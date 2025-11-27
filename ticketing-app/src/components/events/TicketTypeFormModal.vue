<template>
  <Modal :show="show" @close="$emit('close')">
    <div class="p-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">
        {{ isEditing ? 'Edit Ticket Type' : 'Create New Ticket Type' }}
      </h2>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Ticket Type Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-1"
            >Ticket Type Name</label
          >
          <input
            id="name"
            v-model="formData.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Price -->
        <div>
          <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
          <input
            id="price"
            v-model.number="formData.price"
            type="number"
            step="0.01"
            min="0"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Quantity -->
        <div>
          <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1"
            >Available Quantity</label
          >
          <input
            id="quantity"
            v-model.number="formData.quantity"
            type="number"
            min="1"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
          <div class="flex gap-2">
            <AlertCircleIcon class="w-5 h-5 text-red-600 flex-shrink-0" />
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <LoaderIcon v-if="loading" class="w-4 h-4 animate-spin" />
            <span>{{ isEditing ? 'Update Ticket Type' : 'Create Ticket Type' }}</span>
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import Modal from '@/components/common/Modal.vue' // Assuming this Modal component exists
import { LoaderIcon, AlertCircleIcon } from 'lucide-vue-next'
import ticketTypeService from '@/services/ticketTypeService' // Assuming this service exists

const props = defineProps<{
  show: boolean
  eventId?: string // Required when creating a new ticket type
  ticketType?: any // Optional, for editing existing ticket type
}>()

const emit = defineEmits(['close', 'saved'])

const isEditing = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)

const formData = ref({
  name: '',
  price: 0,
  quantity: 1,
  // Add other fields relevant to ticket types
})

watch(() => props.show, (newVal) => {
  if (newVal) {
    if (props.ticketType) {
      isEditing.value = true
      formData.value = { ...props.ticketType } // Populate form for editing
    } else {
      isEditing.value = false
      formData.value = { name: '', price: 0, quantity: 1 } // Reset form for creation
    }
    error.value = null // Clear previous errors
  }
})

const handleSubmit = async () => {
  loading.value = true
  error.value = null

  try {
    if (isEditing.value && props.ticketType?.id) {
      await ticketTypeService.updateTicketType(props.ticketType.id, formData.value)
    } else if (props.eventId) {
      await ticketTypeService.createTicketType(props.eventId, formData.value)
    } else {
      throw new Error('Event ID is required to create a new ticket type.')
    }
    emit('saved') // Notify parent component that an item was saved
    emit('close') // Close the modal
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to save ticket type.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Scoped styles for this component */
</style>
