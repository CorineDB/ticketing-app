import { ref } from 'vue'
import scanService from '@/services/scanService'
import type { Scan, ScanTicketData, ScanResponse, ScanFilters, PaginatedResponse, ScanRequestData, ScanConfirmData } from '@/types/api'

export function useScanner() {
  const scans = ref<Scan[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const lastScanResult = ref<ScanResponse | null>(null)

  const scanTicket = async (data: ScanTicketData) => {
    loading.value = true
    error.value = null
    try {
      const result = await scanService.scanTicket(data)
      lastScanResult.value = result
      return result
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to scan ticket'
      console.error('Error scanning ticket:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const requestScan = async (data: ScanRequestData) => {
    loading.value = true
    error.value = null
    try {
      const result = await scanService.requestScan(data)
      return result
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to request scan'
      console.error('Error requesting scan:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const confirmScan = async (data: ScanConfirmData) => {
    loading.value = true
    error.value = null
    try {
      const result = await scanService.confirmScan(data)
      lastScanResult.value = result
      return result
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to confirm scan'
      console.error('Error confirming scan:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const performTwoStepScan = async (
    requestData: ScanRequestData,
    confirmData?: Partial<Omit<ScanConfirmData, 'session_token'>>
  ) => {
    loading.value = true
    error.value = null
    try {
      const result = await scanService.performTwoStepScan(requestData, confirmData)
      lastScanResult.value = result
      return result
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to perform scan'
      console.error('Error performing scan:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const fetchScans = async (filters?: ScanFilters) => {
    loading.value = true
    error.value = null
    try {
      const response: PaginatedResponse<Scan> = await scanService.getAll(filters)
      scans.value = response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch scans'
      console.error('Error fetching scans:', e)
    } finally {
      loading.value = false
    }
  }

  const parseQRCode = (qrData: string): ScanRequestData | null => {
    try {
      console.log("Parsing QR data:", qrData)
      // Try parsing as JSON first (Standard format for this app)
      const data = JSON.parse(qrData)
      
      // Check if it matches ScanRequestData structure (partially)
      if (data.ticket_id && data.event_id && data.qr_hmac) {
        return {
          ticket_id: data.ticket_id,
          event_id: data.event_id,
          qr_hmac: data.qr_hmac,
          scan_type: 'entry', // Default
          device_info: navigator.userAgent
        }
      } else if (data.code) {
         // Handle potential legacy or simplified format if needed
         // But 2-step scan requires hmac. 
         console.warn("QR code missing required fields for 2-step scan")
      }
    } catch (e) {
      console.error("Failed to parse QR code:", e)
    }
    return null
  }

  return {
    scanHistory: scans,
    scans,
    loading,
    error,
    lastScanResult,
    scanTicket,
    requestScan,
    confirmScan,
    performTwoStepScan,
    fetchScans,
    fetchMyScans,
    parseQRCode
  }
}
