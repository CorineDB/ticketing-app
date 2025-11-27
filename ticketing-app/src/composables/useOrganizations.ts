import { ref } from 'vue'
import organizationService from '@/services/organizationService'
import type { Organization, CreateOrganizationData, UpdateOrganizationData, OrganizationFilters, PaginatedResponse } from '@/types/api'

export function useOrganizations() {
  const organizations = ref<Organization[]>([])
  const organization = ref<Organization | null>(null)
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
      const response: PaginatedResponse<Organization> = await organizationService.getAll(filters)
      organizations.value = response.data
      pagination.value = {
        total: response.total,
        per_page: response.per_page,
        current_page: response.current_page,
        last_page: response.last_page
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch organizations'
      console.error('Error fetching organizations:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchOrganization = async (id: number) => {
    loading.value = true
    error.value = null
    try {
      organization.value = await organizationService.getById(id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch organization'
      console.error('Error fetching organization:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchMyOrganization = async () => {
    loading.value = true
    error.value = null
    try {
      organization.value = await organizationService.getMyOrganization()
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch my organization'
      console.error('Error fetching my organization:', e)
    } finally {
      loading.value = false
    }
  }

  const createOrganization = async (data: CreateOrganizationData) => {
    loading.value = true
    error.value = null
    try {
      const newOrganization = await organizationService.create(data)
      organizations.value.unshift(newOrganization)
      return newOrganization
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create organization'
      console.error('Error creating organization:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const updateOrganization = async (id: number, data: UpdateOrganizationData) => {
    loading.value = true
    error.value = null
    try {
      const updatedOrganization = await organizationService.update(id, data)
      const index = organizations.value.findIndex(o => o.id === id)
      if (index !== -1) {
        organizations.value[index] = updatedOrganization
      }
      if (organization.value?.id === id) {
        organization.value = updatedOrganization
      }
      return updatedOrganization
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update organization'
      console.error('Error updating organization:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const deleteOrganization = async (id: number) => {
    loading.value = true
    error.value = null
    try {
      await organizationService.delete(id)
      organizations.value = organizations.value.filter(o => o.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to delete organization'
      console.error('Error deleting organization:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const suspendOrganization = async (id: number, reason?: string) => {
    loading.value = true
    error.value = null
    try {
      const suspended = await organizationService.suspend(id, reason)
      const index = organizations.value.findIndex(o => o.id === id)
      if (index !== -1) {
        organizations.value[index] = suspended
      }
      if (organization.value?.id === id) {
        organization.value = suspended
      }
      return suspended
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to suspend organization'
      console.error('Error suspending organization:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const activateOrganization = async (id: number) => {
    loading.value = true
    error.value = null
    try {
      const activated = await organizationService.activate(id)
      const index = organizations.value.findIndex(o => o.id === id)
      if (index !== -1) {
        organizations.value[index] = activated
      }
      if (organization.value?.id === id) {
        organization.value = activated
      }
      return activated
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to activate organization'
      console.error('Error activating organization:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    organizations,
    organization,
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
