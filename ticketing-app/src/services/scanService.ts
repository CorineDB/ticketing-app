import api from './api'
import type {
  ScanRequestPayload,
  ScanRequestResponse,
  ScanConfirmPayload,
  ScanConfirmResponse
} from '@/types/scan'

export const scanService = {
  /**
   * Niveau 1: Validation du QR code (public)
   * Vérifie la signature HMAC et récupère les infos du ticket + session token
   */
  async scanRequest(payload: ScanRequestPayload): Promise<ScanRequestResponse> {
    const response = await api.post<ScanRequestResponse>('/scan/request', payload)
    return response.data
  },

  /**
   * Niveau 2: Confirmation de l'entrée (authentifié)
   * L'agent valide l'entrée après vérification visuelle
   */
  async scanConfirm(payload: ScanConfirmPayload): Promise<ScanConfirmResponse> {
    const response = await api.post<ScanConfirmResponse>('/scans/confirm', payload)
    return response.data
  },

  /**
   * Get scan history with filters
   */
  async getAll(filters?: any): Promise<any> {
    const params = new URLSearchParams()

    if (filters) {
      if (filters.event_id) params.append('event_id', filters.event_id)
      if (filters.gate_id) params.append('gate_id', filters.gate_id)
      if (filters.scanner_id) params.append('scanner_id', filters.scanner_id)
      if (filters.scan_type) params.append('scan_type', filters.scan_type)
      if (filters.result) params.append('result', filters.result)
      if (filters.start_date) params.append('start_date', filters.start_date)
      if (filters.end_date) params.append('end_date', filters.end_date)
      if (filters.search) params.append('search', filters.search)
      if (filters.per_page) params.append('per_page', filters.per_page.toString())
    }

    const response = await api.get(`/scans/history?${params.toString()}`)
    return response.data
  },

  /**
   * Export scans data
   */
  async exportScans(filters?: any): Promise<void> {
    // TODO: Implement export functionality when backend endpoint is ready
    console.log('Export scans with filters:', filters)
  }
}

export default scanService