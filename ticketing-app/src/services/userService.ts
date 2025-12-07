import api from './api'
import type { User, PaginatedResponse } from '@/types/api'

interface CreateUserData {
  name: string
  email: string
  phone?: string
  password: string
  type: string
  organisateur_id?: string
  role_id?: number
}

interface UpdateUserData {
  name?: string
  email?: string
  phone?: string
  password?: string
  type?: string
  organisateur_id?: string
  role_id?: number
}

class UserService {
  /**
   * Get all users
   */
  async getAll(filters?: Record<string, any>): Promise<PaginatedResponse<User>> {
    // Remove empty, null, or undefined values from filters
    const cleanFilters = filters
      ? Object.fromEntries(
        Object.entries(filters).filter(([_, value]) => value !== '' && value !== null && value !== undefined)
      )
      : {}

    const response = await api.get<PaginatedResponse<User>>('/users', { params: cleanFilters })
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
  async assignRole(userId: string, roleId: string): Promise<User> {
    const response = await api.post<{ data: User }>(`/users/${userId}/roles`, {
      role_id: roleId
    })
    return response.data.data
  }

  /**
   * Remove role from user
   */
  async removeRole(userId: string): Promise<User> {
    const response = await api.delete<{ data: User }>(`/users/${userId}/roles`)
    return response.data.data
  }

  /**
   * Get scanners for an organisateur
   */
  async getScanners(organisateurId?: number): Promise<User[]> {
    const params = organisateurId ? { organisateur_id: organisateurId } : {}
    const response = await api.get<{ data: User[] }>('/users/scanners', { params })
    return response.data.data
  }

  /**
   * Get cashiers for an organisateur
   */
  async getCashiers(organisateurId?: number): Promise<User[]> {
    const params = organisateurId ? { organisateur_id: organisateurId } : {}
    const response = await api.get<{ data: User[] }>('/users/cashiers', { params })
    return response.data.data
  }
}

export default new UserService()
