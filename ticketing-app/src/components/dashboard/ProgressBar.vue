<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="mb-2">
      <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
        <span>{{ label }}</span>
        <span class="font-semibold">{{ current }} / {{ max }}</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
        <div
          :class="[
            'h-full rounded-full transition-all duration-500 ease-out',
            getBarColor(percentage)
          ]"
          :style="{ width: `${percentage}%` }"
        ></div>
      </div>
      <div class="flex items-center justify-between mt-1">
        <span class="text-xs text-gray-500">{{ percentage.toFixed(1) }}%</span>
        <span v-if="remaining > 0" class="text-xs text-gray-500">
          {{ remaining }} remaining
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  label: string
  current: number
  max: number
}

const props = defineProps<Props>()

const percentage = computed(() => {
  if (props.max === 0) return 0
  return Math.min((props.current / props.max) * 100, 100)
})

const remaining = computed(() => {
  return Math.max(props.max - props.current, 0)
})

function getBarColor(percent: number): string {
  if (percent >= 90) return 'bg-red-500'
  if (percent >= 75) return 'bg-orange-500'
  if (percent >= 50) return 'bg-yellow-500'
  return 'bg-green-500'
}
</script>
