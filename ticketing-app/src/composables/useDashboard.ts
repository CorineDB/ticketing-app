import { ref } from 'vue'
import dashboardService from '@/services/dashboardService'
import type { DashboardStats, OrganizerDashboard, ScannerDashboard } from '@/types/api'

export function useDashboard() {
  const stats = ref<DashboardStats | OrganizerDashboard | ScannerDashboard | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchSuperAdminStats = async () => {
    loading.value = true
    error.value = null
    try {
      stats.value = await dashboardService.getSuperAdminStats()
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch dashboard stats'
      console.error('Error fetching dashboard stats:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchOrganizerStats = async () => {
    loading.value = true
    error.value = null
    try {
      stats.value = await dashboardService.getOrganizerStats()
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch organizer stats'
      console.error('Error fetching organizer stats:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchScannerStats = async () => {
    loading.value = true
    error.value = null
    try {
      stats.value = await dashboardService.getScannerStats()
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch scanner stats'
      console.error('Error fetching scanner stats:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchAnalytics = async (startDate: string, endDate: string) => {
    loading.value = true
    error.value = null
    try {
      return await dashboardService.getAnalytics(startDate, endDate)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch analytics'
      console.error('Error fetching analytics:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    stats,
    loading,
    error,
    fetchSuperAdminStats,
    fetchOrganizerStats,
    fetchScannerStats,
    fetchAnalytics
  }
}
