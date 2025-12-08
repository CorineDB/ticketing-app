<template>
  <Modal
    v-model="isOpen"
    :title="user ? 'Edit User' : 'Create User'"
    size="lg"
    :confirm-disabled="!isFormValid || loading"
    @confirm="handleSubmit"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
          Name <span class="text-red-500">*</span>
        </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="Enter full name"
        />
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
          Email <span class="text-red-500">*</span>
        </label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="user@example.com"
        />
        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
      </div>

      <!-- Phone -->
      <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
          Phone
        </label>
        <input
          id="phone"
          v-model="form.phone"
          type="tel"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="+1 234 567 8900"
        />
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
          Password <span v-if="!user" class="text-red-500">*</span>
          <span v-else class="text-gray-500 text-xs">(leave blank to keep current)</span>
        </label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          :required="!user"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="Enter password"
          minlength="8"
        />
        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
        <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
      </div>

      <!-- Role Selection -->
      <div>
        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
          Role <span class="text-red-500">*</span>
        </label>
        <select
          id="role"
          v-model="form.role_id"
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
      <div v-if="form.role_id === '__create_new__'" class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-3">
        <h4 class="text-sm font-semibold text-blue-900">Create New Role</h4>
        <div>
          <label for="new_role_name" class="block text-sm font-medium text-blue-900 mb-1">
            Role Name <span class="text-red-500">*</span>
          </label>
          <input
            id="new_role_name"
            v-model="newRole.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="e.g., Event Manager"
          />
          <p class="mt-1 text-xs text-blue-700">
            Slug will be auto-generated: {{ generateSlug(newRole.name) }}
          </p>
        </div>
        <p class="text-xs text-blue-700">
          <InfoIcon class="w-3 h-3 inline mr-1" />
          The new role will inherit all permissions from your current role. Permissions can be modified later.
        </p>
      </div>

      <!-- Error Message -->
      <div v-if="errors.general" class="bg-red-50 border border-red-200 rounded-lg p-3">
        <p class="text-sm text-red-600">{{ errors.general }}</p>
      </div>
    </form>
  </Modal>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue'
import { useRoles } from '@/composables/useRoles'
import { useAuthStore } from '@/stores/auth'
import Modal from '@/components/common/Modal.vue'
import { InfoIcon } from 'lucide-vue-next'
import type { User } from '@/types/api'

const props = defineProps<{
  modelValue: boolean
  user?: User | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  submit: [data: any]
}>()

const { roles, fetchRoles, createRole } = useRoles()
const authStore = useAuthStore()
const loading = ref(false)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  role_id: ''
})

const newRole = reactive({
  name: '',
  slug: ''
})

const errors = reactive({
  email: '',
  password: '',
  general: ''
})

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const isFormValid = computed(() => {
  if (!form.name || !form.email || !form.role_id) return false
  if (!props.user && !form.password) return false
  if (form.role_id === '__create_new__' && !newRole.name) return false
  return true
})

// Watch for user prop changes to populate form
watch(() => props.user, (user) => {
  if (user) {
    form.name = user.name
    form.email = user.email
    form.phone = user.phone || ''
    form.password = ''
    form.role_id = user.role_id || ''
  } else {
    resetForm()
  }
}, { immediate: true })

// Fetch roles when modal opens
watch(() => props.modelValue, (isOpen) => {
  if (isOpen && roles.value.length === 0) {
    fetchRoles()
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

function resetForm() {
  form.name = ''
  form.email = ''
  form.phone = ''
  form.password = ''
  form.role_id = ''
  newRole.name = ''
  newRole.slug = ''
  errors.email = ''
  errors.password = ''
  errors.general = ''
}

async function handleSubmit() {
  errors.general = ''
  loading.value = true

  try {
    let roleId = form.role_id

    // Create new role if needed
    if (form.role_id === '__create_new__') {
      const slug = generateSlug(newRole.name)
      
      // Get current user's permissions to assign to new role
      const currentUserPermissions = authStore.user?.role?.permissions || []
      const permissionIds = currentUserPermissions.map(p => p.id)

      const createdRole = await createRole({
        name: newRole.name,
        slug: slug,
        permission_ids: permissionIds
      })
      
      roleId = createdRole.id
    }

    // Prepare user data
    const userData: any = {
      name: form.name,
      email: form.email,
      phone: form.phone || undefined,
      role_id: roleId
    }

    // Only include password if it's provided
    if (form.password) {
      userData.password = form.password
    }

    emit('submit', userData)
    isOpen.value = false
    resetForm()
  } catch (error: any) {
    errors.general = error.response?.data?.message || 'Failed to save user'
    console.error('Error saving user:', error)
  } finally {
    loading.value = false
  }
}
</script>
