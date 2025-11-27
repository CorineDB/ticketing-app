import api from './api'
import type { User, PaginatedResponse } from '@/types/api'

interface CreateUserData {
  name: string
  email: string
  phone?: string
  password: string
  type: string
  organization_id?: number
  role_id?: number
}

interface UpdateUserData {
  name?: string
  email?: string
  phone?: string
  password?: string
  type?: string
  organization_id?: number
  role_id?: number
}

class UserService {
  /**
   * Get all users
   */
  async getAll(filters?: Record<string, any>): Promise<PaginatedResponse<User>> {
    const response = await api.get<PaginatedResponse<User>>('/users', { params: filters })
    return response.data
  }

  /**
   * Get single user by ID
   */
  async getById(id: string): Promise<User> {
    const response = await api.get<{ data: User }>(`/users/${id}`)
    return response.data.data
  }

  /**
   * Create a new user
   */
  async create(data: CreateUserData): Promise<User> {
    const response = await api.post<{ data: User }>('/users', data)
    return response.data.data
  }

  /**
   * Update an existing user
   */
  async update(id: string, data: UpdateUserData): Promise<User> {
    const response = await api.put<{ data: User }>(`/users/${id}`, data)
    return response.data.data
  }

  /**
   * Delete a user
   */
  async delete(id: string): Promise<void> {
    await api.delete(`/users/${id}`)
  }

  /**
   * Assign role to user
   */
  async assignRole(userId: number, roleId: number): Promise<User> {
    const response = await api.post<{ data: User }>(`/users/${userId}/roles`, {
      role_id: roleId
    })
    return response.data.data
  }

  /**
   * Remove role from user
   */
  async removeRole(userId: number): Promise<User> {
    const response = await api.delete<{ data: User }>(`/users/${userId}/roles`)
    return response.data.data
  }

  /**
   * Get scanners for an organization
   */
  async getScanners(organizationId?: number): Promise<User[]> {
    const params = organizationId ? { organization_id: organizationId } : {}
    const response = await api.get<{ data: User[] }>('/users/scanners', { params })
    return response.data.data
  }

  /**
   * Get cashiers for an organization
   */
  async getCashiers(organizationId?: number): Promise<User[]> {
    const params = organizationId ? { organization_id: organizationId } : {}
    const response = await api.get<{ data: User[] }>('/users/cashiers', { params })
    return response.data.data
  }
}

export default new UserService()
