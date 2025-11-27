import { ref } from 'vue'
import api from '@/services/api'
import type { Event, TicketType } from '@/types/api'

export function useCheckout() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const selectedEvent = ref<Event | null>(null)
  const selectedTicketType = ref<TicketType | null>(null)
  const quantity = ref(1)

  const initiateCheckout = async (eventId: number, ticketTypeId: number, qty: number, customerData: any) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/checkout/initiate', {
        event_id: eventId,
        ticket_type_id: ticketTypeId,
        quantity: qty,
        customer: customerData
      })
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to initiate checkout'
      console.error('Error initiating checkout:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const processPayment = async (checkoutId: number, paymentData: any) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(`/checkout/${checkoutId}/process`, paymentData)
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to process payment'
      console.error('Error processing payment:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const verifyPayment = async (transactionId: string) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/checkout/verify/${transactionId}`)
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to verify payment'
      console.error('Error verifying payment:', e)
      throw e
    } finally {
      loading.value = false
    }
  }

  const setSelectedEvent = (event: Event) => {
    selectedEvent.value = event
  }

  const setSelectedTicketType = (ticketType: TicketType) => {
    selectedTicketType.value = ticketType
  }

  const setQuantity = (qty: number) => {
    quantity.value = qty
  }

  const reset = () => {
    selectedEvent.value = null
    selectedTicketType.value = null
    quantity.value = 1
    error.value = null
  }

  return {
    loading,
    error,
    selectedEvent,
    selectedTicketType,
    quantity,
    initiateCheckout,
    processPayment,
    verifyPayment,
    setSelectedEvent,
    setSelectedTicketType,
    setQuantity,
    reset
  }
}
