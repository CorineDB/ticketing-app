// src/stores/auth.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { User, UserRole } from '@/types/User';
import { apiService } from '@/services/apiService';

export const useAuthStore = defineStore('auth', () => {
  const currentUser = ref<User | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const isLoggedIn = computed(() => !!currentUser.value);
  const userRole = computed((): UserRole | null => currentUser.value?.role ?? null);
  const superAdmin = computed(() => userRole.value === 'super_admin');
  const organizer = computed(() => userRole.value === 'organizer');
  const controller = computed(() => userRole.value === 'controller');
  const cashier = computed(() => userRole.value === 'cashier');


  async function login(email: string) {
    isLoading.value = true;
    error.value = null;
    try {
      // In a real app, you'd also pass a password
      const user = await apiService.login(email);
      if (user) {
        currentUser.value = user;
      } else {
        throw new Error('User not found or credentials incorrect.');
      }
    } catch (e: any) {
      error.value = e.message;
      currentUser.value = null; // Ensure user is null on failed login
    } finally {
      isLoading.value = false;
    }
  }

  function logout() {
    currentUser.value = null;
  }
  
  // This would be used in a real app to fetch user data on app load if a token exists
  async function fetchUser() {
    // For now, we don't have token persistence, so this is just a placeholder
    // Example: const user = await apiService.getMe(token);
    // currentUser.value = user;
  }

  return {
    currentUser,
    isLoading,
    error,
    isLoggedIn,
    userRole,
    superAdmin,
    organizer,
    controller,
    cashier,
    login,
    logout,
    fetchUser
  };
});
