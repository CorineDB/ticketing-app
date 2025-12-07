<template>
  <DashboardLayout>
    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Mon Profil</h1>
        <p class="text-gray-600 mt-2">Gérez vos informations personnelles et vos préférences</p>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex gap-3">
          <CheckCircleIcon class="w-5 h-5 text-green-600 flex-shrink-0" />
          <p class="text-sm text-green-700">{{ successMessage }}</p>
        </div>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-gray-500">Chargement du profil...</p>
      </div>

      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
        <AlertCircleIcon class="w-12 h-12 text-red-500 mx-auto mb-3" />
        <p class="text-red-700">{{ error }}</p>
      </div>

      <div v-else-if="authStore.user" class="space-y-6">
        
        <!-- Profile Picture Section -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2">
            <UserCircleIcon class="w-6 h-6 text-blue-600" />
            Photo de profil
          </h2>
          
          <form @submit.prevent="updateAvatar">
            <div class="flex flex-col sm:flex-row items-center gap-6">
              <!-- Avatar Preview -->
              <div class="relative">
                <div v-if="avatarPreview || formData.avatar" class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-200">
                  <img :src="avatarPreview || formData.avatar" alt="Avatar" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white text-4xl font-bold border-4 border-gray-200">
                  {{ userInitials }}
                </div>
                <button
                  v-if="avatarPreview || formData.avatar"
                  type="button"
                  @click="removeAvatar"
                  class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 text-white rounded-full hover:bg-red-600 transition flex items-center justify-center"
                >
                  <XIcon class="w-4 h-4" />
                </button>
              </div>

              <!-- Upload Controls -->
              <div class="flex-1 w-full">
                <label class="block">
                  <span class="sr-only">Choisir une photo de profil</span>
                  <div class="flex items-center gap-3">
                    <input
                      type="file"
                      accept="image/*"
                      @change="handleAvatarUpload"
                      class="hidden"
                      ref="fileInput"
                    />
                    <button
                      type="button"
                      @click="$refs.fileInput.click()"
                      class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition flex items-center gap-2 border border-gray-300"
                    >
                      <UploadIcon class="w-4 h-4" />
                      Choisir une image
                    </button>
                  </div>
                </label>
                <p class="text-sm text-gray-500 mt-2">JPG, PNG ou GIF. Max 2MB.</p>
                
                <!-- Error for avatar -->
                <div v-if="avatarError" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                  <p class="text-sm text-red-700">{{ avatarError }}</p>
                </div>
                
                <!-- Save Avatar Button -->
                <button
                  v-if="avatarPreview"
                  type="submit"
                  :disabled="avatarLoading"
                  class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                  <LoaderIcon v-if="avatarLoading" class="w-4 h-4 animate-spin" />
                  <SaveIcon v-else class="w-4 h-4" />
                  <span>{{ avatarLoading ? 'Enregistrement...' : 'Enregistrer la photo' }}</span>
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Personal Information Section -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2">
            <UserIcon class="w-6 h-6 text-blue-600" />
            Informations personnelles
          </h2>

          <form @submit.prevent="updatePersonalInfo">
            <div class="grid md:grid-cols-2 gap-6">
              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                  Nom complet <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <UserIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                  <input
                    id="name"
                    v-model="formData.name"
                    type="text"
                    required
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="John Doe"
                  />
                </div>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                  Email <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <MailIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                  <input
                    id="email"
                    v-model="formData.email"
                    type="email"
                    readonly
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed text-gray-600"
                    placeholder="email@example.com"
                  />
                </div>
                <p class="text-xs text-gray-500 mt-1">L'email ne peut pas être modifié</p>
              </div>

              <!-- Phone -->
              <div class="md:col-span-2">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                  Téléphone
                </label>
                <div class="relative">
                  <PhoneIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                  <input
                    id="phone"
                    v-model="formData.phone"
                    type="tel"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="+33 6 12 34 56 78"
                  />
                </div>
              </div>
            </div>

            <!-- Error for personal info -->
            <div v-if="profileError" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
              <div class="flex gap-3">
                <AlertCircleIcon class="w-5 h-5 text-red-600 flex-shrink-0" />
                <p class="text-sm text-red-700">{{ profileError }}</p>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex gap-4">
              <button
                type="submit"
                :disabled="profileLoading"
                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-lg"
              >
                <LoaderIcon v-if="profileLoading" class="w-5 h-5 animate-spin" />
                <SaveIcon v-else class="w-5 h-5" />
                <span>{{ profileLoading ? 'Enregistrement...' : 'Enregistrer les informations' }}</span>
              </button>
              <button
                type="button"
                @click="resetPersonalInfo"
                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition"
              >
                Annuler
              </button>
            </div>
          </form>
        </div>

        <!-- Security Section -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900 mb-2 flex items-center gap-2">
            <LockIcon class="w-6 h-6 text-blue-600" />
            Modifier le mot de passe
          </h2>
          <p class="text-sm text-gray-600 mb-6">Changez votre mot de passe pour sécuriser votre compte</p>

          <form @submit.prevent="updatePassword">
            <div class="space-y-4">
              <!-- Current Password -->
              <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                  Mot de passe actuel <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <LockIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                  <input
                    id="current_password"
                    v-model="passwordData.current_password"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    required
                    class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    @click="showCurrentPassword = !showCurrentPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                  >
                    <EyeOffIcon v-if="showCurrentPassword" class="w-5 h-5" />
                    <EyeIcon v-else class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- New Password -->
              <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                  Nouveau mot de passe <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <LockIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                  <input
                    id="new_password"
                    v-model="passwordData.new_password"
                    :type="showNewPassword ? 'text' : 'password'"
                    required
                    class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    @click="showNewPassword = !showNewPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                  >
                    <EyeOffIcon v-if="showNewPassword" class="w-5 h-5" />
                    <EyeIcon v-else class="w-5 h-5" />
                  </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Minimum 8 caractères</p>
              </div>

              <!-- Confirm Password -->
              <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                  Confirmer le nouveau mot de passe <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <LockIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                  <input
                    id="confirm_password"
                    v-model="passwordData.confirm_password"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    required
                    class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                  >
                    <EyeOffIcon v-if="showConfirmPassword" class="w-5 h-5" />
                    <EyeIcon v-else class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Error for password -->
            <div v-if="passwordError" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
              <div class="flex gap-3">
                <AlertCircleIcon class="w-5 h-5 text-red-600 flex-shrink-0" />
                <p class="text-sm text-red-700">{{ passwordError }}</p>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex gap-4">
              <button
                type="submit"
                :disabled="passwordLoading"
                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-lg"
              >
                <LoaderIcon v-if="passwordLoading" class="w-5 h-5 animate-spin" />
                <LockIcon v-else class="w-5 h-5" />
                <span>{{ passwordLoading ? 'Modification...' : 'Modifier le mot de passe' }}</span>
              </button>
              <button
                type="button"
                @click="resetPassword"
                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition"
              >
                Annuler
              </button>
            </div>
          </form>
        </div>

      </div>

      <div v-else class="bg-white rounded-xl shadow-md p-12 text-center">
        <UserCircleIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <p class="text-gray-500">Utilisateur non trouvé.</p>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import { useAuthStore } from '@/stores/auth'
