import api from './api'
import type {
  DashboardStats,
  OrganizerDashboard,
  ScannerDashboard,
  SalesReport,
  ScanActivityReport
} from '@/types/api'

class DashboardService {
  /**
   * Get super admin dashboard stats
   */
  async getSuperAdminStats(): Promise<DashboardStats> {
    const response = await api.get<DashboardStats>('/dashboard/super-admin')
    return response.data
  }

  /**
   * Get organizer dashboard stats
   */
  async getOrganizerStats(): Promise<OrganizerDashboard> {
    const response = await api.get<{ data: OrganizerDashboard }>('/dashboard/organizer')
    return response.data.data
  }

  /**
   * Get scanner dashboard stats
   */
  async getScannerStats(): Promise<ScannerDashboard> {
    const response = await api.get<{ data: ScannerDashboard }>('/dashboard/scanner')
    return response.data.data
  }

  /**
   * Get analytics for date range
   */
  async getAnalytics(startDate: string, endDate: string): Promise<any> {
    const response = await api.get('/dashboard/analytics', {
      params: { start_date: startDate, end_date: endDate }
    })
    return response.data.data
  }

  /**
   * Get sales report data
   */
  async getSalesData(): Promise<SalesReport> {
    const response = await api.get<{ data: SalesReport }>('/reports/sales')
    return response.data.data
  }

  /**
   * Get scan activity data
   */
  async getScanActivityData(): Promise<ScanActivityReport> {
    const response = await api.get<{ data: ScanActivityReport }>('/reports/scans')
    return response.data.data
  }
}

export default new DashboardService()
