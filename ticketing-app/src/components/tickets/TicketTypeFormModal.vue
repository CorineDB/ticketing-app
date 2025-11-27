<template>
  <Modal :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" size="lg">
    <template #header>
      <h2 class="text-xl font-semibold text-gray-900">
        {{ ticketType ? 'Edit Ticket Type' : 'Create Ticket Type' }}
      </h2>
    </template>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
          Name <span class="text-red-500">*</span>
        </label>
        <input
          id="name"
          v-model="formData.name"
          type="text"
          required
          placeholder="e.g., VIP, General Admission, Early Bird"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      <!-- Description -->
      <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
          Description
        </label>
        <textarea
          id="description"
          v-model="formData.description"
          rows="3"
          placeholder="Describe what this ticket type includes"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        ></textarea>
      </div>

      <!-- Price and Quantity Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Price -->
        <div>
          <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
            Price (¬) <span class="text-red-500">*</span>
          </label>
          <input
            id="price"
            v-model.number="formData.price"
            type="number"
            min="0"
            step="0.01"
            required
            placeholder="0.00"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Quantity -->
        <div>
          <label for="quantity_total" class="block text-sm font-medium text-gray-700 mb-2">
            Total Quantity <span class="text-red-500">*</span>
          </label>
          <input
            id="quantity_total"
            v-model.number="formData.quantity_total"
            type="number"
            min="1"
            required
            placeholder="100"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      </div>

      <!-- Max Per Order -->
      <div>
        <label for="max_per_order" class="block text-sm font-medium text-gray-700 mb-2">
          Maximum Per Order
        </label>
        <input
          id="max_per_order"
          v-model.number="formData.max_per_order"
          type="number"
          min="1"
          placeholder="Leave empty for no limit"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <p class="mt-1 text-sm text-gray-500">
          Leave empty to allow unlimited tickets per order
        </p>
      </div>

      <!-- Sale Period -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">Sale Period</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Sale Start Date -->
          <div>
            <label for="sale_start_date" class="block text-xs text-gray-600 mb-1">
              Start Date
            </label>
            <input
              id="sale_start_date"
              v-model="formData.sale_start_date"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Sale End Date -->
          <div>
            <label for="sale_end_date" class="block text-xs text-gray-600 mb-1">
              End Date
            </label>
            <input
              id="sale_end_date"
              v-model="formData.sale_end_date"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>
        <p class="mt-1 text-sm text-gray-500">
          Leave empty to allow sales indefinitely
        </p>
      </div>

      <!-- Status -->
      <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
          Status <span class="text-red-500">*</span>
        </label>
        <select
          id="status"
          v-model="formData.status"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="active">Active</option>
          <option value="paused">Paused</option>
          <option value="sold_out">Sold Out</option>
        </select>
        <p class="mt-1 text-sm text-gray-500">
          <span v-if="formData.status === 'active'" class="text-green-600">
            This ticket type is available for sale
          </span>
          <span v-else-if="formData.status === 'paused'" class="text-orange-600">
            Sales are temporarily paused
          </span>
          <span v-else-if="formData.status === 'sold_out'" class="text-red-600">
            This ticket type is sold out
          </span>
        </p>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex gap-3">
          <AlertCircleIcon class="w-5 h-5 text-red-600 flex-shrink-0" />
          <p class="text-sm text-red-700">{{ error }}</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3 pt-4">
        <button
          type="button"
          @click="$emit('update:modelValue', false)"
          class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="loading"
          class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <LoaderIcon v-if="loading" class="w-5 h-5 animate-spin" />
          <span>{{ loading ? 'Saving...' : (ticketType ? 'Update' : 'Create') }}</span>
        </button>
      </div>
    </form>
  </Modal>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { TicketType } from '@/types/api'
import Modal from '@/components/common/Modal.vue'
import { AlertCircleIcon, LoaderIcon } from 'lucide-vue-next'

interface Props {
  modelValue: boolean
  eventId?: number
  ticketType?: TicketType | null
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'submit', data: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const formData = ref({
  name: '',
  description: '',
  price: 0,
  quantity_total: 1,
  max_per_order: null as number | null,
  sale_start_date: '',
  sale_end_date: '',
  status: 'active' as 'active' | 'paused' | 'sold_out'
})

const loading = ref(false)
const error = ref('')

// Watch for ticket type changes to populate form
watch(() => props.ticketType, (newTicketType) => {
  if (newTicketType) {
    formData.value = {
      name: newTicketType.name,
      description: newTicketType.description || '',
      price: newTicketType.price,
      quantity_total: newTicketType.quantity_total,
      max_per_order: newTicketType.max_per_order || null,
      sale_start_date: newTicketType.sale_start_date || '',
      sale_end_date: newTicketType.sale_end_date || '',
      status: newTicketType.status
    }
  } else {
    resetForm()
  }
}, { immediate: true })

// Watch modal close to reset form
watch(() => props.modelValue, (isOpen) => {
  if (!isOpen && !props.ticketType) {
    resetForm()
  }
  error.value = ''
})

function resetForm() {
  formData.value = {
    name: '',
    description: '',
    price: 0,
    quantity_total: 1,
    max_per_order: null,
    sale_start_date: '',
    sale_end_date: '',
    status: 'active'
  }
}

async function handleSubmit() {
  loading.value = true
  error.value = ''

  try {
    // Validate sale period
    if (formData.value.sale_start_date && formData.value.sale_end_date) {
      if (new Date(formData.value.sale_end_date) < new Date(formData.value.sale_start_date)) {
        error.value = 'Sale end date must be after start date'
        loading.value = false
        return
      }
    }

    // Prepare data for submission
    const data: any = {
      name: formData.value.name,
      description: formData.value.description || null,
      price: formData.value.price,
      quantity_total: formData.value.quantity_total,
      max_per_order: formData.value.max_per_order || null,
      sale_start_date: formData.value.sale_start_date || null,
      sale_end_date: formData.value.sale_end_date || null,
      status: formData.value.status
    }

    // Add event_id for new ticket types
    if (!props.ticketType && props.eventId) {
      data.event_id = props.eventId
    }

    emit('submit', data)
    emit('update:modelValue', false)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to save ticket type. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>
