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
    const response = await api.post<ScanConfirmResponse>('/scan/confirm', payload)
    return response.data
  }
}