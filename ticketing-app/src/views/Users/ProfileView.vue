<template>
  <DashboardLayout>
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">User Profile</h1>

      <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Profile Information</h2>
        <div v-if="loading" class="text-center text-gray-500">Loading profile...</div>
        <div v-else-if="error" class="text-center text-red-500">Error: {{ error }}</div>
        <form v-else-if="user" @submit.prevent="updateProfile" class="space-y-6">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
            <input
              id="name"
              v-model="formData.name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Email (read-only for now) -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input
              id="email"
              v-model="formData.email"
              type="email"
              readonly
              class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
            />
          </div>

          <!-- Password change (separate form or modal typically) -->
          <h3 class="text-lg font-medium text-gray-900 pt-4">Change Password</h3>
          <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2"
              >Current Password</label
            >
            <input
              id="current_password"
              v-model="passwordData.current_password"
              type="password"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2"
              >New Password</label
            >
            <input
              id="new_password"
              v-model="passwordData.new_password"
              type="password"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2"
              >Confirm New Password</label
            >
            <input
              id="confirm_password"
              v-model="passwordData.confirm_password"
              type="password"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Error Message -->
          <div v-if="profileError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex gap-3">
              <AlertCircleIcon class="w-5 h-5 text-red-600 flex-shrink-0" />
              <p class="text-sm text-red-700">{{ profileError }}</p>
            </div>
          </div>

          <button
            type="submit"
            :disabled="profileLoading"
            class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <LoaderIcon v-if="profileLoading" class="w-5 h-5 animate-spin" />
            <span>Update Profile</span>
          </button>
        </form>
        <div v-else class="text-center text-gray-500">User not found.</div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth' // Assuming useAuthStore contains current user
import { AlertCircleIcon, LoaderIcon } from 'lucide-vue-next'

const authStore = useAuthStore()

const loading = ref(false)
const error = ref<string | null>(null)
const profileLoading = ref(false)
const profileError = ref<string | null>(null)

const formData = ref({
  name: '',
  email: ''
  // Add other profile fields here if needed
})

const passwordData = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

onMounted(() => {
  if (authStore.user) {
    formData.value.name = authStore.user.name || ''
    formData.value.email = authStore.user.email || ''
  } else {
    // Optionally fetch user if not in store (e.g., on direct page load without prior auth check)
    // For now, assuming authStore.user is populated after login
  }
})

watch(() => authStore.user, (newUser) => {
  if (newUser) {
    formData.value.name = newUser.name || ''
    formData.value.email = newUser.email || ''
  }
}, { immediate: true }) // Immediate watch to populate if user is already there

const updateProfile = async () => {
  profileLoading.value = true
  profileError.value = null

  try {
    // Update basic profile info
    if (authStore.user && (formData.value.name !== (authStore.user.name || ''))) {
      await authStore.updateUserProfile({ name: formData.value.name }) // Assuming authStore has this method
    }

    // Update password if fields are filled
    if (passwordData.value.new_password) {
      if (passwordData.value.new_password !== passwordData.value.confirm_password) {
        profileError.value = 'New password and confirmation do not match.'
        return
      }
      // Assuming authStore has an updatePassword method
      await authStore.updatePassword(
        passwordData.value.current_password,
        passwordData.value.new_password
      )
      // Clear password fields on success
      passwordData.value = { current_password: '', new_password: '', confirm_password: '' }
    }

    // Success notification
    // notifications.success('Profile Updated', 'Your profile has been updated successfully.')
  } catch (err: any) {
    profileError.value = err.response?.data?.message || 'Failed to update profile.'
  } finally {
    profileLoading.value = false
  }
}
</script>

<style scoped>
/* Scoped styles for this component */
</style>