import authService from '@/services/authService'
import { 
  AlertCircleIcon, 
  LoaderIcon,
  CheckCircleIcon,
  UserCircleIcon,
  UserIcon,
  MailIcon,
  PhoneIcon,
  LockIcon,
  EyeIcon,
  EyeOffIcon,
  UploadIcon,
  XIcon,
  SaveIcon
} from 'lucide-vue-next'

const authStore = useAuthStore()

const loading = ref(false)
const error = ref<string | null>(null)

// Separate loading/error states for each section
const avatarLoading = ref(false)
const avatarError = ref<string | null>(null)
const profileLoading = ref(false)
const profileError = ref<string | null>(null)
const passwordLoading = ref(false)
const passwordError = ref<string | null>(null)

const successMessage = ref<string | null>(null)

const formData = ref({
  name: '',
  email: '',
  phone: '',
  avatar: ''
})

const passwordData = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const avatarPreview = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const userInitials = computed(() => {
  if (!authStore.user?.name) return 'U'
  const names = authStore.user.name.split(' ')
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase()
  }
  return names[0][0].toUpperCase()
})

onMounted(() => {
  if (authStore.user) {
    formData.value.name = authStore.user.name || ''
    formData.value.email = authStore.user.email || ''
    formData.value.phone = authStore.user.phone || ''
    formData.value.avatar = authStore.user.avatar || ''
  }
})

