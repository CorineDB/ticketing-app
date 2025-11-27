<template>
  <div class="bg-white rounded-lg border border-gray-200 p-4">
    <div class="flex flex-col lg:flex-row gap-4">
      <!-- Search -->
      <div v-if="showSearch" class="flex-1">
        <div class="relative">
          <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <input
            v-model="localFilters.search"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            @input="debouncedEmit"
          />
        </div>
      </div>

      <!-- Custom Filters Slot -->
      <div v-if="$slots.filters" class="flex flex-wrap gap-4">
        <slot name="filters" :filters="localFilters" :update-filter="updateFilter" />
      </div>

      <!-- Actions -->
      <div class="flex gap-2">
        <button
          v-if="showReset"
          @click="resetFilters"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-2"
        >
          <XIcon class="w-4 h-4" />
          Reset
        </button>

        <button
          v-if="showApply"
          @click="applyFilters"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 flex items-center gap-2"
        >
          <FilterIcon class="w-4 h-4" />
          Apply
        </button>
      </div>
    </div>

    <!-- Active Filters -->
    <div v-if="activeFilters.length > 0" class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-200">
      <div
        v-for="filter in activeFilters"
        :key="filter.key"
        class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm"
      >
        <span>{{ filter.label }}: {{ filter.value }}</span>
        <button
          @click="removeFilter(filter.key)"
          class="hover:bg-blue-100 rounded-full p-0.5"
        >
          <XIcon class="w-3 h-3" />
        </button>
      </div>
      <button
        @click="resetFilters"
        class="text-sm text-gray-600 hover:text-gray-900 underline"
      >
        Clear all
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { SearchIcon, FilterIcon, XIcon } from 'lucide-vue-next'

interface FilterValue {
  key: string
  label: string
  value: string | number
}

const props = withDefaults(
  defineProps<{
    modelValue?: Record<string, any>
    showSearch?: boolean
    searchPlaceholder?: string
    showReset?: boolean
    showApply?: boolean
    autoApply?: boolean
    filterLabels?: Record<string, string>
  }>(),
  {
    modelValue: () => ({}),
    showSearch: true,
    searchPlaceholder: 'Search...',
    showReset: true,
    showApply: false,
    autoApply: true,
    filterLabels: () => ({})
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: Record<string, any>]
  'change': [value: Record<string, any>]
  'reset': []
}>()

const localFilters = ref<Record<string, any>>({ ...props.modelValue })
let debounceTimer: ReturnType<typeof setTimeout> | null = null

watch(() => props.modelValue, (newValue) => {
  localFilters.value = { ...newValue }
}, { deep: true })

const activeFilters = computed(() => {
  const filters: FilterValue[] = []
  Object.entries(localFilters.value).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      const label = props.filterLabels[key] || key
      filters.push({
        key,
        label,
        value: String(value)
      })
    }
  })
  return filters
})

function updateFilter(key: string, value: any) {
  localFilters.value[key] = value
  if (props.autoApply) {
    debouncedEmit()
  }
}

function removeFilter(key: string) {
  delete localFilters.value[key]
  if (props.autoApply) {
    emitChange()
  }
}

function resetFilters() {
  localFilters.value = {}
  emit('reset')
  emitChange()
}

function applyFilters() {
  emitChange()
}

function emitChange() {
  emit('update:modelValue', { ...localFilters.value })
  emit('change', { ...localFilters.value })
}

function debouncedEmit() {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  debounceTimer = setTimeout(() => {
    emitChange()
  }, 300)
}
</script>
