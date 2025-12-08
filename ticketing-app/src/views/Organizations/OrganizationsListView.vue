<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Organizations</h1>
          <p class="mt-2 text-gray-600">Manage all organisateurs in the system</p>
        </div>
        <button
          @click="showOrgModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
        >
          <PlusIcon class="w-5 h-5" />
          Create Organization
        </button>
      </div>

      <!-- Search -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="relative">
          <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by organisateur name..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            @input="debouncedSearch"
          />
        </div>
      </div>

      <!-- Results Count -->
      <div class="text-sm text-gray-600">
        {{ organisateurs.length }} organisateur(s) found
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-12 text-center text-gray-500">
          <LoaderIcon class="w-8 h-8 animate-spin mx-auto mb-2" />
          Loading organisateurs...
        </div>
      </div>

      <!-- Organizations Grid -->
      <div v-else-if="organisateurs.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="org in organisateurs"
          :key="org.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <!-- Organization Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                {{ getInitials(org.name) }}
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ org.name }}</h3>
                                 <StatusBadge :status="org.status || 'active'" type="custom" />              </div>
            </div>
          </div>

          <!-- Organization Stats -->
          <div class="space-y-3 mb-4">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600">Events</span>
              <span class="font-medium text-gray-900">{{ org.events_count || 0 }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600">Users</span>
              <span class="font-medium text-gray-900">{{ org.users_count || 0 }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600">Revenue</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(org.total_revenue || 0) }}</span>
            </div>
          </div>

          <!-- Contact Info -->
          <div class="border-t border-gray-200 pt-4 mb-4">
            <div class="text-xs text-gray-500 mb-1">Contact</div>
            <div class="text-sm text-gray-900">{{ org.contact_email || 'No email' }}</div>
            <div v-if="org.phone" class="text-sm text-gray-600">{{ org.phone }}</div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2">
            <button
              @click="viewOrganization(org)"
              class="flex-1 px-3 py-2 text-sm text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100"
            >
              View Details
            </button>
            <button
              @click="editOrganization(org)"
              class="px-3 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200"
            >
              <EditIcon class="w-4 h-4" />
            </button>
            <button
              @click="confirmDelete(org)"
              class="px-3 py-2 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <BuildingIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No organisateurs found</h3>
        <p class="text-gray-600 mb-6">Get started by creating your first organisateur</p>
        <button
          @click="showOrgModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center gap-2"
        >
          <PlusIcon class="w-5 h-5" />
          Create Organization
        </button>
      </div>
    </div>

    <!-- Modals -->
    <OrganizationFormModal
      v-model="showOrgModal"
      :organisateur="selectedOrg"
      @submit="handleOrgSubmit"
    />

    <OrganizationDetailModal
      v-model="showDetailModal"
      :organisateur="selectedOrg"
    />

    <ConfirmModal
      v-model="showDeleteModal"
      title="Delete Organization"
      message="Are you sure you want to delete this organisateur? This will also delete all associated events, users, and data. This action cannot be undone."
      variant="danger"
      confirm-text="Delete"
      @confirm="handleDelete"
    />
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useOrganizations } from '@/composables/useOrganizations'
import { formatCurrency } from '@/utils/formatters'
import type { Organization } from '@/types/api'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import OrganizationFormModal from '@/components/organizations/OrganizationFormModal.vue'
import OrganizationDetailModal from '@/components/organizations/OrganizationDetailModal.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'
import {
  PlusIcon,
  SearchIcon,
  LoaderIcon,
  BuildingIcon,
  EditIcon,
  TrashIcon
} from 'lucide-vue-next'

const { organisateurs, loading, fetchOrganizations, createOrganization, updateOrganization, deleteOrganization } = useOrganizations()

const searchQuery = ref('')
const showOrgModal = ref(false)
const showDetailModal = ref(false)
const showDeleteModal = ref(false)
const selectedOrg = ref<Organization | null>(null)
const orgToDelete = ref<Organization | null>(null)

let searchTimeout: any = null

onMounted(() => {
  loadOrganizations()
})

async function loadOrganizations() {
  await fetchOrganizations({ search: searchQuery.value })
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadOrganizations()
  }, 500)
}

function getInitials(name: string): string {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

function viewOrganization(org: Organization | null = null) {
  selectedOrg.value = org
  showDetailModal.value = true
}

function editOrganization(org: Organization | null = null) {
  selectedOrg.value = org
  showOrgModal.value = true
}

function confirmDelete(org: Organization | null = null) {
  orgToDelete.value = org
  showDeleteModal.value = true
}

async function handleOrgSubmit(data: any) {
  if (selectedOrg.value) {
    await updateOrganization(selectedOrg.value.id, data)
  } else {
    await createOrganization(data)
  }
  selectedOrg.value = null
  await loadOrganizations()
}

async function handleDelete() {
  if (orgToDelete.value) {
    await deleteOrganization(orgToDelete.value.id)
    orgToDelete.value = null
    await loadOrganizations()
  }
}
</script>
