<template>
  <Modal
    v-model="isOpen"
    :title="isEdit ? 'Edit Gate' : 'Create New Gate'"
    size="md"
    :confirm-disabled="!isFormValid"
    @confirm="handleSubmit"
    @close="handleClose"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Gate Name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
          Gate Name <span class="text-red-500">*</span>
        </label>
        <input
          id="name"
          v-model="formData.name"
          type="text"
          required
          placeholder="e.g., Main Entrance, VIP Exit"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      <!-- Gate Type -->
      <div>
        <label for="gate_type" class="block text-sm font-medium text-gray-700 mb-1">
          Gate Type <span class="text-red-500">*</span>
        </label>
        <select
          id="gate_type"
          v-model="formData.gate_type"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Select type</option>
          <option value="entrance">Entrance</option>
          <option value="exit">Exit</option>
          <option value="vip">VIP</option>
          <option value="other">Other</option>
        </select>
      </div>

      <!-- Location -->
      <div>
        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
          Location
        </label>
        <input
          id="location"
          v-model="formData.location"
          type="text"
          placeholder="e.g., Building A, Floor 1"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <p class="mt-1 text-sm text-gray-500">Optional physical location of the gate</p>
      </div>

      <!-- Status -->
      <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
          Status <span class="text-red-500">*</span>
        </label>
        <select
          id="status"
          v-model="formData.status"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="active">Active</option>
          <option value="pause">Paused</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>

      <!-- Scanner Assignment -->
      <div v-if="scanners && scanners.length > 0">
        <label for="scanner_id" class="block text-sm font-medium text-gray-700 mb-1">
          Assign Scanner
        </label>
        <select
          id="scanner_id"
          v-model="formData.scanner_id"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option :value="null">No scanner assigned</option>
          <option v-for="scanner in scanners" :key="scanner.id" :value="scanner.id">
            {{ scanner.name }} ({{ scanner.email }})
          </option>
        </select>
        <p class="mt-1 text-sm text-gray-500">Assign a scanner user to this gate</p>
      </div>
    </form>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { Gate, CreateGateData, UpdateGateData, User, GateType, GateStatus } from '@/types/api'
import Modal from '@/components/common/Modal.vue'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    gate?: Gate | null
    eventId: number
    scanners?: User[]
  }>(),
  {
    gate: null
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  submit: [data: CreateGateData | UpdateGateData]
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const isEdit = computed(() => !!props.gate)

const formData = ref<{
  name: string
  gate_type: GateType | ''
  location: string
  status: GateStatus
  scanner_id: number | null
}>({
  name: '',
  gate_type: '',
  location: '',
  status: 'active',
  scanner_id: null
})

const isFormValid = computed(() => {
  return formData.value.name.trim() !== '' && formData.value.gate_type !== ''
})

// Watch for gate changes to populate form
watch(() => props.gate, (gate) => {
  if (gate) {
    formData.value = {
      name: gate.name,
      gate_type: gate.gate_type,
      location: gate.location || '',
      status: gate.status,
      scanner_id: gate.scanner_id || null
    }
  } else {
    resetForm()
  }
}, { immediate: true })

function resetForm() {
  formData.value = {
    name: '',
    gate_type: '',
    location: '',
    status: 'active',
    scanner_id: null
  }
}

function handleSubmit() {
  if (!isFormValid.value) return

  const data: CreateGateData | UpdateGateData = {
    event_id: props.eventId,
    name: formData.value.name,
    gate_type: formData.value.gate_type as GateType,
    location: formData.value.location || undefined,
    status: formData.value.status,
    scanner_id: formData.value.scanner_id || undefined
  }

  emit('submit', data)
  handleClose()
}

function handleClose() {
  isOpen.value = false
  if (!isEdit.value) {
    resetForm()
  }
}
</script>
