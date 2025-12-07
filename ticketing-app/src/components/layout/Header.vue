<template>
  <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Left: Logo & Menu Toggle -->
        <div class="flex items-center gap-4">
          <button
            v-if="showMenuToggle"
            @click="$emit('toggle-sidebar')"
            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg lg:hidden"
          >
            <MenuIcon class="w-6 h-6" />
          </button>

          <RouterLink to="/" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
              <TicketIcon class="w-6 h-6 text-white" />
            </div>
            <span class="text-xl font-bold text-gray-900 hidden sm:block">Ticketing</span>
          </RouterLink>
        </div>

        <!-- Center: Search (optional) -->
        <div v-if="showSearch" class="hidden md:block flex-1 max-w-xl mx-8">
          <div class="relative">
            <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              type="text"
              placeholder="Search events, tickets..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Right: Notifications & User Menu -->
        <div class="flex items-center gap-3">
          <!-- Notifications -->
          <div v-if="isAuthenticated" class="relative">
            <button
              @click="showNotifications = !showNotifications"
              class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg relative"
            >
              <BellIcon class="w-6 h-6" />
              <span
                v-if="unreadCount > 0"
                class="absolute top-1 right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center px-1"
              >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </span>
            </button>

            <!-- Notifications Dropdown -->
            <Transition name="dropdown">
              <div
                v-if="showNotifications"
                ref="notificationsRef"
                class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 py-2 max-h-[500px] overflow-hidden flex flex-col"
              >
                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                  <h3 class="font-semibold text-gray-900">Notifications</h3>
                  <button
                    v-if="unreadCount > 0"
                    @click="handleMarkAllAsRead"
                    class="text-xs text-blue-600 hover:text-blue-700 font-medium"
                  >
                    Tout marquer comme lu
                  </button>
                </div>

                <!-- Notifications List -->
                <div class="overflow-y-auto flex-1">
                  <div v-if="notifications.length === 0" class="p-8 text-center text-gray-500">
                    <BellIcon class="w-12 h-12 mx-auto mb-3 opacity-30" />
                    <p class="text-sm">Aucune notification</p>
                  </div>

                  <div v-else>
                    <button
                      v-for="notification in notifications.slice(0, 10)"
                      :key="notification.id"
                      @click="handleNotificationClick(notification.id)"
                      :class="[
                        'w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors border-b border-gray-100',
                        !notification.read ? 'bg-blue-50' : ''
                      ]"
                    >
                      <div class="flex items-start gap-3">
                        <div :class="[
                          'w-2 h-2 rounded-full mt-2 flex-shrink-0',
                          !notification.read ? 'bg-blue-600' : 'bg-transparent'
                        ]"></div>
                        <div class="flex-1 min-w-0">
                          <p class="text-sm font-medium text-gray-900 mb-1">{{ notification.title }}</p>
                          <p class="text-sm text-gray-600 line-clamp-2">{{ notification.message }}</p>
                          <p class="text-xs text-gray-400 mt-1">{{ formatNotificationTime(notification.timestamp) }}</p>
                        </div>
                      </div>
                    </button>
                  </div>
                </div>

                <!-- Footer -->
                <div v-if="notifications.length > 0" class="px-4 py-2 border-t border-gray-200 text-center">
                  <RouterLink
                    to="/dashboard/notifications"
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    @click="showNotifications = false"
                  >
                    Voir toutes les notifications
                  </RouterLink>
                </div>
              </div>
            </Transition>
          </div>

          <!-- User Menu -->
          <div v-if="isAuthenticated" class="relative">
            <button
              @click="showUserMenu = !showUserMenu"
              class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg"
            >
              <div class="w-8 h-8 rounded-full flex items-center justify-center overflow-hidden" :class="!user?.avatar ? 'bg-gradient-to-br from-blue-500 to-purple-500' : ''">
                <img v-if="user?.avatar" :src="user.avatar" alt="Avatar" class="w-full h-full object-cover" />
                <span v-else class="text-white text-sm font-semibold">
                  {{ userInitials }}
                </span>
              </div>
              <div class="hidden md:block text-left">
                <div class="text-sm font-medium text-gray-900">{{ user?.name }}</div>
                <div class="text-xs text-gray-500">{{ userRole }}</div>
              </div>
              <ChevronDownIcon class="w-4 h-4 text-gray-400 hidden md:block" />
            </button>

            <!-- User Dropdown -->
            <Transition name="dropdown">
              <div
                v-if="showUserMenu"
                ref="userMenuRef"
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1"
              >
                <RouterLink
                  to="/dashboard"
                  class="block px-4 py-3 border-b border-gray-200 hover:bg-gray-50 transition-colors"
                  @click="showUserMenu = false"
                >
                  <div class="text-sm font-medium text-gray-900">{{ user?.name }}</div>
                  <div class="text-sm text-gray-500">{{ user?.email }}</div>
                </RouterLink>

                <RouterLink
                  :to="{ name: 'profile' }"
                  class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  <UserIcon class="w-4 h-4" />
                  My Profile
                </RouterLink>

                <RouterLink
                  v-if="authStore.isOrganizer"
                  to="/organisateur"
                  class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  <BuildingIcon class="w-4 h-4" />
                  My Organization
                </RouterLink>

                <RouterLink
                  to="/dashboard/settings"
                  class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  <SettingsIcon class="w-4 h-4" />
                  Settings
                </RouterLink>

                <hr class="my-1" />

                <button
                  @click="handleLogout"
                  class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-700 hover:bg-red-50"
                >
                  <LogOutIcon class="w-4 h-4" />
                  Logout
                </button>
              </div>
            </Transition>
          </div>

          <!-- Login Button (if not authenticated) -->
          <RouterLink
            v-else
            to="/login"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
          >
            Login
          </RouterLink>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotifications } from '@/composables/useNotifications'
