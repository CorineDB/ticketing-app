import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authService from '@/services/authService'
import type { User, LoginCredentials, OTPRequest, OTPVerification } from '@/types/api'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const otpRequested = ref(false)
  const phoneNumber = ref('')

  // Getters
  const isAuthenticated = computed(() => !!user.value)/* 
  const isSuperAdmin = computed(() => user.value?.type === 'super-admin')
  const isOrganizer = computed(() => user.value?.type === 'organizer')
  const isScanner = computed(() => user.value?.type === 'agent-de-controle')
  const isCashier = computed(() => user.value?.type === 'comptable')
  const isParticipant = computed(() => user.value?.type === 'participant') */
  const isSuperAdmin = computed(() => user.value?.role.slug === 'super-admin')
  const isOrganizer = computed(() => user.value?.role.slug === 'organizer')
  const isScanner = computed(() => user.value?.role.slug === 'agent-de-controle')
  const isCashier = computed(() => user.value?.role.slug === 'comptable')
  const isParticipant = computed(() => user.value?.role.slug === 'participant')

  // Actions
  async function login(credentials: LoginCredentials): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const response = await authService.login(credentials);

      localStorage.setItem("auth_token", response.access_token);

      const fetchedUser = await fetchUser();

      return !!fetchedUser;
    } catch (e: any) {
      error.value = e.response?.data?.message || "Login failed";
      localStorage.removeItem("auth_token");
      localStorage.removeItem("auth_user");
      user.value = null;
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function requestOtp(data: OTPRequest) {
    loading.value = true
    error.value = null

    try {
      await authService.requestOtp(data)
      otpRequested.value = true
      phoneNumber.value = data.phone
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to send OTP'
      return false
    } finally {
      loading.value = false
    }
  }

  async function verifyOtp(data: OTPVerification) {
    loading.value = true
    error.value = null

    try {
      const response = await authService.verifyOtp(data)

      // Store token
      localStorage.setItem('auth_token', response.access_token)
      localStorage.setItem('auth_user', JSON.stringify(response.user))

      user.value = response.user
      otpRequested.value = false

      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Invalid OTP'
      return false
    } finally {
      loading.value = false
    }
  }

  async function fetchUser(): Promise<User | null> {
    loading.value = true
    error.value = null

    try {
      const userData = await authService.me()
      user.value = userData
      console.log(userData);
      localStorage.setItem('auth_user', JSON.stringify(userData))
      return userData
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch user'
      // If unauthorized, logout
      // if (e.response?.status === 401) {
      //   logout()
      // }
      return null
    } finally {
      loading.value = false
    }
  }

  async function changePassword(oldPassword: string, newPassword: string) {
    loading.value = true
    error.value = null

    try {
      await authService.changePassword(oldPassword, newPassword)
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to change password'
      return false
    } finally {
      loading.value = false
    }
  }

  async function updateUserProfile(data: Partial<User>): Promise<User | boolean> {
    loading.value = true;
    error.value = null;

    try {
      const updatedUser = await authService.updateProfile(data);
      user.value = updatedUser;
      localStorage.setItem('auth_user', JSON.stringify(updatedUser)); // Update local storage
      return updatedUser;
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update profile';
      console.error('Error updating user profile:', e);
      return false;
    } finally {
      loading.value = false;
    }
  }

  function logout() {
    // Clear local storage
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')

    // Reset state
    user.value = null
    error.value = null
    otpRequested.value = false
    phoneNumber.value = ''

    // Call API logout (fire and forget)
    authService.logout().catch(() => {
      // Ignore errors on logout
    })
  }

  function initializeFromStorage() {
    const token = localStorage.getItem('auth_token')
    const storedUser = localStorage.getItem('auth_user')

    if (token && storedUser) {
      try {
        user.value = JSON.parse(storedUser)
        // Optionally fetch fresh user data
        fetchUser()
      } catch (e) {
        // If parsing fails, clear storage
        logout()
      }
    }
  }

  // Initialize store from localStorage
  initializeFromStorage()

  return {
    // State
    user,
    loading,
    error,
    otpRequested,
    phoneNumber,

    // Getters
    isAuthenticated,
    isSuperAdmin,
    isOrganizer,
    isScanner,
    isCashier,
    isParticipant,

    // Actions
    login,
    requestOtp,
    verifyOtp,
    fetchUser,
    changePassword,
    updateUserProfile,
    logout,
    initializeFromStorage
  }
})
