<template>
  <Modal
    v-model="isOpen"
    title="User Details"
    size="lg"
    :show-footer="false"
  >
    <div v-if="user" class="space-y-6">
      <!-- User Header -->
      <div class="flex items-center gap-4 pb-4 border-b border-gray-200">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-2xl font-semibold">
          {{ getInitials(user.name) }}
        </div>
        <div class="flex-1">
          <h3 class="text-xl font-semibold text-gray-900">{{ user.name }}</h3>
          <p class="text-sm text-gray-500">{{ user.email }}</p>
        </div>
        <Badge :variant="getRoleVariant(user.role?.name)">
          {{ user.role?.name || 'No Role' }}
        </Badge>
      </div>

      <!-- Basic Information -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">Phone</label>
          <p class="text-sm text-gray-900">{{ user.phone || '-' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
          <StatusBadge :status="user.status || 'active'" type="custom" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">Last Login</label>
          <p class="text-sm text-gray-900">{{ user.last_login_at ? formatDate(user.last_login_at) : 'Never' }}</p>
        </div>
      </div>

      <!-- Role & Permissions -->
      <div v-if="user.role" class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">Role & Permissions</h4>
        <div class="space-y-2">
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Role</label>
            <p class="text-sm text-gray-900">{{ user.role.name }}</p>
            <p class="text-xs text-gray-500">{{ user.role.slug }}</p>
          </div>
          <div v-if="user.role.permissions && user.role.permissions.length > 0">
            <label class="block text-xs font-medium text-gray-500 mb-2">Permissions</label>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="permission in user.role.permissions"
                :key="permission.id"
                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800"
              >
                {{ permission.name }}
              </span>
            </div>
          </div>
          <p v-else class="text-xs text-gray-500 italic">No permissions assigned</p>
        </div>
      </div>

      <!-- Organization -->
      <div v-if="user.organisateur">
        <label class="block text-sm font-medium text-gray-500 mb-1">Organization</label>
        <p class="text-sm text-gray-900">{{ user.organisateur.name }}</p>
      </div>

      <!-- Timestamps -->
      <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Created At</label>
          <p class="text-xs text-gray-600">{{ formatDate(user.created_at) }}</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Updated At</label>
          <p class="text-xs text-gray-600">{{ formatDate(user.updated_at) }}</p>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { formatDate } from '@/utils/formatters'
import Modal from '@/components/common/Modal.vue'
import Badge from '@/components/common/Badge.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import type { User } from '@/types/api'

const props = defineProps<{
  modelValue: boolean
  user?: User | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

function getInitials(name: string): string {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

function getRoleVariant(roleName?: string): 'primary' | 'success' | 'warning' | 'danger' | 'info' | 'secondary' {
  if (!roleName) return 'secondary'

  const lowerRole = roleName.toLowerCase()
  if (lowerRole.includes('admin')) return 'danger'
  if (lowerRole.includes('organizer')) return 'primary'
  if (lowerRole.includes('scanner') || lowerRole.includes('agent')) return 'success'
  if (lowerRole.includes('cashier')) return 'warning'
  return 'secondary'
}
</script>
