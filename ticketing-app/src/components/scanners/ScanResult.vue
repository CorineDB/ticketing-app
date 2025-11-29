<template>
  <Transition name="slide-up">
    <div
      v-if="result"
      :class="[
        'fixed bottom-0 inset-x-0 z-50 p-6',
        result.success ? 'bg-gradient-to-t from-green-50 to-transparent' : 'bg-gradient-to-t from-red-50 to-transparent'
      ]"
    >
      <div
        :class="[
          'max-w-md mx-auto rounded-xl shadow-2xl border-2 p-6',
          result.success ? 'bg-white border-green-500' : 'bg-white border-red-500'
        ]"
      >
        <!-- Icon -->
        <div class="flex justify-center mb-4">
          <div
            :class="[
              'w-20 h-20 rounded-full flex items-center justify-center',
              result.success ? 'bg-green-100' : 'bg-red-100'
            ]"
          >
            <component
              :is="result.success ? CheckCircleIcon : XCircleIcon"
              :class="[
                'w-12 h-12',
                result.success ? 'text-green-600' : 'text-red-600'
              ]"
            />
          </div>
        </div>

        <!-- Message -->
        <div class="text-center mb-6">
          <h3
            :class="[
              'text-2xl font-bold mb-2',
              result.success ? 'text-green-900' : 'text-red-900'
            ]"
          >
            {{ result.success ? getSuccessTitle() : 'Invalid Ticket' }}
          </h3>
          <p class="text-gray-600">{{ result.message }}</p>
        </div>

        <!-- Ticket Info (if successful) -->
        <div v-if="result.success && result.ticket" class="space-y-3 mb-6">
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="text-sm text-gray-600">Holder</span>
            <span class="font-semibold text-gray-900">{{ result.ticket.buyer_name }}</span>
          </div>
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="text-sm text-gray-600">Ticket Type</span>
            <span class="font-semibold text-gray-900">{{ result.ticket.ticket_type?.name }}</span>
          </div>
          <div v-if="result.event_status" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="text-sm text-gray-600">Current Inside</span>
            <span class="font-semibold text-gray-900">
              {{ result.event_status.current_in }}/{{ result.event_status.capacity }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <button
            @click="$emit('close')"
            class="flex-1 py-3 px-4 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300"
          >
            Close
          </button>
          <button
            @click="$emit('scan-next')"
            class="flex-1 py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"
          >
            Scan Next
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { ScanResponse } from '@/types/api'
import {
  CheckCircleIcon,
  XCircleIcon
} from 'lucide-vue-next'

const props = defineProps<{
  result: ScanResponse | null
}>()

defineEmits<{
  close: []
  'scan-next': []
}>()

function getSuccessTitle() {
  if (props.result?.result === 'valid') {
    return props.result.scan?.scan_type === 'entry' ? 'Entry Granted' : 'Exit Granted'
  }
  return 'Ticket Valid'
}
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from {
  transform: translateY(100%);
  opacity: 0;
}

.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>
