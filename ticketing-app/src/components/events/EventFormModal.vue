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
            Event Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.name"
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
            Venue <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.venue"
            type="text"
            required
            placeholder="Convention Center"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Address
          </label>
          <input
            v-model="formData.address"
            type="text"
            placeholder="123 Main Street"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            City
          </label>
          <input
            v-model="formData.city"
            type="text"
            placeholder="New York"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Country
          </label>
          <input
            v-model="formData.country"
            type="text"
            placeholder="USA"
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
const totalSteps = 3
const stepTitles = ['General Info', 'Location & Capacity', 'Banner']
const loading = ref(false)
const fileInput = ref<HTMLInputElement>()
const bannerPreview = ref('')

const formData = ref<Partial<CreateEventData>>({
  name: '',
  description: '',
  venue: '',
  address: '',
  city: '',
  country: '',
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  capacity: 100,
  dress_code: '',
  allow_reentry: false
})

const isStepValid = computed(() => {
  if (currentStep.value === 1) {
    return formData.value.name && formData.value.start_date && formData.value.start_time
  }
  if (currentStep.value === 2) {
    return formData.value.venue && formData.value.capacity && formData.value.capacity > 0
  }
  return true
})

watch(() => props.event, (event) => {
  if (event) {
    formData.value = {
      name: event.name,
      description: event.description,
      venue: event.venue,
      address: event.address,
      city: event.city,
      country: event.country,
      start_date: event.start_date,
      end_date: event.end_date,
      start_time: event.start_time,
      end_time: event.end_time,
      capacity: event.capacity,
      dress_code: event.dress_code,
      allow_reentry: event.allow_reentry
    }
    if (event.banner) {
      bannerPreview.value = event.banner
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
    formData.value.banner = file
  }
}

function removeBanner() {
  bannerPreview.value = ''
  formData.value.banner = undefined
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function handleSubmit() {
  emit('submit', formData.value as CreateEventData)
  handleClose()
}

function handleClose() {
  isOpen.value = false
  currentStep.value = 1
  if (!isEdit.value) {
    resetForm()
  }
}

function resetForm() {
  formData.value = {
    name: '',
    description: '',
    venue: '',
    address: '',
    city: '',
    country: '',
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    capacity: 100,
    dress_code: '',
    allow_reentry: false
  }
  bannerPreview.value = ''
}
</script>
