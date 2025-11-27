<template>
  <PublicLayout>
    <div class="max-w-3xl mx-auto px-4 py-12">
      <h1 class="text-3xl font-bold mb-8">Complete Your Purchase</h1>

      <div v-if="loading" class="animate-pulse space-y-4">
        <div class="h-24 bg-gray-200 rounded"></div>
        <div class="h-48 bg-gray-200 rounded"></div>
      </div>

      <div v-else-if="ticketType" class="space-y-8">
        <!-- Ticket Summary -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
          <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
          <div class="space-y-2">
            <div class="flex justify-between"><span>Ticket:</span><span class="font-medium">{{ ticketType.name }}</span></div>
            <div class="flex justify-between"><span>Price:</span><span>{{ formatCurrency(ticketType.price) }}</span></div>
            <div class="flex justify-between"><span>Quantity:</span>
              <select v-model.number="quantity" class="border rounded px-2 py-1">
                <option v-for="n in Math.min(10, ticketType.quantity_available)" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex justify-between text-xl font-bold pt-4 border-t"><span>Total:</span><span>{{ formatCurrency(ticketType.price * quantity) }}</span></div>
          </div>
        </div>

        <!-- Customer Form -->
        <form @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
          <h2 class="text-xl font-semibold mb-4">Your Information</h2>
          
          <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">First Name *</label><input v-model="form.firstname" required class="w-full border rounded px-3 py-2" /></div>
            <div><label class="block text-sm font-medium mb-1">Last Name *</label><input v-model="form.lastname" required class="w-full border rounded px-3 py-2" /></div>
          </div>
          
          <div><label class="block text-sm font-medium mb-1">Email *</label><input v-model="form.email" type="email" required class="w-full border rounded px-3 py-2" /></div>
          <div><label class="block text-sm font-medium mb-1">Phone *</label><input v-model="form.phone_number" required class="w-full border rounded px-3 py-2" /></div>

          <div v-if="error" class="p-4 bg-red-50 text-red-700 rounded">{{ error }}</div>

          <button type="submit" :disabled="processing" class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50">
            {{ processing ? 'Processing...' : 'Proceed to Payment' }}
          </button>
        </form>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTicketTypes } from '@/composables/useTicketTypes'
import paymentService from '@/services/paymentService'
import { formatCurrency } from '@/utils/formatters'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import type { TicketType } from '@/types/api'

const route = useRoute()
const router = useRouter()
const { fetchTicketType, ticketType, loading } = useTicketTypes()

const quantity = ref(1)
const processing = ref(false)
const error = ref('')
const form = ref({ firstname: '', lastname: '', email: '', phone_number: '' })

onMounted(async () => {
  const id = route.params.ticketTypeId as string
  console.log('Loading ticket type:', id)
  if (id) await fetchTicketType(id)
  console.log('Loaded ticket type:', ticketType.value)
})

const handleSubmit = async () => {
  if (!ticketType.value) return
  processing.value = true
  error.value = ''
  
  try {
    const result = await paymentService.purchaseTicket({
      ticket_type_id: ticketType.value.id.toString(),
      quantity: quantity.value,
      customer: form.value
    })
    
    if (result.payment_url) {
      window.location.href = result.payment_url
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Payment initiation failed'
  } finally {
    processing.value = false
  }
}
</script>
