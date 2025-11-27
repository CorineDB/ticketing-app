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
   * Get all organizations
   */
  async getAll(filters?: OrganizationFilters): Promise<PaginatedResponse<Organization>> {
    const params = this.buildQueryParams(filters)
    const response = await api.get<PaginatedResponse<Organization>>('/organizations', { params })
    return response.data
  }

  /**
   * Get single organization by ID
   */
  async getById(id: string): Promise<Organization> {
    const response = await api.get<{ data: Organization }>(`/organizations/${id}`)
    return response.data.data
  }

  /**
   * Get my organization (for organizer role)
   */
  async getMyOrganization(): Promise<Organization> {
    const response = await api.get<{ data: Organization }>('/organizations/me')
    return response.data.data
  }

  /**
   * Create a new organization
   */
  async create(data: CreateOrganizationData): Promise<Organization> {
    const formData = this.toFormData(data)
    const response = await api.post<{ data: Organization }>('/organizations', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return response.data.data
  }

  /**
   * Update an existing organization
   */
  async update(id: string, data: UpdateOrganizationData): Promise<Organization> {
    const formData = this.toFormData(data)
    formData.append('_method', 'PUT')
    const response = await api.post<{ data: Organization }>(`/organizations/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return response.data.data
  }

  /**
   * Delete an organization
   */
  async delete(id: string): Promise<void> {
    await api.delete(`/organizations/${id}`)
  }

  /**
   * Suspend an organization
   */
  async suspend(id: string, reason?: string): Promise<Organization> {
    const response = await api.post<{ data: Organization }>(`/organizations/${id}/suspend`, {
      reason
    })
    return response.data.data
  }

  /**
   * Activate an organization
   */
  async activate(id: string): Promise<Organization> {
    const response = await api.post<{ data: Organization }>(`/organizations/${id}/activate`)
    return response.data.data
  }

  /**
   * Get organization members
   */
  async getMembers(id: string): Promise<any[]> {
    const response = await api.get(`/organizations/${id}/members`)
    return response.data.data
  }

  /**
   * Add member to organization
   */
  async addMember(id: string, userId: number, role: string): Promise<void> {
    await api.post(`/organizations/${id}/members`, {
      user_id: userId,
      role
    })
  }

  /**
   * Remove member from organization
   */
  async removeMember(id: string, userId: number): Promise<void> {
    await api.delete(`/organizations/${id}/members/${userId}`)
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
          formData.append(key, String(value))
        }
      }
    })

    return formData
  }
}

export default new OrganizationService()
