import { ref, readonly } from 'vue'
import scanService from '@/services/scanService'
import type {
  Scan,
  ScanTicketData,
  ScanResponse,
  ScanRequestData,
  ScanSessionResponse,
  ScanConfirmData
} from '@/types/api'
import { useNotificationStore } from '@/stores/notifications'

export function useScanner() {
  const notifications = useNotificationStore()

  const scans = ref<Scan[]>([])
  const lastScanResult = ref<ScanResponse | null>(null)
  const lastSessionResponse = ref<ScanSessionResponse | null>(null)
  const loading = ref(false)
  const scanning = ref(false)
  const error = ref<string | null>(null)

  async function scanTicket(data: ScanTicketData): Promise<ScanResponse | null> {
    scanning.value = true
    error.value = null

    try {
      const result = await scanService.scanTicket(data)
      lastScanResult.value = result

      if (result.success) {
        if (result.result === 'valid') {
          notifications.success('Valid Ticket', result.message)
        } else if (result.result === 'already_used') {
          notifications.warning('Already Used', result.message)
        }

        // Add scan to local list if successful
        if (result.scan) {
          scans.value.unshift(result.scan)
        }
      } else {
        notifications.error('Invalid Ticket', result.message)
      }

      return result
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to scan ticket'
      notifications.error('Scan Error', error.value)
      return null
    } finally {
      scanning.value = false
    }
  }

  async function fetchMyScans(filters?: any) {
    loading.value = true
    error.value = null

    try {
      const response = await scanService.getMyScans(filters)
      scans.value = response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch scans'
      notifications.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  async function fetchEventScans(eventId: number, filters?: any) {
    loading.value = true
    error.value = null

    try {
      const response = await scanService.getAll({ ...filters, event_id: eventId })
      scans.value = response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch event scans'
      notifications.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  async function fetchEventSummary(eventId: number) {
    loading.value = true
    error.value = null

    try {
      const summary = await scanService.getEventSummary(eventId)
      return summary
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch summary'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  // ==================== 2-STEP SCAN WORKFLOW ====================

  /**
   * Step 1: Request scan session (validates HMAC)
   */
  async function requestScan(data: ScanRequestData): Promise<ScanSessionResponse | null> {
    scanning.value = true
    error.value = null

    try {
      const sessionResponse = await scanService.requestScan(data)
      lastSessionResponse.value = sessionResponse

      if (sessionResponse.success) {
        notifications.info(
          'Scan Ready',
          `Ticket holder: ${sessionResponse.ticket_preview?.holder_name || 'Unknown'}`
        )
      } else {
        notifications.error('Invalid QR Code', sessionResponse.message || 'Failed to validate ticket')
      }

      return sessionResponse
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to request scan'
      notifications.error('Scan Error', error.value)
      return null
    } finally {
      scanning.value = false
    }
  }

  /**
   * Step 2: Confirm scan (executes the scan)
   */
  async function confirmScan(data: ScanConfirmData): Promise<ScanResponse | null> {
    scanning.value = true
    error.value = null

    try {
      const result = await scanService.confirmScan(data)
      lastScanResult.value = result

      if (result.success) {
        if (result.result === 'valid') {
          notifications.success('Valid Ticket', result.message)

          // Show event status if available
          if (result.event_status) {
            const { current_in, capacity, remaining } = result.event_status
            notifications.info(
              'Event Status',
              `${current_in}/${capacity} inside • ${remaining} remaining`
            )
          }
        } else if (result.result === 'already_used') {
          notifications.warning('Already Used', result.message)
        } else if (result.result === 'capacity_full') {
          notifications.error('Capacity Full', result.message)
        }

        // Add scan to local list if successful
        if (result.scan) {
          scans.value.unshift(result.scan)
        }
      } else {
        notifications.error('Invalid Ticket', result.message)
      }

      // Clear session response after confirmation
      lastSessionResponse.value = null

      return result
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to confirm scan'
      notifications.error('Scan Error', error.value)
      return null
    } finally {
      scanning.value = false
    }
  }

  /**
   * Complete 2-step scan workflow
   * Combines request and confirm into one method
   */
  async function performTwoStepScan(
    requestData: ScanRequestData,
    confirmData?: Partial<Omit<ScanConfirmData, 'session_token'>>
  ): Promise<ScanResponse | null> {
    // Step 1: Request scan session
    const sessionResponse = await requestScan(requestData)

    if (!sessionResponse || !sessionResponse.success) {
      return null
    }

    // Step 2: Confirm scan
    return await confirmScan({
      session_token: sessionResponse.session_token,
      gate_id: confirmData?.gate_id,
      location: confirmData?.location,
      notes: confirmData?.notes
    })
  }

  /**
   * Parse QR code data
   * Expected format: "ticket_id|event_id|qr_hmac"
   */
  function parseQRCode(qrData: string): ScanRequestData | null {
    try {
      const parts = qrData.split('|')
      if (parts.length !== 3) {
        throw new Error('Invalid QR code format')
      }

      return {
        ticket_id: parseInt(parts[0]),
        event_id: parseInt(parts[1]),
        qr_hmac: parts[2],
        scan_type: 'entry' // Default, can be overridden
      }
    } catch (e: any) {
      error.value = 'Invalid QR code format'
      notifications.error('Invalid QR Code', error.value)
      return null
    }
  }

  // ==================== UTILITY FUNCTIONS ====================

  function clearLastResult() {
    lastScanResult.value = null
    lastSessionResponse.value = null
  }

  function clearScans() {
    scans.value = []
  }

  return {
    scans: readonly(scans),
    lastScanResult: readonly(lastScanResult),
    lastSessionResponse: readonly(lastSessionResponse),
    loading: readonly(loading),
    scanning: readonly(scanning),
    error: readonly(error),

    // Legacy single-step scan
    scanTicket,

    // 2-step scan workflow
    requestScan,
    confirmScan,
    performTwoStepScan,
    parseQRCode,

    // Data fetching
    fetchMyScans,
    fetchEventScans,
    fetchEventSummary,

    // Utilities
    clearLastResult,
    clearScans
  }
}
