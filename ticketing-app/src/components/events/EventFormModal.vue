<template>
  <Modal
    v-model="isOpen"
    :title="isEdit ? 'Edit Event' : 'Create New Event'"
    size="xl"
    :show-footer="currentStep === totalSteps"
    :confirm-text="isEdit ? 'Update Event' : 'Create Event'"
    :confirm-disabled="!isStepValid || loading"
    @confirm="handleSubmit"
    @close="handleClose"
  >
    <!-- Step Progress -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-2">
        <div
          v-for="step in totalSteps"
          :key="step"
          class="flex-1"
        >
          <div class="flex items-center">
            <div
              :class="[
                'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
                currentStep >= step
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-200 text-gray-600'
              ]"
            >
              {{ step }}
            </div>
            <div
              v-if="step < totalSteps"
              :class="[
                'flex-1 h-1 mx-2',
                currentStep > step ? 'bg-blue-600' : 'bg-gray-200'
              ]"
            />
          </div>
          <div class="text-xs text-gray-600 mt-1">{{ stepTitles[step - 1] }}</div>
        </div>
      </div>
    </div>

    <!-- Step 1: General Information -->
    <div v-show="currentStep === 1" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Event Title <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.title"
            type="text"
            required
            placeholder="Summer Music Festival 2025"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Description
          </label>
          <textarea
            v-model="formData.description"
            rows="4"
            placeholder="Describe your event..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Start Date <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.start_date"
            type="date"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Start Time <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.start_time"
            type="time"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            End Date
          </label>
          <input
            v-model="formData.end_date"
            type="date"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            End Time
          </label>
          <input
            v-model="formData.end_time"
            type="time"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      </div>
    </div>

    <!-- Step 2: Location & Capacity -->
    <div v-show="currentStep === 2" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Location
          </label>
          <input
            v-model="formData.location"
            type="text"
            placeholder="Convention Center or Address"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Capacity <span class="text-red-500">*</span>
          </label>
          <input
            v-model.number="formData.capacity"
            type="number"
            required
            min="1"
            placeholder="1000"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Dress Code
          </label>
          <input
            v-model="formData.dress_code"
            type="text"
            placeholder="Casual, Formal, etc."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      </div>

      <div class="flex items-center gap-2">
        <input
          v-model="formData.allow_reentry"
          type="checkbox"
          id="allow_reentry"
          class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
        />
        <label for="allow_reentry" class="text-sm text-gray-700">
          Allow re-entry (participants can exit and enter multiple times)
        </label>
      </div>
    </div>

    <!-- Step 3: Banner Upload -->
    <div v-show="currentStep === 3" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Event Banner
        </label>
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            @change="handleFileUpload"
            class="hidden"
          />
          <div v-if="bannerPreview" class="relative">
            <img
              :src="bannerPreview"
              alt="Banner preview"
              class="max-h-64 mx-auto rounded-lg"
            />
            <button
              @click="removeBanner"
              class="absolute top-2 right-2 p-2 bg-red-600 text-white rounded-full hover:bg-red-700"
            >
              <XIcon class="w-4 h-4" />
            </button>
          </div>
          <div v-else @click="$refs.fileInput.click()" class="cursor-pointer">
            <UploadIcon class="w-12 h-12 text-gray-400 mx-auto mb-2" />
            <p class="text-gray-600">Click to upload banner image</p>
            <p class="text-sm text-gray-500 mt-1">Recommended: 1200x600px</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Step 4: Ticket Types -->
    <div v-show="currentStep === 4" class="space-y-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Ticket Types (Optional)</h3>
        <button
          type="button"
          @click="addTicketType"
          class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          + Add Ticket Type
        </button>
      </div>

      <div v-if="formData.ticket_types.length === 0" class="text-center py-8 text-gray-500">
        <p>No ticket types yet. Add one to get started.</p>
      </div>

      <div
        v-for="(ticket, index) in formData.ticket_types"
        :key="index"
        class="border border-gray-200 rounded-lg p-4 space-y-3"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700">Ticket Type {{ index + 1 }}</span>
          <button
            type="button"
            @click="removeTicketType(index)"
            class="text-red-600 hover:text-red-700 text-sm"
          >
            Remove
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
              Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="ticket.name"
              type="text"
              placeholder="e.g., VIP, General Admission"
              required
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
              Price
            </label>
            <input
              v-model.number="ticket.price"
              type="number"
              min="0"
              step="0.01"
              placeholder="0.00"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
              Quota
            </label>
            <input
              v-model.number="ticket.quota"
              type="number"
              min="0"
              placeholder="Available tickets"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
              Usage Limit
            </label>
            <input
              v-model.number="ticket.usage_limit"
              type="number"
              min="1"
              placeholder="Max scans per ticket"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
              Valid From
            </label>
            <input
              v-model="ticket.validity_from"
              type="date"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
              Valid To
            </label>
            <input
              v-model="ticket.validity_to"
              type="date"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-6 pt-6 border-t border-gray-200">
      <button
        v-if="currentStep > 1"
        @click="previousStep"
        class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
      >
        Previous
      </button>
      <div v-else></div>

      <button
        v-if="currentStep < totalSteps"
        @click="nextStep"
        :disabled="!isStepValid"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Next
      </button>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { Event, CreateEventData } from '@/types/api'
