import api from './api'
import type {
  TicketType,
  CreateTicketTypeData,
  UpdateTicketTypeData
} from '@/types/api'

class TicketTypeService {
  /**
   * Get all ticket types for an event
   */
  async getAll(eventId: string): Promise<TicketType[]> {
    const response = await api.get<{ data: TicketType[] }>(`/events/${eventId}/ticket-types`)
    return response.data
  }

  /**
   * Get single ticket type by ID
   */
  async getById(id: string): Promise<TicketType> {
    const response = await api.get<{ data: TicketType }>(`/ticket-types/${id}`)
    return response.data
  }

  /**
   * Get single ticket type by ID
   */
  async getPublicById(id: string): Promise<TicketType> {
    const response = await api.get<{ data: TicketType }>(`/public/ticket-types/${id}`)
    return response.data
  }

  /**
   * Create a new ticket type
   */
  async create(data: CreateTicketTypeData): Promise<TicketType> {
    const response = await api.post<{ data: TicketType }>('/ticket-types', data)
    return response.data
  }

  /**
   * Update an existing ticket type
   */
  async update(id: string, data: UpdateTicketTypeData): Promise<TicketType> {
    const response = await api.put<{ data: TicketType }>(`/ticket-types/${id}`, data)
    return response.data
  }

  /**
   * Delete a ticket type
   */
  async delete(id: string): Promise<void> {
    await api.delete(`/ticket-types/${id}`)
  }

  /**
   * Activate a ticket type
   */
  async activate(id: string): Promise<TicketType> {
    const response = await api.post<{ data: TicketType }>(`/ticket-types/${id}/activate`)
    return response.data
  }

  /**
   * Deactivate a ticket type
   */
  async deactivate(id: string): Promise<TicketType> {
    const response = await api.post<{ data: TicketType }>(`/ticket-types/${id}/deactivate`)
    return response.data
  }
}

export default new TicketTypeService()
