import { ref } from 'vue'
import scanService from '@/services/scanService'
import type { Scan, ScanFilters } from '@/types/api'

export function useScans() {
  const scans = ref<Scan[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1
  })

  const fetchScans = async (filters?: ScanFilters) => {
    loading.value = true
    error.value = null
    try {
      const response = await scanService.getAll(filters)

      // Handle Laravel pagination format
      if (response && response.data) {
        scans.value = response.data
        pagination.value = {
          total: response.total || 0,
          per_page: response.per_page || 15,
          current_page: response.current_page || 1,
          last_page: response.last_page || 1
        }
      } else {
        // Fallback if response is just an array
        scans.value = Array.isArray(response) ? response : []
        pagination.value = {
          total: scans.value.length,
          per_page: 15,
          current_page: 1,
          last_page: 1
        }
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch scans'
      console.error('Error fetching scans:', e)
      scans.value = []
    } finally {
      loading.value = false
    }
  }

  const exportScans = async (filters?: ScanFilters) => {
    await scanService.exportScans(filters)
  }

  return {
    scans,
    loading,
    error,
    pagination,
    fetchScans,
    exportScans
  }
}
