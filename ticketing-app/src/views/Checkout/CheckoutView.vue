<template>
  <PublicLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-7xl mx-auto px-4">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <LoaderIcon class="w-12 h-12 animate-spin mx-auto text-blue-600 mb-4" />
          <p class="text-gray-600">Loading event details...</p>
        </div>

        <!-- Event Not Found -->
        <div v-else-if="!event" class="text-center py-12">
          <AlertCircleIcon class="w-16 h-16 text-red-400 mx-auto mb-4" />
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Event Not Found</h2>
          <p class="text-gray-600">The event you're looking for doesn't exist or is no longer available.</p>
        </div>

        <!-- Checkout Form -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left Side: Form -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Event Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <div class="flex items-start gap-4">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex-shrink-0">
                  <img
                    v-if="event.banner"
                    :src="event.banner"
                    :alt="event.name"
                    class="w-full h-full object-cover rounded-lg"
                  />
                </div>
                <div class="flex-1">
                  <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ event.name }}</h1>
                  <div class="flex items-center gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-1">
                      <CalendarIcon class="w-4 h-4" />
                      <span>{{ formatDate(event.start_date) }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                      <MapPinIcon class="w-4 h-4" />
                      <span>{{ event.venue }}, {{ event.city }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 1: Select Tickets -->
            <div v-if="currentStep === 1" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Select Tickets</h2>

              <div v-if="ticketTypes.length === 0" class="text-center py-8 text-gray-500">
                No tickets available for this event
              </div>

              <div v-else class="space-y-4">
                <div
                  v-for="ticketType in ticketTypes"
                  :key="ticketType.id"
                  class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition-colors"
                >
                  <div class="flex items-center justify-between mb-3">
                    <div>
                      <h3 class="font-semibold text-gray-900">{{ ticketType.name }}</h3>
                      <p v-if="ticketType.description" class="text-sm text-gray-600 mt-1">
                        {{ ticketType.description }}
                      </p>
                    </div>
                    <div class="text-right">
                      <div class="text-xl font-bold text-gray-900">
                        {{ formatCurrency(ticketType.price) }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ ticketType.quantity_available }} left
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <button
                        @click="decreaseQuantity(ticketType.id)"
                        :disabled="getQuantity(ticketType.id) === 0"
                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <MinusIcon class="w-4 h-4" />
                      </button>
                      <span class="text-lg font-medium text-gray-900 w-8 text-center">
                        {{ getQuantity(ticketType.id) }}
                      </span>
                      <button
                        @click="increaseQuantity(ticketType.id)"
                        :disabled="!canIncreaseQuantity(ticketType)"
                        class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <PlusIcon class="w-4 h-4" />
                      </button>
                    </div>
                    <div v-if="ticketType.max_per_order" class="text-sm text-gray-500">
                      Max {{ ticketType.max_per_order }} per order
                    </div>
                  </div>
                </div>
              </div>

              <button
                @click="goToStep(2)"
                :disabled="totalTickets === 0"
                class="w-full mt-6 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
              >
                Continue to Details
              </button>
            </div>

            <!-- Step 2: Buyer Information -->
            <div v-if="currentStep === 2" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Buyer Information</h2>

              <form @submit.prevent="goToStep(3)" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                      First Name <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="first_name"
                      v-model="buyerInfo.first_name"
                      type="text"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>

                  <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                      Last Name <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="last_name"
                      v-model="buyerInfo.last_name"
                      type="text"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                </div>

                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="email"
                    v-model="buyerInfo.email"
                    type="email"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p class="text-xs text-gray-500 mt-1">Your tickets will be sent to this email</p>
                </div>

                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number
                  </label>
                  <input
                    id="phone"
                    v-model="buyerInfo.phone"
                    type="tel"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div class="flex gap-3 pt-4">
                  <button
                    type="button"
                    @click="goToStep(1)"
                    class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                  >
                    Back
                  </button>
                  <button
                    type="submit"
                    class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
                  >
                    Continue to Payment
                  </button>
                </div>
              </form>
            </div>

            <!-- Step 3: Payment -->
            <div v-if="currentStep === 3" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment</h2>

              <div class="space-y-4">
                <!-- Payment Method Selection -->
                <div class="space-y-3">
                  <label class="flex items-center gap-3 p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input
                      v-model="paymentMethod"
                      type="radio"
                      name="payment"
                      value="card"
                      class="w-4 h-4 text-blue-600"
                    />
                    <CreditCardIcon class="w-5 h-5 text-gray-600" />
                    <span class="font-medium text-gray-900">Credit/Debit Card</span>
                  </label>

                  <label class="flex items-center gap-3 p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input
                      v-model="paymentMethod"
                      type="radio"
                      name="payment"
                      value="mobile_money"
                      class="w-4 h-4 text-blue-600"
                    />
                    <SmartphoneIcon class="w-5 h-5 text-gray-600" />
                    <span class="font-medium text-gray-900">Mobile Money</span>
                  </label>
                </div>

                <!-- Payment Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <div class="flex gap-3">
                    <InfoIcon class="w-5 h-5 text-blue-600 flex-shrink-0" />
                    <div class="text-sm text-blue-900">
                      <p class="font-medium mb-1">Secure Payment</p>
                      <p>Your payment information is encrypted and secure.</p>
                    </div>
                  </div>
                </div>

                <div class="flex gap-3 pt-4">
                  <button
                    type="button"
                    @click="goToStep(2)"
                    class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                  >
                    Back
                  </button>
                  <button
                    @click="processPayment"
                    :disabled="!paymentMethod || processing"
                    class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium flex items-center justify-center gap-2"
                  >
                    <LoaderIcon v-if="processing" class="w-5 h-5 animate-spin" />
                    <span>{{ processing ? 'Processing...' : `Pay ${formatCurrency(totalAmount)}` }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Side: Order Summary -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>

              <div class="space-y-3 mb-4">
                <div
                  v-for="(quantity, ticketTypeId) in selectedTickets"
                  :key="ticketTypeId"
                  class="flex items-center justify-between text-sm"
                >
                  <div>
                    <div class="font-medium text-gray-900">
                      {{ getTicketTypeName(Number(ticketTypeId)) }}
                    </div>
                    <div class="text-gray-500">Qty: {{ quantity }}</div>
                  </div>
                  <div class="font-medium text-gray-900">
                    {{ formatCurrency(getTicketTypePrice(Number(ticketTypeId)) * quantity) }}
                  </div>
                </div>
              </div>

              <div v-if="totalTickets > 0" class="border-t border-gray-200 pt-4 space-y-2">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-600">Subtotal</span>
                  <span class="text-gray-900">{{ formatCurrency(totalAmount) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-600">Fees</span>
                  <span class="text-gray-900">{{ formatCurrency(0) }}</span>
                </div>
                <div class="flex items-center justify-between text-lg font-bold border-t border-gray-200 pt-2">
                  <span>Total</span>
                  <span>{{ formatCurrency(totalAmount) }}</span>
                </div>
              </div>

              <div v-else class="text-center py-8 text-gray-500">
                No tickets selected
              </div>

              <!-- Step Indicator -->
              <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between mb-2">
                  <div
                    v-for="step in 3"
                    :key="step"
                    :class="[
                      'w-full h-2 rounded-full',
                      step === 1 ? '' : 'ml-2',
                      currentStep >= step ? 'bg-blue-600' : 'bg-gray-200'
                    ]"
                  />
                </div>
                <div class="text-sm text-gray-600 text-center">
                  Step {{ currentStep }} of 3
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEvents } from '@/composables/useEvents'
import { useTicketTypes } from '@/composables/useTicketTypes'
import { useCheckout } from '@/composables/useCheckout'
import { formatDate, formatCurrency } from '@/utils/formatters'
import type { Event, TicketType } from '@/types/api'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import {
  LoaderIcon,
  AlertCircleIcon,
  CalendarIcon,
  MapPinIcon,
  MinusIcon,
  PlusIcon,
  CreditCardIcon,
  SmartphoneIcon,
  InfoIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const { fetchEvent } = useEvents()
const { ticketTypes, fetchTicketTypes } = useTicketTypes()
const { processCheckout } = useCheckout()

const event = ref<Event | null>(null)
const loading = ref(true)
const processing = ref(false)
const currentStep = ref(1)
const selectedTickets = ref<Record<number, number>>({})
const paymentMethod = ref<'card' | 'mobile_money' | ''>('')

const buyerInfo = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: ''
})

const totalTickets = computed(() => {
  return Object.values(selectedTickets.value).reduce((sum, qty) => sum + qty, 0)
})

const totalAmount = computed(() => {
  return Object.entries(selectedTickets.value).reduce((sum, [ticketTypeId, quantity]) => {
    const price = getTicketTypePrice(Number(ticketTypeId))
    return sum + (price * quantity)
  }, 0)
})

onMounted(async () => {
  await loadEvent()
})

async function loadEvent() {
  loading.value = true
  try {
    const eventId = Number(route.params.eventId)
    event.value = await fetchEvent(eventId)
    if (event.value) {
      await fetchTicketTypes({ event_id: eventId, status: 'active' })
    }
  } catch (error) {
    console.error('Failed to load event:', error)
  } finally {
    loading.value = false
  }
}

function getQuantity(ticketTypeId: number): number {
  return selectedTickets.value[ticketTypeId] || 0
}

function increaseQuantity(ticketTypeId: number) {
  const current = getQuantity(ticketTypeId)
  selectedTickets.value[ticketTypeId] = current + 1
}

function decreaseQuantity(ticketTypeId: number) {
  const current = getQuantity(ticketTypeId)
  if (current > 0) {
    if (current === 1) {
      delete selectedTickets.value[ticketTypeId]
    } else {
      selectedTickets.value[ticketTypeId] = current - 1
    }
  }
}

function canIncreaseQuantity(ticketType: TicketType): boolean {
  const current = getQuantity(ticketType.id)
  if (current >= ticketType.quantity_available) return false
  if (ticketType.max_per_order && current >= ticketType.max_per_order) return false
  return true
}

function getTicketTypeName(ticketTypeId: number): string {
  const ticketType = ticketTypes.value.find(tt => tt.id === ticketTypeId)
  return ticketType?.name || ''
}

function getTicketTypePrice(ticketTypeId: number): number {
  const ticketType = ticketTypes.value.find(tt => tt.id === ticketTypeId)
  return ticketType?.price || 0
}

function goToStep(step: number) {
  currentStep.value = step
}

async function processPayment() {
  processing.value = true
  try {
    const orderData = {
      event_id: event.value?.id,
      tickets: selectedTickets.value,
      buyer_info: {
        name: `${buyerInfo.value.first_name} ${buyerInfo.value.last_name}`,
        email: buyerInfo.value.email,
        phone: buyerInfo.value.phone
      },
      payment_method: paymentMethod.value
    }

    const result = await processCheckout(orderData)

    // Redirect to success page
    router.push(`/checkout/success/${result.order_id}`)
  } catch (error: any) {
    console.error('Payment failed:', error)
    alert(error.message || 'Payment failed. Please try again.')
  } finally {
    processing.value = false
  }
}
</script>
