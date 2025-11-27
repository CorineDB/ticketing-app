import { ref } from 'vue'
import ticketTypeService from '@/services/ticketTypeService'
import type { TicketType, CreateTicketTypeData, UpdateTicketTypeData } from '@/types/api'

export function useTicketTypes() {
  const ticketTypes = ref<TicketType[]>([])
  const ticketType = ref<TicketType | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchTicketTypes = async (eventId: number) => {
    loading.value = true
    error.value = null
    try {
      ticketTypes.value = await ticketTypeService.getAll(eventId)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch ticket types'
      console.error('Error fetching ticket types:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchTicketType = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      ticketType.value = await ticketTypeService.getById(id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch ticket type'
      console.error('Error fetching ticket type:', e)
    } finally {
      loading.value = false
    }
  }

  const createTicketType = async (data: CreateTicketTypeData) => {
    loading.value = true
    error.value = null
    try {
      const newTicketType = await ticketTypeService.create(data)
      ticketTypes.value.push(newTicketType)
      return newTicketType
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create ticket type'
      console.error('Error creating ticket type:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const updateTicketType = async (id: string, data: UpdateTicketTypeData) => {
    loading.value = true
    error.value = null
    try {
      const updatedTicketType = await ticketTypeService.update(id, data)
      const index = ticketTypes.value.findIndex(tt => tt.id === id)
      if (index !== -1) {
        ticketTypes.value[index] = updatedTicketType
      }
      if (ticketType.value?.id === id) {
        ticketType.value = updatedTicketType
      }
      return updatedTicketType
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update ticket type'
      console.error('Error updating ticket type:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const deleteTicketType = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      await ticketTypeService.delete(id)
      ticketTypes.value = ticketTypes.value.filter(tt => tt.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to delete ticket type'
      console.error('Error deleting ticket type:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const activateTicketType = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      const activated = await ticketTypeService.activate(id)
      const index = ticketTypes.value.findIndex(tt => tt.id === id)
      if (index !== -1) {
        ticketTypes.value[index] = activated
      }
      if (ticketType.value?.id === id) {
        ticketType.value = activated
      }
      return activated
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to activate ticket type'
      console.error('Error activating ticket type:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const deactivateTicketType = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      const deactivated = await ticketTypeService.deactivate(id)
      const index = ticketTypes.value.findIndex(tt => tt.id === id)
      if (index !== -1) {
        ticketTypes.value[index] = deactivated
      }
      if (ticketType.value?.id === id) {
        ticketType.value = deactivated
      }
      return deactivated
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to deactivate ticket type'
      console.error('Error deactivating ticket type:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    ticketTypes,
    ticketType,
    loading,
    error,
    fetchTicketTypes,
    fetchTicketType,
    createTicketType,
    updateTicketType,
    deleteTicketType,
    activateTicketType,
    deactivateTicketType
  }
}
