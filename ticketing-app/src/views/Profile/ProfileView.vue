<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
        <p class="mt-2 text-gray-600">Manage your account settings and preferences</p>
      </div>

      <!-- Profile Card -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-6">
          <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-3xl font-bold">
            {{ getInitials(user?.name || '') }}
          </div>
          <div>
            <h2 class="text-2xl font-semibold text-gray-900">{{ user?.name }}</h2>
            <p class="text-gray-600">{{ user?.email }}</p>
            <div class="flex items-center gap-2 mt-2">
              <Badge :variant="getRoleVariant(user?.role?.name)">
                {{ user?.role?.name }}
              </Badge>
              <StatusBadge :status="user?.status || 'active'" type="user" />
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <Tabs v-model="activeTab" :tabs="tabs">
        <!-- Profile Information -->
        <template #profile>
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Personal Information</h3>

            <form @submit.prevent="updateProfile" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name
                  </label>
                  <input
                    id="name"
                    v-model="profileForm.name"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address
                  </label>
                  <input
                    id="email"
                    v-model="profileForm.email"
                    type="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number
                  </label>
                  <input
                    id="phone"
                    v-model="profileForm.phone"
                    type="tel"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label for="organization" class="block text-sm font-medium text-gray-700 mb-2">
                    Organization
                  </label>
                  <input
                    id="organization"
                    :value="user?.organization?.name || 'N/A'"
                    type="text"
                    disabled
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                  />
                </div>
              </div>

              <div class="flex gap-3 pt-4">
                <button
                  type="submit"
                  :disabled="saving"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                  <LoaderIcon v-if="saving" class="w-5 h-5 animate-spin" />
                  <span>{{ saving ? 'Saving...' : 'Save Changes' }}</span>
                </button>
              </div>
            </form>
          </div>
        </template>

        <!-- Security -->
        <template #security>
          <div class="space-y-6">
            <!-- Change Password -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-6">Change Password</h3>

              <form @submit.prevent="changePassword" class="space-y-4">
                <div>
                  <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                    Current Password
                  </label>
                  <input
                    id="current_password"
                    v-model="passwordForm.current_password"
                    type="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                    New Password
                  </label>
                  <input
                    id="new_password"
                    v-model="passwordForm.new_password"
                    type="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm New Password
                  </label>
                  <input
                    id="confirm_password"
                    v-model="passwordForm.confirm_password"
                    type="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <button
                  type="submit"
                  :disabled="changingPassword"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                  <LoaderIcon v-if="changingPassword" class="w-5 h-5 animate-spin" />
                  <span>{{ changingPassword ? 'Changing...' : 'Change Password' }}</span>
                </button>
              </form>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Two-Factor Authentication</h3>
                  <p class="text-sm text-gray-600 mt-1">Add an extra layer of security to your account</p>
                </div>
                <button
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Enable
                </button>
              </div>
            </div>
          </div>
        </template>

        <!-- Activity -->
        <template #activity>
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Recent Activity</h3>

            <div class="space-y-4">
              <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <LogInIcon class="w-5 h-5 text-blue-600" />
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">Logged in</div>
                    <div class="text-xs text-gray-500">{{ formatDateTime(user?.last_login_at || new Date().toISOString()) }}</div>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <CheckCircleIcon class="w-5 h-5 text-green-600" />
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">Account created</div>
                    <div class="text-xs text-gray-500">{{ formatDate(user?.created_at || new Date().toISOString()) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- Preferences -->
        <template #preferences>
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Preferences</h3>

            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-medium text-gray-900">Email Notifications</div>
                  <div class="text-sm text-gray-600">Receive email updates about your account</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer" checked />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <div class="font-medium text-gray-900">Event Reminders</div>
                  <div class="text-sm text-gray-600">Get reminders before your events</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer" checked />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <div class="font-medium text-gray-900">Marketing Emails</div>
                  <div class="text-sm text-gray-600">Receive updates about new features and events</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer" />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>
          </div>
        </template>
      </Tabs>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { formatDate, formatDateTime } from '@/utils/formatters'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Tabs from '@/components/common/Tabs.vue'
import Badge from '@/components/common/Badge.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import {
  LoaderIcon,
  LogInIcon,
  CheckCircleIcon,
  UserIcon,
  ShieldIcon,
  ClockIcon,
  SettingsIcon
} from 'lucide-vue-next'

const authStore = useAuthStore()
const user = authStore.user

const activeTab = ref('profile')
const saving = ref(false)
const changingPassword = ref(false)

const profileForm = ref({
  name: user?.name || '',
  email: user?.email || '',
  phone: user?.phone || ''
})

const passwordForm = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const tabs = [
  { id: 'profile', label: 'Profile', icon: UserIcon },
  { id: 'security', label: 'Security', icon: ShieldIcon },
  { id: 'activity', label: 'Activity', icon: ClockIcon },
  { id: 'preferences', label: 'Preferences', icon: SettingsIcon }
]

function getInitials(name: string): string {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

function getRoleVariant(roleName?: string): 'primary' | 'success' | 'warning' | 'danger' | 'default' {
  if (!roleName) return 'default'

  const lowerRole = roleName.toLowerCase()
  if (lowerRole.includes('admin')) return 'danger'
  if (lowerRole.includes('organizer')) return 'primary'
  if (lowerRole.includes('scanner')) return 'success'
  if (lowerRole.includes('cashier')) return 'warning'
  return 'default'
}

async function updateProfile() {
  saving.value = true
  try {
    // Call API to update profile
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
    // authStore.updateUser(profileForm.value)
    alert('Profile updated successfully!')
  } catch (error) {
    console.error('Failed to update profile:', error)
    alert('Failed to update profile')
  } finally {
    saving.value = false
  }
}

async function changePassword() {
  if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
    alert('Passwords do not match')
    return
  }

  changingPassword.value = true
  try {
    // Call API to change password
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
    alert('Password changed successfully!')
    passwordForm.value = {
      current_password: '',
      new_password: '',
      confirm_password: ''
    }
  } catch (error) {
    console.error('Failed to change password:', error)
    alert('Failed to change password')
  } finally {
    changingPassword.value = false
  }
}
</script>
