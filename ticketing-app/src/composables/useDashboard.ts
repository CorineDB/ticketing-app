import { ref } from 'vue'
import dashboardService from '@/services/dashboardService'
import type { DashboardStats, OrganizerDashboard, ScannerDashboard, SalesReport, ScanActivityReport } from '@/types/api'

export function useDashboard() {
  const stats = ref<DashboardStats | OrganizerDashboard | ScannerDashboard | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const salesData = ref<SalesReport | null>(null)
  const loadingSales = ref(false)
  const errorSales = ref<string | null>(null)

  const scanActivityData = ref<ScanActivityReport | null>(null)
  const loadingScans = ref(false)
  const errorScans = ref<string | null>(null)

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

  const fetchSalesData = async () => {
    loadingSales.value = true
    errorSales.value = null
    try {
      salesData.value = await dashboardService.getSalesData()
    } catch (e: any) {
      errorSales.value = e.response?.data?.message || 'Failed to fetch sales data'
      console.error('Error fetching sales data:', e)
    } finally {
      loadingSales.value = false
    }
  }

  const fetchScanActivityData = async () => {
    loadingScans.value = true
    errorScans.value = null
    try {
      scanActivityData.value = await dashboardService.getScanActivityData()
    } catch (e: any) {
      errorScans.value = e.response?.data?.message || 'Failed to fetch scan activity data'
      console.error('Error fetching scan activity data:', e)
    } finally {
      loadingScans.value = false
    }
  }

  return {
    stats,
    loading,
    error,
    salesData,
    loadingSales,
    errorSales,
    scanActivityData,
    loadingScans,
    errorScans,
    fetchSuperAdminStats,
    fetchOrganizerStats,
    fetchScannerStats,
    fetchAnalytics,
    fetchSalesData,
    fetchScanActivityData
  }
}
