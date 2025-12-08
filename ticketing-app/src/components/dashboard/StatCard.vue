<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 stat-card">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <div v-if="loading" class="mt-2 h-8 w-24 bg-gray-200 rounded animate-pulse"></div>
        <p v-else class="mt-2 text-3xl font-bold text-gray-900 stat-value">
          {{ displayValue }}
        </p>
        <p v-if="trend" :class="[
          'mt-2 text-sm flex items-center gap-1',
          trend > 0 ? 'text-green-600' : 'text-red-600'
        ]">
          <component :is="trend > 0 ? TrendingUpIcon : TrendingDownIcon" class="w-4 h-4" />
          {{ Math.abs(trend) }}% from last month
        </p>
      </div>
      <div :class="['p-3 rounded-lg icon-container', bgColorClass]">
        <component :is="icon" :class="['w-6 h-6', iconColorClass]" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
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

// Animated number logic
const animatedValue = ref(0)
const hasAnimated = ref(false)

const numericValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value
  }
  // Extract number from formatted string (e.g., "30 261 FCFA" -> 30261)
  const numMatch = String(props.value).replace(/\s/g, '').match(/[\d,.]+/)
  return numMatch ? parseFloat(numMatch[0].replace(/,/g, '')) : 0
})

const isFormattedCurrency = computed(() => {
  return typeof props.value === 'string' && props.value.includes('FCFA')
})

const displayValue = computed(() => {
  return props.value
})

const animateNumber = (from: number, to: number, duration: number = 1500) => {
  const startTime = performance.now()
  const diff = to - from
  
  const easeOutExpo = (t: number): number => {
    return t === 1 ? 1 : 1 - Math.pow(2, -10 * t)
  }
  
  const animate = (currentTime: number) => {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)
    const easedProgress = easeOutExpo(progress)
    
    animatedValue.value = from + diff * easedProgress
    
    if (progress < 1) {
      requestAnimationFrame(animate)
    }
  }
  
  requestAnimationFrame(animate)
}

// Watch numericValue directly - animate when it changes from 0 to a real value
watch(numericValue, (newVal, oldVal) => {
  if (newVal > 0 && !hasAnimated.value) {
    // First time getting a real value - animate from 0
    animateNumber(0, newVal)
    hasAnimated.value = true
  } else if (hasAnimated.value && oldVal !== undefined && newVal !== oldVal) {
    // Value changed after initial animation - animate the difference
    animateNumber(animatedValue.value, newVal, 800)
  }
}, { immediate: true })

// Also watch loading to reset animation state if needed
watch(() => props.loading, (newLoading, oldLoading) => {
  if (!oldLoading && newLoading) {
    // Going back to loading state - reset
    hasAnimated.value = false
    animatedValue.value = 0
  }
})

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

<style scoped>
.stat-card {
  animation: fadeSlideIn 0.5s ease-out forwards;
  opacity: 0;
  transform: translateY(20px);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}

.stat-card:nth-child(1) { animation-delay: 0ms; }
.stat-card:nth-child(2) { animation-delay: 100ms; }
.stat-card:nth-child(3) { animation-delay: 200ms; }
.stat-card:nth-child(4) { animation-delay: 300ms; }

.icon-container {
  transition: transform 0.3s ease;
}

.stat-card:hover .icon-container {
  transform: scale(1.1);
}

.stat-value {
  font-variant-numeric: tabular-nums;
}

@keyframes fadeSlideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
