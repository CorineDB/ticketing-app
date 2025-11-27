<template>
  <div
    :class="[
      'max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden',
      typeClasses
    ]"
  >
    <div class="p-4">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <component :is="icon" :class="['h-6 w-6', iconColorClass]" />
        </div>
        <div class="ml-3 w-0 flex-1 pt-0.5">
          <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
          <p v-if="notification.message" class="mt-1 text-sm text-gray-500">
            {{ notification.message }}
          </p>
        </div>
        <div class="ml-4 flex-shrink-0 flex">
          <button
            @click="$emit('close')"
            class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
          >
            <XIcon class="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  CheckCircleIcon,
  XCircleIcon,
  AlertTriangleIcon,
  InfoIcon,
  XIcon
} from 'lucide-vue-next'
import type { Notification } from '@/types/api'

const props = defineProps<{
  notification: Notification
}>()

defineEmits<{
  close: []
}>()

const icon = computed(() => {
  switch (props.notification.type) {
    case 'success':
      return CheckCircleIcon
    case 'error':
      return XCircleIcon
    case 'warning':
      return AlertTriangleIcon
    default:
      return InfoIcon
  }
})

const iconColorClass = computed(() => {
  switch (props.notification.type) {
    case 'success':
      return 'text-green-400'
    case 'error':
      return 'text-red-400'
    case 'warning':
      return 'text-yellow-400'
    default:
      return 'text-blue-400'
  }
})

const typeClasses = computed(() => {
  switch (props.notification.type) {
    case 'success':
      return 'border-l-4 border-green-500'
    case 'error':
      return 'border-l-4 border-red-500'
    case 'warning':
      return 'border-l-4 border-yellow-500'
    default:
      return 'border-l-4 border-blue-500'
  }
})
</script>
