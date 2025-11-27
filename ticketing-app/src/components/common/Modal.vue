<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="close"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" />

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
          <div
            :class="[
              'relative bg-white rounded-lg shadow-xl transform transition-all',
              sizeClasses
            ]"
            @click.stop
          >
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">
                {{ title }}
              </h3>
              <button
                @click="close"
                class="text-gray-400 hover:text-gray-500 focus:outline-none"
              >
                <XIcon class="h-6 w-6" />
              </button>
            </div>

            <!-- Body -->
            <div class="p-6">
              <slot />
            </div>

            <!-- Footer -->
            <div
              v-if="showFooter"
              class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50"
            >
              <button
                v-if="showCancel"
                @click="close"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                {{ cancelText }}
              </button>
              <button
                v-if="showConfirm"
                @click="$emit('confirm')"
                :disabled="confirmDisabled"
                :class="[
                  'px-4 py-2 text-sm font-medium text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2',
                  confirmDisabled
                    ? 'bg-gray-400 cursor-not-allowed'
                    : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
                ]"
              >
                {{ confirmText }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { XIcon } from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    title: string
    size?: 'sm' | 'md' | 'lg' | 'xl'
    showFooter?: boolean
    showCancel?: boolean
    showConfirm?: boolean
    cancelText?: string
    confirmText?: string
    confirmDisabled?: boolean
  }>(),
  {
    size: 'md',
    showFooter: true,
    showCancel: true,
    showConfirm: true,
    cancelText: 'Cancel',
    confirmText: 'Confirm',
    confirmDisabled: false
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  confirm: []
  close: []
}>()

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'max-w-md w-full',
    md: 'max-w-lg w-full',
    lg: 'max-w-2xl w-full',
    xl: 'max-w-4xl w-full'
  }
  return sizes[props.size]
})

function close() {
  emit('update:modelValue', false)
  emit('close')
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
