<template>
  <div>
    <!-- Tab Headers -->
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="selectTab(tab.key)"
          :class="[
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
            activeTab === tab.key
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          <div class="flex items-center gap-2">
            <component v-if="tab.icon" :is="tab.icon" class="w-5 h-5" />
            {{ tab.label }}
            <Badge v-if="tab.badge" size="sm" variant="primary">
              {{ tab.badge }}
            </Badge>
          </div>
        </button>
      </nav>
    </div>

    <!-- Tab Content -->
    <div class="py-6">
      <slot :name="activeTab" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { Component } from 'vue'
import Badge from './Badge.vue'

export interface Tab {
  key: string
  label: string
  icon?: Component
  badge?: string | number
}

const props = withDefaults(
  defineProps<{
    tabs: Tab[]
    modelValue?: string
  }>(),
  {
    modelValue: undefined
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'change': [value: string]
}>()

const activeTab = ref(props.modelValue || props.tabs[0]?.key || '')

watch(() => props.modelValue, (newValue) => {
  if (newValue !== undefined) {
    activeTab.value = newValue
  }
})

function selectTab(key: string) {
  activeTab.value = key
  emit('update:modelValue', key)
  emit('change', key)
}
</script>