import Modal from '@/components/common/Modal.vue'
import { XIcon, UploadIcon } from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    event?: Event | null
  }>(),
  {
    event: null
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  submit: [data: CreateEventData]
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const isEdit = computed(() => !!props.event)
const currentStep = ref(1)
const totalSteps = 4
const stepTitles = ['General Info', 'Location & Capacity', 'Banner', 'Ticket Types']
const loading = ref(false)
const fileInput = ref<HTMLInputElement>()
const bannerPreview = ref('')

const formData = ref({
  title: '',
  description: '',
  location: '',
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  capacity: 100,
  dress_code: '',
  allow_reentry: false,
  image_file: null as File | null,
  ticket_types: [] as Array<{
    name: string
    price: number
    quota: number
    validity_from?: string
    validity_to?: string
    usage_limit?: number
  }>
})

const isStepValid = computed(() => {
  if (currentStep.value === 1) {
    return formData.value.title && formData.value.start_date && formData.value.start_time
  }
  if (currentStep.value === 2) {
    return formData.value.capacity && formData.value.capacity > 0
  }
  return true
})

watch(() => props.event, (event) => {
  if (event) {
    formData.value = {
      title: event.title,
      description: event.description,
      location: event.venue,
      start_date: event.start_datetime,
      end_date: event.end_date,
      start_time: event.start_time,
      end_time: event.end_time,
      capacity: event.capacity,
      dress_code: event.dress_code,
      allow_reentry: event.allow_reentry,
      image_file: null,
      ticket_types: event.ticket_types?.map(tt => ({
        name: tt.name,
        price: tt.price || 0,
        quota: tt.quota || 0,
        usage_limit: tt.usage_limit || 1,
        validity_from: tt.validity_from || '',
        validity_to: tt.validity_to || ''
      })) || []
    }
    if (event.img_url) {
      bannerPreview.value = event.img_url
    }
  }
}, { immediate: true })

function nextStep() {
  if (currentStep.value < totalSteps) {
    currentStep.value++
  }
}

function previousStep() {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

function handleFileUpload(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      bannerPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
    formData.value.image_file = file
  }
}

function removeBanner() {
  bannerPreview.value = ''
  formData.value.image_file = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function addTicketType() {
  formData.value.ticket_types.push({
    name: '',
    price: 0,
    quota: 0,
    usage_limit: 1,
    validity_from: '',
    validity_to: ''
  })
}

function removeTicketType(index: number) {
  formData.value.ticket_types.splice(index, 1)
}

function handleSubmit() {
  loading.value = true

  // Build start datetime
  const startDatetime = `${formData.value.start_date} ${formData.value.start_time}:00`

  // Build end datetime - null if not provided
  const endDatetime = (formData.value.end_date && formData.value.end_time)
    ? `${formData.value.end_date} ${formData.value.end_time}:00`
    : null

  // Construct payload matching backend CreateEventRequest
  const payload: any = {
    title: formData.value.title,
    description: formData.value.description,
    location: formData.value.location,
    start_datetime: startDatetime,
    end_datetime: endDatetime,
    capacity: formData.value.capacity,
    allow_reentry: formData.value.allow_reentry,
    dress_code: formData.value.dress_code
  }

  if (formData.value.image_file) {
    payload.image_url = formData.value.image_file
  }

  // Add ticket types if any
  if (formData.value.ticket_types.length > 0) {
    payload.ticket_types = formData.value.ticket_types
  }

  emit('submit', payload)
  // Don't close here - let parent close on success
}

function handleClose() {
  isOpen.value = false
  currentStep.value = 1
  loading.value = false
  if (!isEdit.value) {
    resetForm()
  }
}

function resetForm() {
  formData.value = {
    title: '',
    description: '',
    location: '',
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    capacity: 100,
    dress_code: '',
    allow_reentry: false,
    image_file: null,
    ticket_types: []
  }
  bannerPreview.value = ''
}
</script>
