<template>
  <DashboardLayout>
    <div v-if="user" class="max-w-5xl mx-auto space-y-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Mon Profil</h1>
          <p class="mt-1 text-sm text-gray-600">Gérez vos informations personnelles et paramètres de sécurité</p>
        </div>
      </div>

      <!-- Profile Card -->
      <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-sm border border-blue-100 p-8">
        <div class="flex items-center gap-6">
          <!-- Avatar -->
          <div class="relative">
            <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-lg">
              {{ getInitials(user?.name || '') }}
            </div>
            <div class="absolute -bottom-1 -right-1 w-7 h-7 bg-green-500 border-4 border-white rounded-full"></div>
          </div>

          <!-- User Info -->
          <div class="flex-1">
            <h2 class="text-2xl font-bold text-gray-900">{{ user?.name }}</h2>
            <p class="text-gray-600 mt-1">{{ user?.email }}</p>
            <div class="flex items-center gap-3 mt-3">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <ShieldCheckIcon class="w-4 h-4 mr-1" />
                {{ user?.role?.name || 'Utilisateur' }}
              </span>
              <span v-if="user?.created_at" class="text-sm text-gray-500">
                Membre depuis {{ formatDate(user.created_at) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
          <nav class="flex -mb-px">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              <component :is="tab.icon" class="w-5 h-5" />
              {{ tab.label }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Profile Tab -->
          <div v-if="activeTab === 'profile'" class="space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations personnelles</h3>
              <form @submit.prevent="updateProfile" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                      Nom complet
                    </label>
                    <input
                      id="name"
                      v-model="profileForm.name"
                      type="text"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                      placeholder="Votre nom complet"
                    />
                  </div>

                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                      Adresse email
                    </label>
                    <input
                      id="email"
                      v-model="profileForm.email"
                      type="email"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                      placeholder="votre@email.com"
                    />
                  </div>
                </div>

                <!-- Role & Permissions (Read-only) -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                  <h4 class="text-sm font-semibold text-gray-900 mb-3">Rôle et permissions</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <p class="text-xs text-gray-500 mb-1">Rôle</p>
                      <p class="text-sm font-medium text-gray-900">{{ user?.role?.name || 'N/A' }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 mb-1">Type</p>
                      <p class="text-sm font-medium text-gray-900">{{ user?.type || 'N/A' }}</p>
                    </div>
                  </div>
                  <div v-if="user?.role?.permissions && user.role.permissions.length > 0" class="mt-3">
                    <p class="text-xs text-gray-500 mb-2">Permissions</p>
                    <div class="flex flex-wrap gap-2">
                      <span
                        v-for="permission in user.role.permissions.slice(0, 6)"
                        :key="permission.id"
                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-200 text-gray-700"
                      >
                        {{ permission.name }}
                      </span>
                      <span
                        v-if="user.role.permissions.length > 6"
                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-300 text-gray-700"
                      >
                        +{{ user.role.permissions.length - 6 }} autres
                      </span>
                    </div>
                  </div>
                </div>

                <div class="flex gap-3 pt-2">
                  <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 transition-colors"
                  >
                    <LoaderIcon v-if="saving" class="w-5 h-5 animate-spin" />
                    <SaveIcon v-else class="w-5 h-5" />
                    <span>{{ saving ? 'Enregistrement...' : 'Enregistrer les modifications' }}</span>
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Security Tab -->
          <div v-if="activeTab === 'security'" class="space-y-6">
            <!-- Change Password -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Changer le mot de passe</h3>
              <form @submit.prevent="changePassword" class="space-y-4 max-w-md">
                <div>
                  <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                    Mot de passe actuel
                  </label>
                  <input
                    id="current_password"
                    v-model="passwordForm.current_password"
                    type="password"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="••••••••"
                  />
                </div>

                <div>
                  <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                    Nouveau mot de passe
                  </label>
                  <input
                    id="new_password"
                    v-model="passwordForm.new_password"
                    type="password"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="••••••••"
                  />
                  <p class="mt-1 text-xs text-gray-500">Minimum 8 caractères</p>
                </div>

                <div>
                  <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirmer le nouveau mot de passe
                  </label>
                  <input
                    id="confirm_password"
                    v-model="passwordForm.confirm_password"
                    type="password"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="••••••••"
                  />
                </div>

                <button
                  type="submit"
                  :disabled="changingPassword"
                  class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 transition-colors"
                >
                  <LoaderIcon v-if="changingPassword" class="w-5 h-5 animate-spin" />
                  <KeyIcon v-else class="w-5 h-5" />
                  <span>{{ changingPassword ? 'Modification...' : 'Changer le mot de passe' }}</span>
                </button>
              </form>
            </div>

            <!-- Security Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex gap-3">
                <InfoIcon class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                <div>
                  <h4 class="text-sm font-semibold text-blue-900">Conseils de sécurité</h4>
                  <ul class="mt-2 text-sm text-blue-800 space-y-1">
                    <li>• Utilisez un mot de passe unique et complexe</li>
                    <li>• Ne partagez jamais votre mot de passe</li>
                    <li>• Changez votre mot de passe régulièrement</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Activity Tab -->
          <div v-if="activeTab === 'activity'" class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Activité récente</h3>

            <div class="space-y-3">
              <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                  <CheckCircleIcon class="w-5 h-5 text-green-600" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900">Compte créé</div>
                  <div class="text-xs text-gray-500">{{ formatDateTime(user?.created_at || new Date().toISOString()) }}</div>
                </div>
              </div>

              <div v-if="user?.updated_at && user.updated_at !== user.created_at" class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                  <UserIcon class="w-5 h-5 text-blue-600" />
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900">Profil mis à jour</div>
                  <div class="text-xs text-gray-500">{{ formatDateTime(user.updated_at) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="max-w-5xl mx-auto space-y-6 p-6 text-center py-12">
      <LoaderIcon class="w-8 h-8 mx-auto animate-spin text-blue-500" />
      <p class="text-gray-500 mt-4">Chargement du profil...</p>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { formatDate, formatDateTime } from '@/utils/formatters'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import {
  LoaderIcon,
  UserIcon,
  ShieldIcon,
  ClockIcon,
  ShieldCheckIcon,
  SaveIcon,
  KeyIcon,
  InfoIcon,
  CheckCircleIcon
} from 'lucide-vue-next'

const authStore = useAuthStore()
const user = computed(() => authStore.user)

const activeTab = ref('profile')
const saving = ref(false)
const changingPassword = ref(false)

const profileForm = ref({
  name: authStore.user?.name || '',
  email: authStore.user?.email || ''
})

const passwordForm = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const tabs = [
  { id: 'profile', label: 'Profil', icon: UserIcon },
  { id: 'security', label: 'Sécurité', icon: ShieldIcon },
  { id: 'activity', label: 'Activité', icon: ClockIcon }
]

function getInitials(name: string): string {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2) || 'U'
}

async function updateProfile() {
  saving.value = true
  try {
    const updated = await authStore.updateUserProfile(profileForm.value)
    if (updated) {
      alert('✅ Profil mis à jour avec succès!')
    } else {
      alert('❌ Échec de la mise à jour du profil')
    }
  } catch (error: any) {
    console.error('Failed to update profile:', error)
    alert('❌ Erreur: ' + (error.response?.data?.message || 'Échec de la mise à jour'))
  } finally {
    saving.value = false
  }
}

async function changePassword() {
  if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
    alert('❌ Les mots de passe ne correspondent pas')
    return
  }

  if (passwordForm.value.new_password.length < 8) {
    alert('❌ Le mot de passe doit contenir au moins 8 caractères')
    return
  }

  changingPassword.value = true
  try {
    const changed = await authStore.changePassword(
      passwordForm.value.current_password,
      passwordForm.value.new_password
    )
    if (changed) {
      alert('✅ Mot de passe modifié avec succès!')
      passwordForm.value = {
        current_password: '',
        new_password: '',
        confirm_password: ''
      }
    } else {
      alert('❌ Échec de la modification du mot de passe')
    }
  } catch (error: any) {
    console.error('Failed to change password:', error)
    alert('❌ Erreur: ' + (error.response?.data?.message || 'Mot de passe actuel incorrect'))
  } finally {
    changingPassword.value = false
  }
}

onMounted(() => {
  // Refresh user data
  authStore.fetchUser()
})
</script>

<style scoped>
/* Custom styles if needed */
</style>