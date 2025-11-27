import api from './api'
import type {
  Organization,
  CreateOrganizationData,
  UpdateOrganizationData,
  PaginatedResponse
} from '@/types/api'

class OrganizationService {
  /**
   * Get all organizations
   */
  async getAll(): Promise<PaginatedResponse<Organization>> {
    const response = await api.get<PaginatedResponse<Organization>>('/organizations')
    return response.data
  }

  /**
   * Get single organization by ID
   */
  async getById(id: number): Promise<Organization> {
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
  async update(id: number, data: UpdateOrganizationData): Promise<Organization> {
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
  async delete(id: number): Promise<void> {
    await api.delete(`/organizations/${id}`)
  }

  /**
   * Suspend an organization
   */
  async suspend(id: number, reason?: string): Promise<Organization> {
    const response = await api.post<{ data: Organization }>(`/organizations/${id}/suspend`, {
      reason
    })
    return response.data.data
  }

  /**
   * Activate an organization
   */
  async activate(id: number): Promise<Organization> {
    const response = await api.post<{ data: Organization }>(`/organizations/${id}/activate`)
    return response.data.data
  }

  /**
   * Get organization members
   */
  async getMembers(id: number): Promise<any[]> {
    const response = await api.get(`/organizations/${id}/members`)
    return response.data.data
  }

  /**
   * Add member to organization
   */
  async addMember(id: number, userId: number, role: string): Promise<void> {
    await api.post(`/organizations/${id}/members`, {
      user_id: userId,
      role
    })
  }

  /**
   * Remove member from organization
   */
  async removeMember(id: number, userId: number): Promise<void> {
    await api.delete(`/organizations/${id}/members/${userId}`)
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
