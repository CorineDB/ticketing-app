import { ref } from 'vue'
import userService from '@/services/userService'
import type { User, PaginatedResponse } from '@/types/api'

export function useUsers() {
  const users = ref<User[]>([])
  const user = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1
  })

  const fetchUsers = async (filters?: Record<string, any>) => {
    loading.value = true
    error.value = null
    try {
      const response: PaginatedResponse<User> = await userService.getAll(filters)
      users.value = response.data
      pagination.value = {
        total: response.total,
        per_page: response.per_page,
        current_page: response.current_page,
        last_page: response.last_page
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch users'
      console.error('Error fetching users:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchUser = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      user.value = await userService.getById(id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch user'
      console.error('Error fetching user:', e)
    } finally {
      loading.value = false
    }
  }

  const createUser = async (data: any) => {
    loading.value = true
    error.value = null
    try {
      const newUser = await userService.create(data)
      users.value.unshift(newUser)
      return newUser
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create user'
      console.error('Error creating user:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const updateUser = async (id: string, data: any) => {
    loading.value = true
    error.value = null
    try {
      const updatedUser = await userService.update(id, data)
      const index = users.value.findIndex(u => u.id === id)
      if (index !== -1) {
        users.value[index] = updatedUser
      }
      if (user.value?.id === id) {
        user.value = updatedUser
      }
      return updatedUser
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update user'
      console.error('Error updating user:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const deleteUser = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      await userService.delete(id)
      users.value = users.value.filter(u => u.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to delete user'
      console.error('Error deleting user:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const assignRole = async (userId: string, roleId: string) => {
    loading.value = true
    error.value = null
    try {
      const updated = await userService.assignRole(userId, roleId)
      const index = users.value.findIndex(u => u.id === userId)
      if (index !== -1) {
        users.value[index] = updated
      }
      if (user.value?.id === userId) {
        user.value = updated
      }
      return updated
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to assign role'
      console.error('Error assigning role:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    users,
    user,
    loading,
    error,
    pagination,
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
    assignRole
  }
}
