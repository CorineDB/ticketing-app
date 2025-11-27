<template>
  <RouterLink
    :to="to"
    :class="[
      'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
      isActive
        ? 'bg-blue-50 text-blue-700'
        : 'text-gray-700 hover:bg-gray-100'
    ]"
  >
    <component :is="icon" class="w-5 h-5" />
    <span>{{ label }}</span>
    <span v-if="badge" :class="[
      'ml-auto px-2 py-0.5 rounded-full text-xs font-semibold',
      isActive ? 'bg-blue-200 text-blue-800' : 'bg-gray-200 text-gray-700'
    ]">
      {{ badge }}
    </span>
  </RouterLink>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import type { Component } from 'vue'

const props = defineProps<{
  to: string
  icon: Component
  label: string
  badge?: string | number
}>()

const route = useRoute()

const isActive = computed(() => {
  if (props.to === '/dashboard') {
    return route.path === '/dashboard'
  }
  return route.path.startsWith(props.to)
})
</script>
