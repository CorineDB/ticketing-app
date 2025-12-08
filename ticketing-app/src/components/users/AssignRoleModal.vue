<template>
  <Modal
    v-model="isOpen"
    title="Assign Role"
    size="md"
    :confirm-disabled="!selectedRoleId || loading"
    confirm-text="Assign Role"
    @confirm="handleAssign"
  >
    <div class="space-y-4">
      <!-- Current User Info -->
      <div v-if="user" class="bg-gray-50 rounded-lg p-3">
        <p class="text-sm text-gray-600">Assigning role to:</p>
        <p class="text-base font-semibold text-gray-900">{{ user.name }}</p>
        <p class="text-xs text-gray-500">{{ user.email }}</p>
      </div>

      <!-- Current Role -->
      <div v-if="user?.role">
        <label class="block text-sm font-medium text-gray-700 mb-1">Current Role</label>
        <Badge :variant="getRoleVariant(user.role.name)">
          {{ user.role.name }}
        </Badge>
      </div>

      <!-- Role Selection -->
      <div>
        <label for="role-select" class="block text-sm font-medium text-gray-700 mb-1">
          New Role <span class="text-red-500">*</span>
        </label>
        <select
          id="role-select"
          v-model="selectedRoleId"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Select a role</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.name }}
          </option>
          <option value="__create_new__">+ Create New Role</option>
        </select>
      </div>

      <!-- New Role Creation (inline) -->
      <div v-if="selectedRoleId === '__create_new__'" class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-3">
        <h4 class="text-sm font-semibold text-blue-900">Create New Role</h4>
        <div>
          <label for="new-role-name" class="block text-sm font-medium text-blue-900 mb-1">
            Role Name <span class="text-red-500">*</span>
          </label>
          <input
            id="new-role-name"
            v-model="newRoleName"
            type="text"
            required
            class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="e.g., Event Manager"
          />
          <p class="mt-1 text-xs text-blue-700">
            Slug will be auto-generated: {{ generateSlug(newRoleName) }}
          </p>
        </div>
        <p class="text-xs text-blue-700">
          <InfoIcon class="w-3 h-3 inline mr-1" />
          The new role will inherit all permissions from your current role.
        </p>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3">
        <p class="text-sm text-red-600">{{ error }}</p>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoles } from '@/composables/useRoles'
import { useAuthStore } from '@/stores/auth'
import Modal from '@/components/common/Modal.vue'
import Badge from '@/components/common/Badge.vue'
import { InfoIcon } from 'lucide-vue-next'
import type { User } from '@/types/api'

const props = defineProps<{
  modelValue: boolean
  user?: User | null
  roles: any[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  submit: [data: { role_id: string }]
}>()

const { createRole } = useRoles()
const authStore = useAuthStore()

const selectedRoleId = ref('')
const newRoleName = ref('')
const loading = ref(false)
const error = ref('')

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Reset form when modal opens/closes
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    selectedRoleId.value = props.user?.role_id || ''
    newRoleName.value = ''
    error.value = ''
  }
})

function generateSlug(name: string): string {
  return name
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
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

async function handleAssign() {
  error.value = ''
  loading.value = true

  try {
    let roleId = selectedRoleId.value

    // Create new role if needed
    if (selectedRoleId.value === '__create_new__') {
      if (!newRoleName.value) {
        error.value = 'Please enter a role name'
        loading.value = false
        return
      }

      const slug = generateSlug(newRoleName.value)
      
      // Get current user's permissions
      const currentUserPermissions = authStore.user?.role?.permissions || []
      const permissionIds = currentUserPermissions.map(p => p.id)

      const createdRole = await createRole({
        name: newRoleName.value,
        slug: slug,
        permission_ids: permissionIds
      })
      
      roleId = createdRole.id
    }

    emit('submit', { role_id: roleId })
    isOpen.value = false
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to assign role'
    console.error('Error assigning role:', err)
  } finally {
    loading.value = false
  }
}
</script>
