<template>
  <span
    :class="[
      'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
      statusClasses
    ]"
  >
    <span
      v-if="showDot"
      :class="[
        'w-2 h-2 rounded-full mr-2',
        dotClasses
      ]"
    />
    {{ label || status }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    status: string
    type?: 'event' | 'ticket' | 'order' | 'custom'
    label?: string
    showDot?: boolean
  }>(),
  {
    type: 'custom',
    showDot: true
  }
)

const statusClasses = computed(() => {
  // Event statuses
  if (props.type === 'event') {
    const eventStatusMap: Record<string, string> = {
      draft: 'bg-gray-100 text-gray-800',
      published: 'bg-blue-100 text-blue-800',
      ongoing: 'bg-green-100 text-green-800',
      completed: 'bg-purple-100 text-purple-800',
      cancelled: 'bg-red-100 text-red-800'
    }
    return eventStatusMap[props.status] || 'bg-gray-100 text-gray-800'
  }

  // Ticket statuses
  if (props.type === 'ticket') {
    const ticketStatusMap: Record<string, string> = {
      issued: 'bg-gray-100 text-gray-800',
      reserved: 'bg-yellow-100 text-yellow-800',
      paid: 'bg-green-100 text-green-800',
      in: 'bg-blue-100 text-blue-800',
      out: 'bg-purple-100 text-purple-800',
      invalid: 'bg-red-100 text-red-800',
      refunded: 'bg-orange-100 text-orange-800'
    }
    return ticketStatusMap[props.status] || 'bg-gray-100 text-gray-800'
  }

  // Order statuses
  if (props.type === 'order') {
    const orderStatusMap: Record<string, string> = {
      pending: 'bg-yellow-100 text-yellow-800',
      completed: 'bg-green-100 text-green-800',
      failed: 'bg-red-100 text-red-800',
      cancelled: 'bg-gray-100 text-gray-800',
      refunded: 'bg-orange-100 text-orange-800'
    }
    return orderStatusMap[props.status] || 'bg-gray-100 text-gray-800'
  }

  // Default/custom
  return 'bg-gray-100 text-gray-800'
})

const dotClasses = computed(() => {
  if (props.type === 'event') {
    const eventDotMap: Record<string, string> = {
      draft: 'bg-gray-500',
      published: 'bg-blue-500',
      ongoing: 'bg-green-500 animate-pulse',
      completed: 'bg-purple-500',
      cancelled: 'bg-red-500'
    }
    return eventDotMap[props.status] || 'bg-gray-500'
  }

  if (props.type === 'ticket') {
    const ticketDotMap: Record<string, string> = {
      issued: 'bg-gray-500',
      reserved: 'bg-yellow-500',
      paid: 'bg-green-500',
      in: 'bg-blue-500 animate-pulse',
      out: 'bg-purple-500',
      invalid: 'bg-red-500',
      refunded: 'bg-orange-500'
    }
    return ticketDotMap[props.status] || 'bg-gray-500'
  }

  if (props.type === 'order') {
    const orderDotMap: Record<string, string> = {
      pending: 'bg-yellow-500 animate-pulse',
      completed: 'bg-green-500',
      failed: 'bg-red-500',
      cancelled: 'bg-gray-500',
      refunded: 'bg-orange-500'
    }
    return orderDotMap[props.status] || 'bg-gray-500'
  }

  return 'bg-gray-500'
})
</script>