watch(() => authStore.user, (newUser) => {
  if (newUser) {
    formData.value.name = newUser.name || ''
    formData.value.email = newUser.email || ''
    formData.value.phone = newUser.phone || ''
    formData.value.avatar = newUser.avatar || ''
  }
}, { immediate: true })

function handleAvatarUpload(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    // Check file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
      profileError.value = 'L\'image ne doit pas dépasser 2MB'
      return
    }
    
    // Check file type
    if (!file.type.startsWith('image/')) {
      profileError.value = 'Veuillez sélectionner une image valide'
      return
    }
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

function removeAvatar() {
  avatarPreview.value = null
  formData.value.avatar = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Avatar Section Functions
async function updateAvatar() {
  avatarLoading.value = true
  avatarError.value = null
  successMessage.value = null

  try {
    if (!avatarPreview.value) {
      avatarError.value = 'Aucune image sélectionnée'
      return
    }

    const updateData = { avatar: avatarPreview.value }
    await authStore.updateUserProfile(updateData)

    successMessage.value = 'Photo de profil mise à jour avec succès!'
    avatarPreview.value = null
    
    setTimeout(() => {
      successMessage.value = null
    }, 5000)
  } catch (err: any) {
    avatarError.value = err.response?.data?.message || 'Échec de la mise à jour de la photo'
  } finally {
    avatarLoading.value = false
  }
}

// Personal Info Section Functions
async function updatePersonalInfo() {
  profileLoading.value = true
  profileError.value = null
  successMessage.value = null

  try {
    if (!authStore.user) return

    const updateData: any = {}
    
    if (formData.value.name !== (authStore.user.name || '')) {
      updateData.name = formData.value.name
    }
    
    if (formData.value.phone !== (authStore.user.phone || '')) {
      updateData.phone = formData.value.phone
    }
    
    if (Object.keys(updateData).length === 0) {
      profileError.value = 'Aucune modification détectée'
      return
    }
    
    await authStore.updateUserProfile(updateData)

    successMessage.value = 'Informations personnelles mises à jour avec succès!'
    
    setTimeout(() => {
      successMessage.value = null
    }, 5000)
  } catch (err: any) {
    profileError.value = err.response?.data?.message || 'Échec de la mise à jour du profil'
  } finally {
    profileLoading.value = false
  }
}

function resetPersonalInfo() {
  if (authStore.user) {
    formData.value.name = authStore.user.name || ''
    formData.value.phone = authStore.user.phone || ''
  }
  profileError.value = null
}

// Password Section Functions
async function updatePassword() {
  passwordLoading.value = true
  passwordError.value = null
  successMessage.value = null

  try {
    // Validation
    if (!passwordData.value.current_password) {
      passwordError.value = 'Veuillez entrer votre mot de passe actuel'
      return
    }
    
    if (!passwordData.value.new_password) {
      passwordError.value = 'Veuillez entrer un nouveau mot de passe'
      return
    }
    
    if (passwordData.value.new_password.length < 8) {
      passwordError.value = 'Le mot de passe doit contenir au moins 8 caractères'
      return
    }
    
    if (passwordData.value.new_password !== passwordData.value.confirm_password) {
      passwordError.value = 'Les mots de passe ne correspondent pas'
      return
    }
    
    // Call the backend endpoint
    await authService.changePassword(
      passwordData.value.current_password,
      passwordData.value.new_password
    )
    
    // Clear password fields on success
    passwordData.value = { current_password: '', new_password: '', confirm_password: '' }
    
    successMessage.value = 'Mot de passe modifié avec succès!'
    
    setTimeout(() => {
      successMessage.value = null
    }, 5000)
  } catch (err: any) {
    passwordError.value = err.response?.data?.message || 'Échec de la modification du mot de passe'
  } finally {
    passwordLoading.value = false
  }
}

function resetPassword() {
  passwordData.value = {
    current_password: '',
    new_password: '',
    confirm_password: ''
  }
  passwordError.value = null
}
</script>

<style scoped>
/* Scoped styles for this component */
</style>
