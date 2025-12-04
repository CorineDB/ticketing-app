import { ref } from 'vue'
import ticketService from '@/services/ticketService'
import type { Ticket, CreateTicketData, UpdateTicketData, TicketFilters, PaginatedResponse } from '@/types/api'

export function useTickets() {
  const tickets = ref<Ticket[]>([])
  const ticket = ref<Ticket | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1
  })

  const fetchTickets = async (filters?: TicketFilters) => {
    loading.value = true
    error.value = null
    try {
      const response: PaginatedResponse<Ticket> = await ticketService.getAll(filters)
      tickets.value = response.data
      pagination.value = {
        total: response.meta.total,
        per_page: response.meta.per_page,
        current_page: response.meta.current_page,
        last_page: response.meta.last_page
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch tickets'
      console.error('Error fetching tickets:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchTicket = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      ticket.value = await ticketService.getById(id)
      console.log('Fetched ticket:', ticket.value);
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch ticket'
      console.error('Error fetching ticket:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchPublicTicket = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      ticket.value = await ticketService.getPublicById(id)
      console.log('Fetched ticket:', ticket.value);
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch ticket'
      console.error('Error fetching ticket:', e)
    } finally {
      loading.value = false
    }
  }

  const getTicketQR = async (id: string, token: string) => {
    loading.value = true
    error.value = null
    try {
      return await ticketService.downloadQR(id, token);
      console.log('Fetched ticket:', qr);
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch ticket'
      console.error('Error fetching ticket:', e)
    } finally {
      loading.value = false
    }
  }

  const fetchTicketByCode = async (code: string, token?: string) => {
    loading.value = true
    error.value = null
    try {
      ticket.value = await ticketService.getByCode(code, token)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch ticket'
      console.error('Error fetching ticket:', e)
    } finally {
      loading.value = false
    }
  }

  const createTicket = async (data: CreateTicketData) => {
    loading.value = true
    error.value = null
    try {
      const newTicket = await ticketService.create(data)
      tickets.value.unshift(newTicket)
      return newTicket
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create ticket'
      console.error('Error creating ticket:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const updateTicket = async (id: string, data: UpdateTicketData) => {
    loading.value = true
    error.value = null
    try {
      const updatedTicket = await ticketService.update(id, data)
      const index = tickets.value.findIndex(t => t.id === id)
      if (index !== -1) {
        tickets.value[index] = updatedTicket
      }
      if (ticket.value?.id === id) {
        ticket.value = updatedTicket
      }
      return updatedTicket
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update ticket'
      console.error('Error updating ticket:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const cancelTicket = async (id: string, reason?: string) => {
    loading.value = true
    error.value = null
    try {
      const cancelled = await ticketService.cancel(id, reason)
      const index = tickets.value.findIndex(t => t.id === id)
      if (index !== -1) {
        tickets.value[index] = cancelled
      }
      if (ticket.value?.id === id) {
        ticket.value = cancelled
      }
      return cancelled
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to cancel ticket'
      console.error('Error cancelling ticket:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const refundTicket = async (id: string, reason?: string) => {
    loading.value = true
    error.value = null
    try {
      const refunded = await ticketService.refund(id, reason)
      const index = tickets.value.findIndex(t => t.id === id)
      if (index !== -1) {
        tickets.value[index] = refunded
      }
      if (ticket.value?.id === id) {
        ticket.value = refunded
      }
      return refunded
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to refund ticket'
      console.error('Error refunding ticket:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const validateTicket = async (code: string) => {
    loading.value = true
    error.value = null
    try {
      const result = await ticketService.validate(code)
      return result
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to validate ticket'
      console.error('Error validating ticket:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const resendTicketEmail = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      await ticketService.sendEmail(id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to resend ticket email'
      console.error('Error resending ticket email:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const updateTicketStatus = async (id: string, newStatus: string) => {
    loading.value = true
    error.value = null
    try {
      const updatedTicket = await ticketService.update(id, { status: newStatus as TicketStatus })
      const index = tickets.value.findIndex(t => t.id === id)
      if (index !== -1) {
        tickets.value[index] = updatedTicket
      }
      if (ticket.value?.id === id) {
        ticket.value = updatedTicket
      }
      return updatedTicket
    } catch (e: any) {
      error.value = e.response?.data?.message || `Failed to update ticket status to ${newStatus}`
      console.error(`Error updating ticket status to ${newStatus}:`, e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const regenerateQRCode = async (id: string) => {
    loading.value = true
    error.value = null
    try {
      await ticketService.regenerateQRCode(id)
      // Refresh ticket data to get the new QR code
      await fetchTicket(id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to regenerate QR code'
      console.error('Error regenerating QR code:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    tickets,
    ticket,
    loading,
    error,
    pagination,
    fetchTickets,
    fetchTicketById: fetchTicket, // Renamed fetchTicket to fetchTicketById
    fetchPublicTicket,
    fetchTicketByCode,
    qr_code: getTicketQR,
    createTicket,
    updateTicket,
    cancelTicket,
    refundTicket,
    validateTicket,
    resendTicketEmail,
    updateTicketStatus,
    regenerateQRCode
  }
}
