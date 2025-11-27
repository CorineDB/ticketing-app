<template>
  <PublicLayout>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-12">
      <div class="max-w-xl w-full bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-3xl font-bold text-gray-900 mb-6 text-center">Checkout</h1>

        <div v-if="loading" class="text-center text-gray-500">Loading checkout details...</div>
        <div v-else-if="error" class="text-center text-red-500">Error: {{ error }}</div>
        <div v-else-if="event && ticketType">
          <!-- Order Summary -->
          <div class="mb-8 border-b pb-6">
            <h2 class="text-2xl font-semibold mb-4">Order Summary</h2>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-600">Event:</span>
                <span class="font-medium">{{ event.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Ticket Type:</span>
                <span class="font-medium">{{ ticketType.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Price per ticket:</span>
                <span class="font-medium">{{ formatCurrency(ticketType.price) }}</span>
              </div>
              <div class="flex justify-between items-center pt-4">
                <label for="quantity" class="text-gray-600">Quantity:</label>
                <input
                  id="quantity"
                  type="number"
                  v-model.number="quantity"
                  min="1"
                  :max="ticketType.available_quantity"
                  class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>
            <div class="flex justify-between items-center text-xl font-bold pt-6 mt-6 border-t">
              <span>Total:</span>
              <span>{{ formatCurrency(totalAmount) }}</span>
            </div>
          </div>

          <!-- Payment Form / Button -->
          <div>
            <h2 class="text-2xl font-semibold mb-4">Payment Information</h2>
            <p class="text-gray-600 mb-6">
              You will be redirected to a secure payment gateway to complete your purchase.
            </p>

            <button
              @click="proceedToPayment"
              :disabled="paymentProcessing"
              class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <LoaderIcon v-if="paymentProcessing" class="w-5 h-5 animate-spin" />
              <span>{{ paymentProcessing ? 'Redirecting...' : 'Proceed to Payment' }}</span>
            </button>

            <div v-if="paymentError" class="p-4 bg-red-50 border border-red-200 rounded-lg mt-4">
              <div class="flex gap-3">
                <AlertCircleIcon class="w-5 h-5 text-red-600 flex-shrink-0" />
                <p class="text-sm text-red-700">{{ paymentError }}</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center text-gray-500">Event or Ticket Type not found.</div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import { LoaderIcon, AlertCircleIcon } from 'lucide-vue-next'
import eventService from '@/services/eventService' // Assuming direct service call for simplicity
import ticketTypeService from '@/services/ticketTypeService' // Assuming direct service call for simplicity
import orderService from '@/services/orderService' // Assuming direct service call for simplicity
import { formatCurrency } from '@/utils/currency' // Assuming a currency formatter utility

const route = useRoute()

const event = ref<any>(null)
const ticketType = ref<any>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const quantity = ref(1)
const paymentProcessing = ref(false)
const paymentError = ref<string | null>(null)

const eventId = ref<string | null>(null)
const ticketTypeId = ref<string | null>(null)

onMounted(async () => {
  eventId.value = Array.isArray(route.params.eventId) ? route.params.eventId[0] : route.params.eventId
  ticketTypeId.value = Array.isArray(route.params.ticketTypeId) ? route.params.ticketTypeId[0] : route.params.ticketTypeId

  if (!eventId.value || !ticketTypeId.value) {
    error.value = 'Missing event ID or ticket type ID.'
    loading.value = false
    return
  }

  try {
    const eventResponse = await eventService.getById(eventId.value)
    event.value = eventResponse.data

    const ticketTypeResponse = await ticketTypeService.getById(ticketTypeId.value)
    ticketType.value = ticketTypeResponse.data

    // Adjust quantity if it exceeds available
    if (ticketType.value && quantity.value > ticketType.value.available_quantity) {
      quantity.value = ticketType.value.available_quantity
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load event or ticket type details.'
  } finally {
    loading.value = false
  }
})

const totalAmount = computed(() => {
  if (ticketType.value) {
    return quantity.value * ticketType.value.price
  }
  return 0
})

const proceedToPayment = async () => {
  paymentProcessing.value = true
  paymentError.value = null

  if (!event.value || !ticketType.value) {
    paymentError.value = 'Event or ticket type data is missing.'
    paymentProcessing.value = false
    return
  }

  try {
    const orderData = {
      eventId: event.value.id,
      ticketTypeId: ticketType.value.id,
      quantity: quantity.value,
      amount: totalAmount.value
      // Add any other necessary order details
    }
    const orderResponse = await orderService.createOrder(orderData)
    const orderId = orderResponse.data.id

    // Initiate payment
    const paymentResponse = await orderService.initializePayment(orderId, {
      successCallbackUrl: `${window.location.origin}/payment/callback?status=success&orderId=${orderId}`,
      cancelCallbackUrl: `${window.location.origin}/payment/callback?status=cancelled&orderId=${orderId}`
    })

    // Redirect to payment gateway
    if (paymentResponse.data?.redirectUrl) {
      window.location.href = paymentResponse.data.redirectUrl
    } else {
      paymentError.value = 'Payment redirection failed. Please try again.'
    }
  } catch (err: any) {
    paymentError.value = err.response?.data?.message || 'Failed to initiate payment. Please try again.'
  } finally {
    paymentProcessing.value = false
  }
}
</script>

<style scoped>
/* Scoped styles for this component */
</style>
