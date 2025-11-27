import api from './api'
import type {
  Event,
  CreateEventData,
  UpdateEventData,
  EventFilters,
  PaginatedResponse,
  EventStatistics
} from '@/types/api'

class EventService {
  /**
   * Get all events with optional filters
   */
  async getAll(filters?: EventFilters): Promise<PaginatedResponse<Event>> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<PaginatedResponse<Event>>('/events', { params })
    return response.data
  }

  /**
   * Get single event by ID
   */
  async getById(id: string): Promise<Event> {
    const response = await api.get<{ data: Event }>(`/events/${id}`)
    return response.data.data
  }

  /**
   * Get public event by ID
   */
  async getPublicById(id: string): Promise<Event> {
    const response = await api.get<{ data: Event }>(`/public/events/${id}`)
    return response.data.data
  }


  /**
   * Get event by slug (public access)
   */
  async getBySlug(slug: string): Promise<Event> {
    const response = await api.get<{ data: Event }>(`/events/slug/${slug}`)
    return response.data.data
  }



  /**
   * Create a new event
   */
  async create(data: CreateEventData): Promise<Event> {
    const formData = this.toFormData(data)
    const response = await api.post<{ data: Event }>('/events', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return response.data.data
  }

  /**
   * Update an existing event
   */
  async update(id: string, data: UpdateEventData): Promise<Event> {
    const formData = this.toFormData(data)
    formData.append('_method', 'PUT')
    const response = await api.post<{ data: Event }>(`/events/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return response.data.data
  }

  /**
   * Delete an event
   */
  async delete(id: string): Promise<void> {
    await api.delete(`/events/${id}`)
  }

  /**
   * Publish an event
   */
  async publish(id: string): Promise<Event> {
    const response = await api.post<{ data: Event }>(`/events/${id}/publish`)
    return response.data.data
  }

  /**
   * Unpublish an event
   */
  async unpublish(id: string): Promise<Event> {
    const response = await api.post<{ data: Event }>(`/events/${id}/unpublish`)
    return response.data.data
  }

  /**
   * Get event statistics
   */
  async getStatistics(id: string): Promise<EventStatistics> {
    const response = await api.get<{ data: EventStatistics }>(`/events/${id}/statistics`)
    return response.data.data
  }

  /**
   * Get my events (for organizers)
   */
  async getMyEvents(filters?: EventFilters): Promise<PaginatedResponse<Event>> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<PaginatedResponse<Event>>('/events/my-events', { params })
    return response.data
  }

  /**
   * Duplicate an event
   */
  async duplicate(id: string): Promise<Event> {
    const response = await api.post<{ data: Event }>(`/events/${id}/duplicate`)
    return response.data.data
  }

  /**
   * Build query parameters from filters
   */
  private buildQueryParams(filters?: EventFilters): Record<string, any> {
    if (!filters) return {}

    const params: Record<string, any> = {}

    if (filters.status) params.status = filters.status
    if (filters.organization_id) params.organization_id = filters.organization_id
    if (filters.start_date) params.start_date = filters.start_date
    if (filters.end_date) params.end_date = filters.end_date
    if (filters.city) params.city = filters.city
    if (filters.search) params.search = filters.search

    return params
  }

  /**
   * Convert data to FormData for file upload support
   */
  private toFormData(data: CreateEventData | UpdateEventData): FormData {
    const formData = new FormData()

    Object.entries(data).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        if (value instanceof File) {
          formData.append(key, value)
        } else if (Array.isArray(value)) {
          // Handle arrays (like ticket_types)
          value.forEach((item, index) => {
            if (typeof item === 'object') {
              // Handle array of objects
              Object.entries(item).forEach(([subKey, subValue]) => {
                // Only append if value is not empty string, undefined, or null
                if (subValue !== undefined && subValue !== null && subValue !== '') {
                  formData.append(`${key}[${index}][${subKey}]`, String(subValue))
                }
              })
            } else {
              formData.append(`${key}[${index}]`, String(item))
            }
          })
        } else {
          formData.append(key, String(value))
        }
      }
    })

    return formData
  }
}

export default new EventService()
