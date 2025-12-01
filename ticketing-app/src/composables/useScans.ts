import { ref } from 'vue'
import scanService from '@/services/scanService'
import type { Scan, ScanFilters, PaginatedResponse } from '@/types/api'

export function useScans() {
  const scans = ref<Scan[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1
  })

  const fetchScans = async (filters?: ScanFilters) => {
    loading.value = true
    error.value = null
    try {
      const response: PaginatedResponse<Scan> = await scanService.getAll(filters)
      scans.value = response.data
      pagination.value = {
        total: response.total,
        per_page: response.per_page,
        current_page: response.current_page,
        last_page: response.last_page
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch scans'
      console.error('Error fetching scans:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchTicketScans = async (ticketId: string) => {
    loading.value = true
    error.value = null
    try {
      scans.value = await scanService.getTicketScans(ticketId)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch ticket scans'
      console.error('Error fetching ticket scans:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchEventSummary = async (eventId: string) => {
    loading.value = true
    error.value = null
    try {
      return await scanService.getEventSummary(eventId)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch event summary'
      console.error('Error fetching event summary:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const exportToCSV = async (eventId: string, filters?: ScanFilters) => {
    loading.value = true
    error.value = null
    try {
      const blob = await scanService.exportToCSV(eventId, filters)
      // Create download link
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `event_${eventId}_scans.csv`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to export scans'
      console.error('Error exporting scans:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    scans,
    loading,
    error,
    pagination,
    fetchScans,
    fetchTicketScans,
    fetchEventSummary,
    exportToCSV: exportToCSV,//exportScansData
  }
}
