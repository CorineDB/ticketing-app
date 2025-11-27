/**
 * Gate Service
 * Manages gates for events (entrance, exit, vip, other)
 */

import type {
  Gate,
  CreateGateData,
  UpdateGateData,
  GateFilters,
  PaginatedResponse,
  ApiResponse
} from '@/types/api'
import api from './api'

class GateService {
  private readonly BASE_URL = '/gates'

  /**
   * Get all gates with optional filters
   */
  async getAll(filters?: GateFilters): Promise<PaginatedResponse<Gate>> {
    const response = await api.get<PaginatedResponse<Gate>>(this.BASE_URL, {
      params: filters
    })
    return response.data
  }

  /**
   * Get gates for a specific event
   */
  async getByEventId(eventId: number, filters?: GateFilters): Promise<Gate[]> {
    const response = await api.get<ApiResponse<Gate[]>>(`/events/${eventId}/gates`, {
      params: filters
    })
    return response.data.data || []
  }

  /**
   * Get a single gate by ID
   */
  async getById(id: string): Promise<Gate> {
    const response = await api.get<ApiResponse<Gate>>(`${this.BASE_URL}/${id}`)
    return response.data.data!
  }

  /**
   * Create a new gate
   */
  async create(data: CreateGateData): Promise<Gate> {
    const response = await api.post<ApiResponse<Gate>>(this.BASE_URL, data)
    return response.data.data!
  }

  /**
   * Update an existing gate
   */
  async update(id: string, data: UpdateGateData): Promise<Gate> {
    const response = await api.put<ApiResponse<Gate>>(`${this.BASE_URL}/${id}`, data)
    return response.data.data!
  }

  /**
   * Delete a gate
   */
  async delete(id: string): Promise<void> {
    await api.delete(`${this.BASE_URL}/${id}`)
  }

  /**
   * Update gate status (active, pause, inactive)
   */
  async updateStatus(id: string, status: 'active' | 'pause' | 'inactive'): Promise<Gate> {
    const response = await api.patch<ApiResponse<Gate>>(`${this.BASE_URL}/${id}/status`, {
      status
    })
    return response.data.data!
  }

  /**
   * Assign a scanner to a gate
   */
  async assignScanner(gateId: number, scannerId: number | null): Promise<Gate> {
    const response = await api.patch<ApiResponse<Gate>>(`${this.BASE_URL}/${gateId}/assign`, {
      scanner_id: scannerId
    })
    return response.data.data!
  }

  /**
   * Get gate statistics
   */
  async getStatistics(gateId: number, dateFrom?: string, dateTo?: string) {
    const response = await api.get<ApiResponse<{
      total_scans: number
      valid_scans: number
      invalid_scans: number
      entries: number
      exits: number
      scans_by_hour: {
        hour: string
        count: number
      }[]
    }>>(`${this.BASE_URL}/${gateId}/statistics`, {
      params: { date_from: dateFrom, date_to: dateTo }
    })
    return response.data.data!
  }

  /**
   * Bulk create gates for an event
   */
  async bulkCreate(eventId: number, gates: Omit<CreateGateData, 'event_id'>[]): Promise<Gate[]> {
    const response = await api.post<ApiResponse<Gate[]>>(`/events/${eventId}/gates/bulk`, {
      gates
    })
    return response.data.data || []
  }
}

export default new GateService()
