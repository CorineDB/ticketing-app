<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <div v-if="loading" class="mt-2 h-8 w-24 bg-gray-200 rounded animate-pulse"></div>
        <p v-else class="mt-2 text-3xl font-bold text-gray-900">
          {{ value }}
        </p>
        <p v-if="trend" :class="[
          'mt-2 text-sm flex items-center gap-1',
          trend > 0 ? 'text-green-600' : 'text-red-600'
        ]">
          <component :is="trend > 0 ? TrendingUpIcon : TrendingDownIcon" class="w-4 h-4" />
          {{ Math.abs(trend) }}% from last month
        </p>
      </div>
      <div :class="['p-3 rounded-lg', bgColorClass]">
        <component :is="icon" :class="['w-6 h-6', iconColorClass]" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Component } from 'vue'
import { TrendingUpIcon, TrendingDownIcon } from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    title: string
    value: string | number
    icon: Component
    color?: 'blue' | 'green' | 'purple' | 'red' | 'yellow' | 'emerald'
    trend?: number
    loading?: boolean
  }>(),
  {
    color: 'blue',
    loading: false
  }
)

const bgColorClass = computed(() => {
  const colors = {
    blue: 'bg-blue-100',
    green: 'bg-green-100',
    purple: 'bg-purple-100',
    red: 'bg-red-100',
    yellow: 'bg-yellow-100',
    emerald: 'bg-emerald-100'
  }
  return colors[props.color]
})

const iconColorClass = computed(() => {
  const colors = {
    blue: 'text-blue-600',
    green: 'text-green-600',
    purple: 'text-purple-600',
    red: 'text-red-600',
    yellow: 'text-yellow-600',
    emerald: 'text-emerald-600'
  }
  return colors[props.color]
})
</script>
