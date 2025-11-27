import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function usePermissions() {
  const authStore = useAuthStore()

  const can = (permission: string): boolean => {
    if (!authStore.user) return false
    
    // Super admin has all permissions
    if (authStore.isSuperAdmin) return true
    
    // Check user permissions
    return authStore.user.permissions?.some(p => p.slug === permission) || false
  }

  const cannot = (permission: string): boolean => {
    return !can(permission)
  }

  const hasRole = (role: string): boolean => {
    if (!authStore.user?.role) return false
    return authStore.user.role.slug === role
  }

  const hasAnyRole = (roles: string[]): boolean => {
    if (!authStore.user?.role) return false
    return roles.includes(authStore.user.role.slug)
  }

  const hasAllPermissions = (permissions: string[]): boolean => {
    return permissions.every(permission => can(permission))
  }

  const hasAnyPermission = (permissions: string[]): boolean => {
    return permissions.some(permission => can(permission))
  }

  const isAuthenticated = computed(() => authStore.isAuthenticated)
  const isSuperAdmin = computed(() => authStore.isSuperAdmin)
  const isOrganizer = computed(() => authStore.isOrganizer)
  const isScanner = computed(() => authStore.isScanner)
  const isCashier = computed(() => authStore.isCashier)
  const isParticipant = computed(() => authStore.isParticipant)

  return {
    can,
    cannot,
    hasRole,
    hasAnyRole,
    hasAllPermissions,
    hasAnyPermission,
    isAuthenticated,
    isSuperAdmin,
    isOrganizer,
    isScanner,
    isCashier,
    isParticipant
  }
}
