<template>
  <span
    :class="[
      'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
      statusClasses
    ]"
  >
    <span
      :class="[
        'w-2 h-2 rounded-full mr-2',
        dotClasses
      ]"
    />
    {{ statusText }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { GateStatus } from '@/types/api'

const props = defineProps<{
  status: GateStatus
}>()

const statusClasses = computed(() => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    pause: 'bg-yellow-100 text-yellow-800',
    inactive: 'bg-gray-100 text-gray-800'
  }
  return classes[props.status]
})

const dotClasses = computed(() => {
  const classes = {
    active: 'bg-green-500',
    pause: 'bg-yellow-500',
    inactive: 'bg-gray-500'
  }
  return classes[props.status]
})

const statusText = computed(() => {
  const text = {
    active: 'Active',
    pause: 'Paused',
    inactive: 'Inactive'
  }
  return text[props.status]
})
</script>
