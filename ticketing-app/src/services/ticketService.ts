import api from './api'
import type {
  Ticket,
  CreateTicketData,
  UpdateTicketData,
  TicketFilters,
  PaginatedResponse
} from '@/types/api'

class TicketService {
  /**
   * Get all tickets with optional filters
   */
  async getAll(filters?: TicketFilters): Promise<PaginatedResponse<Ticket>> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<PaginatedResponse<Ticket>>('/tickets', { params })
    return response.data
  }

  /**
   * Get single ticket by ID
   */
  async getById(id: string): Promise<Ticket> {
    const response = await api.get<Ticket>(`/tickets/${id}`)
    return response.data
  }

  /**
   * Get single ticket by ID
   */
  async getPublicById(id: string): Promise<Ticket> {
    const response = await api.get<Ticket>(`public/tickets/${id}`)
    return response.data
  }

  /**
   * Get ticket by code (for public access)
   */
  async getByCode(code: string, token?: string): Promise<Ticket> {
    const params = token ? { token } : {}
    const response = await api.get<Ticket>(`/tickets/code/${code}`, { params })
    return response.data
  }

  /**
   * Create a new ticket (manual/cash)
   */
  async create(data: CreateTicketData): Promise<Ticket> {
    const response = await api.post<{ data: Ticket }>('/tickets', data)
    return response.data.data
  }

  /**
   * Update ticket information
   */
  async update(id: string, data: UpdateTicketData): Promise<Ticket> {
    const response = await api.put<{ data: Ticket }>(`/tickets/${id}`, data)
    return response.data.data
  }

  /**
   * Cancel a ticket
   */
  async cancel(id: string, reason?: string): Promise<Ticket> {
    const response = await api.post<{ data: Ticket }>(`/tickets/${id}/cancel`, { reason })
    return response.data.data
  }

  /**
   * Refund a ticket
   */
  async refund(id: string, reason?: string): Promise<Ticket> {
    const response = await api.post<{ data: Ticket }>(`/tickets/${id}/refund`, { reason })
    return response.data.data
  }

  /**
   * Mark ticket as paid (for cash payments)
   */
  async markAsPaid(id: string, paymentReference: string): Promise<Ticket> {
    const response = await api.post<{ data: Ticket }>(`/tickets/${id}/mark-paid`, {
      payment_reference: paymentReference
    })
    return response.data.data
  }

  /**
   * Download ticket as PDF
   */
  async downloadPDF(id: string): Promise<Blob> {
    const response = await api.get(`public/tickets/${id}/qr/download`, {
      responseType: 'blob'
    })
    return response.data
  }

  /**
   * Download ticket as PDF
   */
  async downloadQR(id: string, token: string): Promise<Blob> {
    const response = await api.get(`public/tickets/${id}/qr/download?token=${token}`, {
      responseType: 'blob'
    })
    return response.data
  }

  /**
   * Send ticket via email
   */
  async sendEmail(id: string): Promise<void> {
    await api.post(`/tickets/${id}/send-email`)
  }

  /**
   * Send ticket via SMS
   */
  async sendSMS(id: string): Promise<void> {
    await api.post(`/tickets/${id}/send-sms`)
  }

  /**
   * Validate ticket (check if valid for entry)
   */
  async validate(code: string): Promise<{
    valid: boolean
    message: string
    ticket?: Ticket
  }> {
    const response = await api.post<{
      valid: boolean
      message: string
      ticket?: Ticket
    }>('/tickets/validate', { code })
    return response.data
  }

  /**
   * Regenerate QR code for a ticket
   */
  async regenerateQRCode(id: string): Promise<any> {
    const response = await api.post(`/tickets/${id}/regenerate-qr`)
    return response.data
  }

  /**
   * Build query parameters from filters
   */
  private buildQueryParams(filters?: TicketFilters): Record<string, any> {
    if (!filters) return {}

    const params: Record<string, any> = {}

    if (filters.event_id) params.event_id = filters.event_id
    if (filters.ticket_type_id) params.ticket_type_id = filters.ticket_type_id
    if (filters.status) params.status = filters.status
    if (filters.buyer_email) params.buyer_email = filters.buyer_email
    if (filters.search) params.search = filters.search
    if (filters.paid !== undefined) params.paid = filters.paid
    if (filters.used !== undefined) params.used = filters.used

    return params
  }
}

export default new TicketService()
