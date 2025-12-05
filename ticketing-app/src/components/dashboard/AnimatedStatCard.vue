<template>
  <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-3">
          <div :class="[
            'p-3 rounded-lg',
            colorClasses.bg
          ]">
            <component :is="icon" :class="['w-6 h-6', colorClasses.icon]" />
          </div>
          <div class="flex-1">
            <div class="text-sm font-medium text-gray-600">{{ label }}</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">
              <span v-if="prefix">{{ prefix }}</span>
              <CountUp 
                :end-val="value" 
                :duration="duration"
                :options="countUpOptions"
              />
              <span v-if="suffix" class="text-lg ml-1">{{ suffix }}</span>
            </div>
          </div>
        </div>
        
        <div v-if="trend !== undefined" class="flex items-center gap-2">
          <div :class="[
            'flex items-center gap-1 text-sm font-medium',
            trend > 0 ? 'text-green-600' : trend < 0 ? 'text-red-600' : 'text-gray-600'
          ]">
            <TrendingUpIcon v-if="trend > 0" class="w-4 h-4" />
            <TrendingDownIcon v-else-if="trend < 0" class="w-4 h-4" />
            <MinusIcon v-else class="w-4 h-4" />
            <span>{{ Math.abs(trend) }}%</span>
          </div>
          <span class="text-xs text-gray-500">vs last period</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import CountUp from 'vue-countup-v3'
import { TrendingUpIcon, TrendingDownIcon, MinusIcon } from 'lucide-vue-next'
import type { Component } from 'vue'

interface Props {
  label: string
  value: number
  icon: Component
  color?: 'blue' | 'green' | 'purple' | 'orange' | 'red'
  trend?: number
  prefix?: string
  suffix?: string
  format?: 'number' | 'currency' | 'percentage'
  duration?: number
}

const props = withDefaults(defineProps<Props>(), {
  color: 'blue',
  format: 'number',
  duration: 1.5
})

const colorClasses = computed(() => {
  const colors = {
    blue: { bg: 'bg-blue-50', icon: 'text-blue-600' },
    green: { bg: 'bg-green-50', icon: 'text-green-600' },
    purple: { bg: 'bg-purple-50', icon: 'text-purple-600' },
    orange: { bg: 'bg-orange-50', icon: 'text-orange-600' },
    red: { bg: 'bg-red-50', icon: 'text-red-600' }
  }
  return colors[props.color]
})

const countUpOptions = computed(() => {
  const baseOptions = {
    separator: ' ',
    decimal: ',',
    duration: props.duration
  }

  if (props.format === 'currency') {
    return { ...baseOptions, decimalPlaces: 0 }
  } else if (props.format === 'percentage') {
    return { ...baseOptions, decimalPlaces: 1 }
  }
  
  return baseOptions
})
</script>