import { onClickOutside } from '@vueuse/core'
import {
  MenuIcon,
  TicketIcon,
  SearchIcon,
  BellIcon,
  ChevronDownIcon,
  UserIcon,
  BuildingIcon,
  SettingsIcon,
  LogOutIcon
} from 'lucide-vue-next'

defineProps<{
  showMenuToggle?: boolean
  showSearch?: boolean
}>()

defineEmits<{
  'toggle-sidebar': []
}>()

const router = useRouter()
const authStore = useAuthStore()

const {
  notifications,
  unreadCount,
  markAsRead,
  markAllAsRead,
  setupWebSocketListeners,
  cleanup,
  requestNotificationPermission
} = useNotifications()

const showNotifications = ref(false)
const showUserMenu = ref(false)
const userMenuRef = ref<HTMLElement | null>(null)
const notificationsRef = ref<HTMLElement | null>(null)

// Close user menu when clicking outside
onClickOutside(userMenuRef, () => {
  showUserMenu.value = false
})

// Close notifications when clicking outside
onClickOutside(notificationsRef, () => {
  showNotifications.value = false
})

// Setup WebSocket listeners on mount
onMounted(() => {
  if (authStore.isAuthenticated) {
    setupWebSocketListeners()
    requestNotificationPermission()
  }
})

// Cleanup on unmount
onUnmounted(() => {
  cleanup()
})

const isAuthenticated = computed(() => authStore.isAuthenticated)
const user = computed(() => authStore.user)

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  const names = user.value.name.split(' ')
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase()
  }
  return names[0][0].toUpperCase()
})

const userRole = computed(() => {
  if (!user.value) return ''
  const roleMap: Record<string, string> = {
    'super-admin': 'Super Admin',
    organizer: 'Organizer',
    'agent-de-controle': 'Scanner',
    comptable: 'Cashier',
    participant: 'Participant'
  }
  return roleMap[user.value.type] || user.value.type
})

function handleNotificationClick(notificationId: string) {
  markAsRead(notificationId)
}

function handleMarkAllAsRead() {
  markAllAsRead()
}

function formatNotificationTime(date: Date): string {
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const minutes = Math.floor(diff / 60000)
  
  if (minutes < 1) return 'À l\'instant'
  if (minutes < 60) return `Il y a ${minutes} min`
  
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `Il y a ${hours}h`
  
  const days = Math.floor(hours / 24)
  return `Il y a ${days}j`
}

async function handleLogout() {
  showUserMenu.value = false
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
