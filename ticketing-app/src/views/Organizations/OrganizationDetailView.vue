<template>
  <DashboardLayout>
    <div class="container mx-auto px-4 py-8">
      <div v-if="loading" class="text-center text-gray-500">Loading organization details...</div>
      <div v-else-if="error" class="text-center text-red-500">Error: {{ error }}</div>
      <div v-else-if="organization">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">{{ organization.name }}</h1>
          <div class="flex space-x-2">
            <!--
            <Can permission="manage_organizations">
            -->
              <RouterLink
                :to="{ name: 'organization-edit', params: { id: organization.id } }"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Edit Organization
              </RouterLink>
              <button
                @click="deleteOrganization"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
              >
                Delete Organization
              </button>
            <!--
            </Can>
            -->
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
          <h2 class="text-xl font-semibold mb-4">Organization Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-gray-500">Email:</p>
              <p class="font-medium">{{ organization.email }}</p>
            </div>
            <div>
              <p class="text-gray-500">Phone:</p>
              <p class="font-medium">{{ organization.phone || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Address:</p>
              <p class="font-medium">{{ organization.address || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-gray-500">Status:</p>
              <p class="font-medium">
                <StatusBadge :status="organization.status" /> <!-- Assuming status field -->
              </p>
            </div>
            <!-- Add more organization details here -->
          </div>
        </div>

        <!-- Placeholder for organization members or other related data -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold mb-4">Members</h2>
          <p class="text-gray-500">List of members will go here.</p>
        </div>
      </div>
      <div v-else class="text-center text-gray-500">Organization not found.</div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Can from '@/components/permissions/Can.vue'
import StatusBadge from '@/components/common/StatusBadge.vue' // This component exists
// Assuming a useOrganizations composable exists or will be created
import { useOrganizations } from '@/composables/useOrganizations'

const route = useRoute()
const router = useRouter()
const { organization, loading, error, fetchOrganizationById, removeOrganization } = useOrganizations()

onMounted(() => {
  const orgId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id
  if (orgId) {
    fetchOrganizationById(orgId)
  }
})

const deleteOrganization = async () => {
  if (confirm('Are you sure you want to delete this organization?')) {
    const orgId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id
    if (orgId) {
      await removeOrganization(orgId)
      if (!error.value) {
        router.push({ name: 'organizations' }) // Redirect to organizations list after deletion
      }
    }
  }
}
</script>

<style scoped>
/* Scoped styles for this component */
</style>
