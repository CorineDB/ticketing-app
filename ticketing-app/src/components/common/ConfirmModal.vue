<template>
  <Modal
    v-model="isOpen"
    :title="title"
    size="sm"
    :confirm-text="confirmText"
    :cancel-text="cancelText"
    :confirm-disabled="loading"
    @confirm="handleConfirm"
    @close="handleClose"
  >
    <div class="space-y-4">
      <!-- Icon -->
      <div
        v-if="variant"
        :class="[
          'w-12 h-12 rounded-full flex items-center justify-center mx-auto',
          iconBgClass
        ]"
      >
        <component :is="variantIcon" :class="['w-6 h-6', iconColorClass]" />
      </div>

      <!-- Message -->
      <div :class="['text-center', variant ? 'text-gray-600' : 'text-gray-900']">
        {{ message }}
      </div>

      <!-- Additional content slot -->
      <div v-if="$slots.default">
        <slot />
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Modal from './Modal.vue'
import {
  AlertTriangleIcon,
  CheckCircleIcon,
  InfoIcon,
  XCircleIcon,
  HelpCircleIcon
} from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    title: string
    message: string
    variant?: 'danger' | 'warning' | 'success' | 'info' | 'question'
    confirmText?: string
    cancelText?: string
    loading?: boolean
  }>(),
  {
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    loading: false
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  confirm: []
  close: []
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const variantIcon = computed(() => {
  const icons = {
    danger: XCircleIcon,
    warning: AlertTriangleIcon,
    success: CheckCircleIcon,
    info: InfoIcon,
    question: HelpCircleIcon
  }
  return props.variant ? icons[props.variant] : null
})

const iconBgClass = computed(() => {
  const classes = {
    danger: 'bg-red-100',
    warning: 'bg-yellow-100',
    success: 'bg-green-100',
    info: 'bg-blue-100',
    question: 'bg-purple-100'
  }
  return props.variant ? classes[props.variant] : ''
})

const iconColorClass = computed(() => {
  const classes = {
    danger: 'text-red-600',
    warning: 'text-yellow-600',
    success: 'text-green-600',
    info: 'text-blue-600',
    question: 'text-purple-600'
  }
  return props.variant ? classes[props.variant] : ''
})

function handleConfirm() {
  emit('confirm')
}

function handleClose() {
  emit('close')
  isOpen.value = false
}
</script>
