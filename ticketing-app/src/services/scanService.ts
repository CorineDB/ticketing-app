import api from './api'
import type {
  Scan,
  ScanTicketData,
  ScanResponse,
  ScanFilters,
  PaginatedResponse,
  ScanRequestData,
  ScanSessionResponse,
  ScanConfirmData
} from '@/types/api'

class ScanService {
  // ==================== 2-STEP SCAN WORKFLOW ====================

  /**
   * Step 1: Request scan session (PUBLIC endpoint, no auth required)
   * Validates HMAC and returns temporary session token
   *
   * @param data - Scan request with ticket_id, event_id, qr_hmac
   * @returns Session token with 30-second TTL
   */
  async requestScan(data: ScanRequestData): Promise<ScanSessionResponse> {
    const response = await api.post<ScanSessionResponse>('/scan/request', data)
    return response.data
  }

  /**
   * Step 2: Confirm scan (AUTHENTICATED endpoint)
   * Executes the actual scan using session token
   *
   * @param data - Confirmation with session_token
   * @returns Final scan result
   */
  async confirmScan(data: ScanConfirmData): Promise<ScanResponse> {
    const response = await api.post<ScanResponse>('/scan/confirm', data)
    return response.data
  }

  /**
   * Complete 2-step scan workflow helper
   * Combines both steps into one method for convenience
   *
   * @param requestData - Initial scan request data
   * @param confirmData - Additional data for confirmation (gate_id, location, notes)
   * @returns Final scan result
   */
  async performTwoStepScan(
    requestData: ScanRequestData,
    confirmData?: Partial<Omit<ScanConfirmData, 'session_token'>>
  ): Promise<ScanResponse> {
    // Step 1: Request scan session
    const sessionResponse = await this.requestScan(requestData)

    if (!sessionResponse.success) {
      throw new Error(sessionResponse.message || 'Failed to request scan session')
    }

    // Step 2: Confirm scan with session token
    const finalResponse = await this.confirmScan({
      session_token: sessionResponse.session_token,
      gate_id: confirmData?.gate_id,
      location: confirmData?.location,
      notes: confirmData?.notes
    })

    return finalResponse
  }

  // ==================== LEGACY SINGLE-STEP SCAN ====================

  /**
   * Scan a ticket (entry/exit) - Legacy single-step method
   * @deprecated Use performTwoStepScan for better security
   */
  async scanTicket(data: ScanTicketData): Promise<ScanResponse> {
    const response = await api.post<ScanResponse>('/scans', data)
    return response.data
  }

  /**
   * Get all scans with optional filters
   */
  async getAll(filters?: ScanFilters): Promise<PaginatedResponse<Scan>> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<PaginatedResponse<Scan>>('/scans', { params })
    return response.data
  }

  /**
   * Get scan history for a specific ticket
   */
  async getTicketScans(ticketId: number): Promise<Scan[]> {
    const response = await api.get<{ data: Scan[] }>(`/tickets/${ticketId}/scans`)
    return response.data.data
  }

  /**
   * Get my scans (for scanner role)
   */
  async getMyScans(filters?: ScanFilters): Promise<PaginatedResponse<Scan>> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<PaginatedResponse<Scan>>('/scans/my-scans', { params })
    return response.data
  }

  /**
   * Get event scans summary
   */
  async getEventSummary(eventId: number): Promise<{
    total_scans: number
    entries: number
    exits: number
    current_attendance: number
    valid_scans: number
    invalid_scans: number
  }> {
    const response = await api.get(`/events/${eventId}/scans/summary`)
    return response.data.data
  }

  /**
   * Export scans to CSV
   */
  async exportToCSV(eventId: number, filters?: ScanFilters): Promise<Blob> {
    const params = this.buildQueryParams(filters)
    const response = await api.get(`/events/${eventId}/scans/export`, {
      params,
      responseType: 'blob'
    })
    return response.data
  }

  /**
   * Build query parameters from filters
   */
  private buildQueryParams(filters?: ScanFilters): Record<string, any> {
    if (!filters) return {}

    const params: Record<string, any> = {}

    if (filters.event_id) params.event_id = filters.event_id
    if (filters.gate_id) params.gate_id = filters.gate_id
    if (filters.scanner_id) params.scanner_id = filters.scanner_id
    if (filters.scan_type) params.scan_type = filters.scan_type
    if (filters.result) params.result = filters.result
    if (filters.date) params.date = filters.date

    return params
  }
}

export default new ScanService()
