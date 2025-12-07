<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Users</h1>
          <p class="mt-2 text-gray-600">Manage system users and their permissions</p>
        </div>
        <button
          v-if="canManageUsers"
          @click="showUserModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
        >
          <PlusIcon class="w-5 h-5" />
          Create User
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="lg:col-span-2">
            <div class="relative">
              <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search by name or email..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                @input="debouncedSearch"
              />
            </div>
          </div>

          <!-- Role Filter -->
          <select
            v-model="filters.role_id"
            @change="fetchUsers"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Roles</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.name }}
            </option>
          </select>

          <!-- Status Filter -->
          <select
            v-model="filters.status"
            @change="fetchUsers"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>
      </div>

      <!-- Results Count -->
      <div class="text-sm text-gray-600">
        {{ users.length }} user(s) found
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-12 text-center text-gray-500">
          <LoaderIcon class="w-8 h-8 animate-spin mx-auto mb-2" />
          Loading users...
        </div>
      </div>

      <!-- Users Table -->
      <div v-else-if="users.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">User</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Role</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Organization</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Status</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Last Login</th>
                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="user in users"
                :key="user.id"
                class="border-b border-gray-100 hover:bg-gray-50"
              >
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                      {{ getInitials(user.name) }}
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-xs text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <Badge :variant="getRoleVariant(user.role?.name)">
                      {{ user.role?.name || 'No Role' }}
                    </Badge>
                  </div>
                </td>
                <td class="py-3 px-4 text-sm text-gray-600">
                  {{ user.organisateur?.name || '-' }}
                </td>
                <td class="py-3 px-4">
                  <StatusBadge :status="user.status" type="custom" />
                </td>
                <td class="py-3 px-4 text-sm text-gray-600">
                  {{ user.last_login_at ? formatDate(user.last_login_at) : 'Never' }}
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <button
                      @click="viewUser(user)"
                      class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                      title="View user"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="canManageUsers"
                      @click="editUser(user)"
                      class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                      title="Edit"
                    >
                      <EditIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="canAssignRoles"
                      @click="assignRole(user)"
                      class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg"
                      title="Assign role"
                    >
                      <ShieldIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="canManageUsers"
                      @click="confirmDelete(user)"
                      class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                      title="Delete"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <UsersIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No users found</h3>
        <p class="text-gray-600 mb-6">No users match your current filters</p>
        <button
          @click="clearFilters"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
        >
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Modals -->
    <!-- TODO: Uncomment when modals are created
    <UserFormModal
      v-model="showUserModal"
      :user="selectedUser"
      @submit="handleUserSubmit"
    />

    <UserDetailModal
      v-model="showDetailModal"
      :user="selectedUser"
    />

    <AssignRoleModal
      v-model="showRoleModal"
      :user="selectedUser"
      :roles="roles"
      @submit="handleRoleAssign"
    />
    -->

    <ConfirmModal
      v-model="showDeleteModal"
      title="Delete User"
      message="Are you sure you want to delete this user? This action cannot be undone."
      variant="danger"
      confirm-text="Delete"
      @confirm="handleDelete"
    />
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useUsers } from '@/composables/useUsers'
import { useRoles } from '@/composables/useRoles'
import { usePermissions } from '@/composables/usePermissions'
import { formatDate } from '@/utils/formatters'
import type { User, UserFilters } from '@/types/api'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Badge from '@/components/common/Badge.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
// TODO: Create these components
// import UserFormModal from '@/components/users/UserFormModal.vue'
// import UserDetailModal from '@/components/users/UserDetailModal.vue'
// import AssignRoleModal from '@/components/users/AssignRoleModal.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import {
  PlusIcon,
  SearchIcon,
  LoaderIcon,
  UsersIcon,
  EyeIcon,
  EditIcon,
  ShieldIcon,
  TrashIcon
} from 'lucide-vue-next'

const { users, loading, fetchUsers, createUser, updateUser, deleteUser } = useUsers()
const { roles, fetchRoles } = useRoles()
const {
  canManageUsers,
  canAssignRoles
} = usePermissions()

const filters = ref<UserFilters>({
  search: '',
  role_id: '',
  status: ''
})

const showUserModal = ref(false)
const showDetailModal = ref(false)
const showRoleModal = ref(false)
const showDeleteModal = ref(false)
const selectedUser = ref<User | null>(null)
const userToDelete = ref<User | null>(null)

let searchTimeout: any = null

onMounted(() => {
  loadData()
})

async function loadData() {
  await Promise.all([
    fetchUsers(filters.value),
    fetchRoles()
  ])
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchUsers(filters.value)
  }, 500)
}

function clearFilters() {
  filters.value = {
    search: '',
    role_id: '',
    status: ''
  }
  fetchUsers(filters.value)
}

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
  if (lowerRole.includes('scanner')) return 'success'
  if (lowerRole.includes('cashier')) return 'warning'
  return 'secondary'
}

function viewUser(user: User) {
  selectedUser.value = user
  showDetailModal.value = true
}

function editUser(user: User) {
  selectedUser.value = user
  showUserModal.value = true
}

function assignRole(user: User) {
  selectedUser.value = user
  showRoleModal.value = true
}

function confirmDelete(user: User) {
  userToDelete.value = user
  showDeleteModal.value = true
}

async function handleUserSubmit(data: any) {
  if (selectedUser.value) {
    await updateUser(selectedUser.value.id, data)
  } else {
    await createUser(data)
  }
  selectedUser.value = null
  await fetchUsers(filters.value)
}

async function handleRoleAssign(data: { role_id: string }) {
  if (selectedUser.value) {
    await updateUser(selectedUser.value.id, data)
    selectedUser.value = null
    await fetchUsers(filters.value)
  }
}

async function handleDelete() {
  if (userToDelete.value) {
    await deleteUser(userToDelete.value.id)
    userToDelete.value = null
    await fetchUsers(filters.value)
  }
}
</script>
