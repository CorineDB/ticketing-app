import { ref, readonly } from 'vue'
import ticketService from '@/services/ticketService'
import type { Ticket, CreateTicketData, UpdateTicketData, TicketFilters } from '@/types/api'
import { useNotificationStore } from '@/stores/notifications'

export function useTickets() {
  const notifications = useNotificationStore()

  const tickets = ref<Ticket[]>([])
  const currentTicket = ref<Ticket | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const totalPages = ref(1)
  const currentPage = ref(1)

  async function fetchTickets(filters?: TicketFilters) {
    loading.value = true
    error.value = null

    try {
      const response = await ticketService.getAll(filters)
      tickets.value = response.data
      totalPages.value = response.meta.last_page
      currentPage.value = response.meta.current_page
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch tickets'
      notifications.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  async function fetchTicket(id: number) {
    loading.value = true
    error.value = null

    try {
      currentTicket.value = await ticketService.getById(id)
      return currentTicket.value
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch ticket'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function fetchTicketByCode(code: string, token?: string) {
    loading.value = true
    error.value = null

    try {
      currentTicket.value = await ticketService.getByCode(code, token)
      return currentTicket.value
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Ticket not found'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function createTicket(data: CreateTicketData) {
    loading.value = true
    error.value = null

    try {
      const newTicket = await ticketService.create(data)
      tickets.value.unshift(newTicket)
      notifications.success('Success', 'Ticket created successfully')
      return newTicket
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create ticket'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateTicket(id: number, data: UpdateTicketData) {
    loading.value = true
    error.value = null

    try {
      const updatedTicket = await ticketService.update(id, data)
      const index = tickets.value.findIndex((t) => t.id === id)
      if (index > -1) {
        tickets.value[index] = updatedTicket
      }
      if (currentTicket.value?.id === id) {
        currentTicket.value = updatedTicket
      }
      notifications.success('Success', 'Ticket updated successfully')
      return updatedTicket
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update ticket'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function cancelTicket(id: number, reason?: string) {
    loading.value = true
    error.value = null

    try {
      const cancelledTicket = await ticketService.cancel(id, reason)
      const index = tickets.value.findIndex((t) => t.id === id)
      if (index > -1) {
        tickets.value[index] = cancelledTicket
      }
      notifications.success('Success', 'Ticket cancelled')
      return cancelledTicket
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to cancel ticket'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function markAsPaid(id: number, paymentReference: string) {
    loading.value = true
    error.value = null

    try {
      const paidTicket = await ticketService.markAsPaid(id, paymentReference)
      const index = tickets.value.findIndex((t) => t.id === id)
      if (index > -1) {
        tickets.value[index] = paidTicket
      }
      notifications.success('Success', 'Ticket marked as paid')
      return paidTicket
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to mark ticket as paid'
      notifications.error('Error', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  async function downloadTicket(id: number) {
    loading.value = true
    error.value = null

    try {
      const blob = await ticketService.downloadPDF(id)
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `ticket-${id}.pdf`
      link.click()
      window.URL.revokeObjectURL(url)
      notifications.success('Success', 'Ticket downloaded')
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to download ticket'
      notifications.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  async function sendTicketEmail(id: number) {
    loading.value = true
    error.value = null

    try {
      await ticketService.sendEmail(id)
      notifications.success('Success', 'Ticket sent via email')
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to send email'
      notifications.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  async function sendTicketSMS(id: number) {
    loading.value = true
    error.value = null

    try {
      await ticketService.sendSMS(id)
      notifications.success('Success', 'Ticket sent via SMS')
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to send SMS'
      notifications.error('Error', error.value)
    } finally {
      loading.value = false
    }
  }

  return {
    tickets: readonly(tickets),
    currentTicket: readonly(currentTicket),
    loading: readonly(loading),
    error: readonly(error),
    totalPages: readonly(totalPages),
    currentPage: readonly(currentPage),

    fetchTickets,
    fetchTicket,
    fetchTicketByCode,
    createTicket,
    updateTicket,
    cancelTicket,
    markAsPaid,
    downloadTicket,
    sendTicketEmail,
    sendTicketSMS
  }
}
