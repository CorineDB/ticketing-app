import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { PERMISSIONS } from '@/constants/permissions'
import type { UserType } from '@/types/api'
import type { Permission } from '@/constants/permissions'

export function usePermissions() {
  const authStore = useAuthStore()

  /**
   * Check if user has a specific permission
   */
  const can = (permissionSlug: Permission | string): boolean => {
    if (!authStore.user) return false

    // Super admin has all permissions
    if (authStore.isSuperAdmin) return true

    // Check if user has the permission directly
    if (authStore.user.permissions) {
      return authStore.user.permissions.some((p) => p.slug === permissionSlug)
    }

    // Check if user's role has the permission
    if (authStore.user.role?.permissions) {
      return authStore.user.role.permissions.some((p) => p.slug === permissionSlug)
    }

    return false
  }

  /**
   * Check if user does NOT have a specific permission
   */
  const cannot = (permissionSlug: Permission | string): boolean => {
    return !can(permissionSlug)
  }

  /**
   * Check if user has a specific role
   */
  const hasRole = (roleSlug: string): boolean => {
    if (!authStore.user) return false
    return authStore.user.role?.slug === roleSlug
  }

  /**
   * Check if user has a specific type
   */
  const hasUserType = (type: UserType): boolean => {
    if (!authStore.user) return false
    return authStore.user.type === type
  }

  /**
   * Check if user has any of the specified roles
   */
  const hasAnyRole = (roleSlugs: string[]): boolean => {
    if (!authStore.user) return false
    return roleSlugs.some((slug) => authStore.user?.role?.slug === slug)
  }

  /**
   * Check if user has any of the specified permissions
   */
  const hasAnyPermission = (permissionSlugs: (Permission | string)[]): boolean => {
    if (!authStore.user) return false
    if (authStore.isSuperAdmin) return true
    return permissionSlugs.some((slug) => can(slug))
  }

  /**
   * Check if user has all of the specified permissions
   */
  const hasAllPermissions = (permissionSlugs: (Permission | string)[]): boolean => {
    if (!authStore.user) return false
    if (authStore.isSuperAdmin) return true
    return permissionSlugs.every((slug) => can(slug))
  }

  // Computed permission helpers (using exact backend permissions)

  // Users
  const canViewUsers = computed(() => can(PERMISSIONS.VIEW_USERS))
  const canManageUsers = computed(() => can(PERMISSIONS.MANAGE_USERS))
  const canAssignRoles = computed(() => can(PERMISSIONS.ASSIGN_ROLES))

  // Roles
  const canViewRoles = computed(() => can(PERMISSIONS.VIEW_ROLES))
  const canManageRoles = computed(() => can(PERMISSIONS.MANAGE_ROLES))

  // Permissions
  const canViewPermissions = computed(() => can(PERMISSIONS.VIEW_PERMISSIONS))
  const canManagePermissions = computed(() => can(PERMISSIONS.MANAGE_PERMISSIONS))

  // Events
  const canViewEvents = computed(() => can(PERMISSIONS.VIEW_EVENTS))
  const canCreateEvents = computed(() => can(PERMISSIONS.CREATE_EVENTS))
  const canUpdateEvents = computed(() => can(PERMISSIONS.UPDATE_EVENTS))
  const canDeleteEvents = computed(() => can(PERMISSIONS.DELETE_EVENTS))
  const canManageEventTicketTypes = computed(() => can(PERMISSIONS.MANAGE_EVENT_TICKET_TYPES))
  const canViewEventStats = computed(() => can(PERMISSIONS.VIEW_EVENT_STATS))

  // Tickets
  const canViewTickets = computed(() => can(PERMISSIONS.VIEW_TICKETS))
  const canCreateTickets = computed(() => can(PERMISSIONS.CREATE_TICKETS))
  const canUpdateTickets = computed(() => can(PERMISSIONS.UPDATE_TICKETS))
  const canDeleteTickets = computed(() => can(PERMISSIONS.DELETE_TICKETS))
  const canMarkTicketsPaid = computed(() => can(PERMISSIONS.MARK_TICKETS_PAID))
  const canGenerateQRCodes = computed(() => can(PERMISSIONS.GENERATE_QR_CODES))

  // Gates
  const canViewGates = computed(() => can(PERMISSIONS.VIEW_GATES))
  const canManageGates = computed(() => can(PERMISSIONS.MANAGE_GATES))

  // Scans
  const canPerformScan = computed(() => can(PERMISSIONS.PERFORM_SCAN))
  const canViewScanLogs = computed(() => can(PERMISSIONS.VIEW_SCAN_LOGS))

  // Webhooks
  const canManageWebhooks = computed(() => can(PERMISSIONS.MANAGE_WEBHOOKS))

  return {
    // Core methods
    can,
    cannot,
    hasRole,
    hasUserType,
    hasAnyRole,
    hasAnyPermission,
    hasAllPermissions,

    // Users
    canViewUsers,
    canManageUsers,
    canAssignRoles,

    // Roles
    canViewRoles,
    canManageRoles,

    // Permissions
    canViewPermissions,
    canManagePermissions,

    // Events
    canViewEvents,
    canCreateEvents,
    canUpdateEvents,
    canDeleteEvents,
    canManageEventTicketTypes,
    canViewEventStats,

    // Tickets
    canViewTickets,
    canCreateTickets,
    canUpdateTickets,
    canDeleteTickets,
    canMarkTicketsPaid,
    canGenerateQRCodes,

    // Gates
    canViewGates,
    canManageGates,

    // Scans
    canPerformScan,
    canViewScanLogs,

    // Webhooks
    canManageWebhooks
  }
}
