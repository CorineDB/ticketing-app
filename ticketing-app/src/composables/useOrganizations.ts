import { ref } from 'vue'
import organizationService from '@/services/organizationService'
import type { Organization, CreateOrganizationData, UpdateOrganizationData, OrganizationFilters, PaginatedResponse } from '@/types/api'

export function useOrganizations() {
  const organisateurs = ref<Organization[]>([])
  const organisateur = ref<Organization | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1
  })

  const fetchOrganizations = async (filters?: OrganizationFilters) => {
    loading.value = true
    error.value = null
    try {
      const response = await organizationService.getAll(filters)
      // Backend returns {data: [...]} without pagination
      if (Array.isArray(response.data)) {
        organisateurs.value = response.data
        pagination.value = {
          total: response.data.length,
          per_page: response.data.length,
          current_page: 1,
          last_page: 1
        }
      } else {
        // Handle paginated response if backend changes
        organisateurs.value = response.data
        pagination.value = {
          total: response.meta?.total || 0,
          per_page: response.meta?.per_page || 10,
          current_page: response.meta?.current_page || 1,
          last_page: response.meta?.last_page || 1
        }
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch organisateurs'
      console.error('Error fetching organisateurs:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchOrganization = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      organisateur.value = await organizationService.getById(id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch organisateur'
      console.error('Error fetching organisateur:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchMyOrganization = async () => {
    loading.value = true
    error.value = null
    try {
      organisateur.value = await organizationService.getMyOrganization()
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch my organisateur'
      console.error('Error fetching my organisateur:', e)
    } finally {
      loading.value = false
    }
  }

  const createOrganization = async (data: CreateOrganizationData) => {
    loading.value = true
    error.value = null
    try {
      const newOrganization = await organizationService.create(data)
      organisateurs.value.unshift(newOrganization)
      return newOrganization
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create organisateur'
      console.error('Error creating organisateur:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const updateOrganization = async (id: string, data: UpdateOrganizationData) => {
    loading.value = true
    error.value = null
    try {
      const updatedOrganization = await organizationService.update(id, data)
      const index = organisateurs.value.findIndex(o => o.id === id)
      if (index !== -1) {
        organisateurs.value[index] = updatedOrganization
      }
      if (organisateur.value?.id === id) {
        organisateur.value = updatedOrganization
      }
      return updatedOrganization
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update organisateur'
      console.error('Error updating organisateur:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const deleteOrganization = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      await organizationService.delete(id)
      organisateurs.value = organisateurs.value.filter(o => o.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to delete organisateur'
      console.error('Error deleting organisateur:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const suspendOrganization = async (id: string, reason?: string) => {
    loading.value = true
    error.value = null
    try {
      const suspended = await organizationService.suspend(id, reason)
      const index = organisateurs.value.findIndex(o => o.id === id)
      if (index !== -1) {
        organisateurs.value[index] = suspended
      }
      if (organisateur.value?.id === id) {
        organisateur.value = suspended
      }
      return suspended
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to suspend organisateur'
      console.error('Error suspending organisateur:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const activateOrganization = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      const activated = await organizationService.activate(id)
      const index = organisateurs.value.findIndex(o => o.id === id)
      if (index !== -1) {
        organisateurs.value[index] = activated
      }
      if (organisateur.value?.id === id) {
        organisateur.value = activated
      }
      return activated
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to activate organisateur'
      console.error('Error activating organisateur:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    organisateurs,
    organisateur,
    loading,
    error,
    pagination,
    fetchOrganizations,
    fetchOrganization,
    fetchMyOrganization,
    createOrganization,
    updateOrganization,
    deleteOrganization,
    suspendOrganization,
    activateOrganization
  }
}
