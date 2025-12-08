import api from './api'
import type {
  Organization,
  CreateOrganizationData,
  UpdateOrganizationData,
  OrganizationFilters,
  PaginatedResponse
} from '@/types/api'

class OrganizationService {
  /**
   * Get all organisateurs
   */
  async getAll(filters?: OrganizationFilters): Promise<PaginatedResponse<Organization>> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<PaginatedResponse<Organization>>('/organisateurs', { params })
    return response.data
  }

  /**
   * Get single organisateur by ID
   */
  async getById(id: string): Promise<Organization> {
    const response = await api.get<{ data: Organization }>(`/organisateurs/${id}`)
    return response.data.data
  }

  /**
   * Get my organisateur (for organizer role)
   */
  async getMyOrganization(): Promise<Organization> {
    const response = await api.get<{ data: Organization }>('/organisateurs/me')
    return response.data.data
  }

  /**
   * Create a new organisateur
   */
  async create(data: CreateOrganizationData): Promise<Organization> {
    const response = await api.post<{ data: Organization }>('/organisateurs', data)
    return response.data.data
  }

  /**
   * Update an existing organisateur
   */
  async update(id: string, data: UpdateOrganizationData): Promise<Organization> {
    const response = await api.put<{ data: Organization }>(`/organisateurs/${id}`, data)
    return response.data.data
  }

  /**
   * Delete an organisateur
   */
  async delete(id: string): Promise<void> {
    await api.delete(`/organisateurs/${id}`)
  }

  /**
   * Suspend an organisateur
   */
  async suspend(id: string, reason?: string): Promise<Organization> {
    const response = await api.post<{ data: Organization }>(`/organisateurs/${id}/suspend`, {
      reason
    })
    return response.data.data
  }

  /**
   * Activate an organisateur
   */
  async activate(id: string): Promise<Organization> {
    const response = await api.post<{ data: Organization }>(`/organisateurs/${id}/activate`)
    return response.data.data
  }

  /**
   * Get organisateur members
   */
  async getMembers(id: string): Promise<any[]> {
    const response = await api.get(`/organisateurs/${id}/members`)
    return response.data.data
  }

  /**
   * Add member to organisateur
   */
  async addMember(id: string, userId: string, role: string): Promise<void> {
    await api.post(`/organisateurs/${id}/members`, {
      user_id: userId,
      role
    })
  }

  /**
   * Remove member from organisateur
   */
  async removeMember(id: string, userId: string): Promise<void> {
    await api.delete(`/organisateurs/${id}/members/${userId}`)
  }

  /**
   * Build query parameters from filters
   */
  private buildQueryParams(filters?: OrganizationFilters): Record<string, any> {
    if (!filters) return {}

    const params: Record<string, any> = {}

    if (filters.search) params.search = filters.search
    if (filters.status) params.status = filters.status

    return params
  }

  /**
   * Convert data to FormData for file upload support
   */
  private toFormData(data: CreateOrganizationData | UpdateOrganizationData): FormData {
    const formData = new FormData()

    Object.entries(data).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        if (value instanceof File) {
          formData.append(key, value)
        } else {
          // Convert booleans to 0/1 for FormData
          const formValue = typeof value === 'boolean' ? (value ? '1' : '0') : String(value)
          formData.append(key, formValue)
        }
      }
    })

    return formData
  }
}

export default new OrganizationService()
