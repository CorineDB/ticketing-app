<template>
  <div class="capacity-gauge bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ title }}</h3>
    
    <div class="relative w-48 h-48 mx-auto">
      <!-- SVG Gauge -->
      <svg viewBox="0 0 200 200" class="transform -rotate-90">
        <!-- Background circle -->
        <circle
          cx="100"
          cy="100"
          r="85"
          fill="none"
          stroke="#e5e7eb"
          stroke-width="15"
        />
        
        <!-- Progress circle -->
        <circle
          cx="100"
          cy="100"
          r="85"
          fill="none"
          :stroke="gaugeColor"
          stroke-width="15"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="dashOffset"
          stroke-linecap="round"
          class="transition-all duration-1000 ease-out"
        />
      </svg>
      
      <!-- Center text -->
      <div class="absolute inset-0 flex flex-col items-center justify-center">
        <div class="text-4xl font-bold" :class="textColor">
          {{ percentage.toFixed(0) }}%
        </div>
        <div class="text-sm text-gray-600 mt-1">
          {{ current }} / {{ max }}
        </div>
      </div>
    </div>
    
    <!-- Status indicator -->
    <div class="mt-4 text-center">
      <span :class="[
        'inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium',
        statusClass
      ]">
        <span class="w-2 h-2 rounded-full animate-pulse" :class="dotClass"></span>
        {{ statusText }}
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  title: string
  current: number
  max: number
  zones?: {
    green: number
    orange: number
    red: number
  }
}

const props = withDefaults(defineProps<Props>(), {
  zones: () => ({ green: 70, orange: 85, red: 95 })
})

const percentage = computed(() => {
  if (props.max === 0) return 0
  return Math.min((props.current / props.max) * 100, 100)
})

const circumference = computed(() => 2 * Math.PI * 85)

const dashOffset = computed(() => {
  const progress = percentage.value / 100
  return circumference.value * (1 - progress)
})

const gaugeColor = computed(() => {
  const p = percentage.value
  if (p >= props.zones.red) return '#EF4444' // red
  if (p >= props.zones.orange) return '#F97316' // orange
  if (p >= props.zones.green) return '#EAB308' // yellow
  return '#10B981' // green
})

const textColor = computed(() => {
  const p = percentage.value
  if (p >= props.zones.red) return 'text-red-600'
  if (p >= props.zones.orange) return 'text-orange-600'
  if (p >= props.zones.green) return 'text-yellow-600'
  return 'text-green-600'
})

const statusClass = computed(() => {
  const p = percentage.value
  if (p >= props.zones.red) return 'bg-red-100 text-red-700'
  if (p >= props.zones.orange) return 'bg-orange-100 text-orange-700'
  if (p >= props.zones.green) return 'bg-yellow-100 text-yellow-700'
  return 'bg-green-100 text-green-700'
})

const dotClass = computed(() => {
  const p = percentage.value
  if (p >= props.zones.red) return 'bg-red-600'
  if (p >= props.zones.orange) return 'bg-orange-600'
  if (p >= props.zones.green) return 'bg-yellow-600'
  return 'bg-green-600'
})

const statusText = computed(() => {
  const p = percentage.value
  if (p >= props.zones.red) return 'Critical Capacity'
  if (p >= props.zones.orange) return 'High Capacity'
  if (p >= props.zones.green) return 'Moderate Capacity'
  return 'Normal Capacity'
})
</script>
