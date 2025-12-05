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
  async getAll(filters?: EventFilters): Promise<any> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<{ data: Event[] }>('/events', { params })
    return response.data
  }
  /**
   * Get public events with optional filters
   */
  async getAllPublicEvents(filters?: EventFilters): Promise<any> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<{ data: Event[] }>('/public/events', { params })
    return response.data
  }

  /**
   * Get single event by ID (authenticated)
   */
  async getById(id: string): Promise<Event> {
    const response = await api.get<Event>(`/events/${id}`)
    return response.data
  }

  /**
   * Get public event by ID
   */
  async getPublicById(id: string): Promise<Event> {
    const response = await api.get<Event>(`/public/events/${id}`)
    return response.data
  }


  /**
   * Get event by slug (authenticated)
   */
  async getBySlug(slug: string): Promise<Event> {
    const response = await api.get<Event>(`/events/slug/${slug}`)
    return response.data
  }


  /**
   * Get public event by slug (public access)
   */
  async getPublicBySlug(slug: string): Promise<Event> {
    const response = await api.get<Event>(`/public/events/slug/${slug}`)
    return response.data
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
   * Publish an event (change status from draft to published)
   */
  async publish(id: string): Promise<Event> {
    const response = await api.patch<{ event: Event }>(`/events/${id}/publish`)
    return response.data.event
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
  async getMyEvents(filters?: EventFilters): Promise<any> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<{ data: Event[] }>('/events/my-events', { params })
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
    if (filters.organisateur_id) params.organisateur_id = filters.organisateur_id
    if (filters.start_date) params.start_date = filters.start_date
    if (filters.end_date) params.end_date = filters.end_date
    if (filters.city) params.city = filters.city
    if (filters.search) params.search = filters.search

    return params
  }

  /**
   * Convert data to FormData for file upload support
   */
  /**
   * Convert data to FormData for file upload support
   */
  private toFormData(data: CreateEventData | UpdateEventData): FormData {
    const formData = new FormData()
    this.appendFormData(formData, data)
    return formData
  }

  /**
   * Helper to recursively append data to FormData
   */
  private appendFormData(formData: FormData, data: any, parentKey: string | null = null) {
    if (data === null || data === undefined) {
      return
    }

    if (data instanceof File) {
      formData.append(parentKey || 'file', data)
      return
    }

    if (Array.isArray(data)) {
      data.forEach((item, index) => {
        const key = parentKey ? `${parentKey}[${index}]` : `${index}`
        this.appendFormData(formData, item, key)
      })
      return
    }

    if (typeof data === 'object' && !(data instanceof Date)) {
      Object.keys(data).forEach(key => {
        const value = data[key]
        const formKey = parentKey ? `${parentKey}[${key}]` : key
        this.appendFormData(formData, value, formKey)
      })
      return
    }

    // Handle primitive values
    const value = typeof data === 'boolean' ? (data ? '1' : '0') : String(data)
    if (parentKey) {
      formData.append(parentKey, value)
    }
  }
}

export default new EventService()
