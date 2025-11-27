import { ref } from 'vue'
import api from '@/services/api'
import type { Role } from '@/types/api'

export function useRoles() {
  const roles = ref<Role[]>([])
  const role = ref<Role | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchRoles = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get<{ data: Role[] }>('/roles')
      roles.value = response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch roles'
      console.error('Error fetching roles:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchRole = async (id: number) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get<{ data: Role }>(`/roles/${id}`)
      role.value = response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch role'
      console.error('Error fetching role:', e)
    } finally {
      loading.value = false
    }
  }

  const createRole = async (data: Partial<Role>) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post<{ data: Role }>('/roles', data)
      const newRole = response.data.data
      roles.value.push(newRole)
      return newRole
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create role'
      console.error('Error creating role:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const updateRole = async (id: number, data: Partial<Role>) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.put<{ data: Role }>(`/roles/${id}`, data)
      const updatedRole = response.data.data
      const index = roles.value.findIndex(r => r.id === id)
      if (index !== -1) {
        roles.value[index] = updatedRole
      }
      if (role.value?.id === id) {
        role.value = updatedRole
      }
      return updatedRole
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update role'
      console.error('Error updating role:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const deleteRole = async (id: number) => {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/roles/${id}`)
      roles.value = roles.value.filter(r => r.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to delete role'
      console.error('Error deleting role:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    roles,
    role,
    loading,
    error,
    fetchRoles,
    fetchRole,
    createRole,
    updateRole,
    deleteRole
  }
}
