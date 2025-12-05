<template>
  <PublicLayout>
    <div class="max-w-4xl mx-auto px-4 py-12">
      <!-- Loading State -->
      <div v-if="loading" class="animate-pulse space-y-6">
        <div class="h-32 bg-gray-200 rounded-xl"></div>
        <div class="h-64 bg-gray-200 rounded-xl"></div>
      </div>

      <!-- Success State -->
      <div v-else-if="payment && !error">
        <!-- Success Header -->
        <div class="text-center mb-8">
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <CheckCircleIcon class="w-12 h-12 text-green-600" />
          </div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">🎉 Paiement Réussi !</h1>
          <p class="text-gray-600">Merci pour votre achat. Vos tickets sont prêts!</p>
        </div>

        <!-- Payment Summary -->
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
          <h2 class="text-xl font-semibold mb-4">Détails de l'achat</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <span class="text-sm text-gray-600">Événement</span>
              <p class="font-medium">{{ payment.event.title }}</p>
            </div>
            <div>
              <span class="text-sm text-gray-600">Date</span>
              <p class="font-medium">{{ formatDate(payment.event.start_date) }}</p>
            </div>
            <div>
              <span class="text-sm text-gray-600">Montant payé</span>
              <p class="font-medium">{{ formatCurrency(payment.amount) }} {{ payment.currency }}</p>
            </div>
            <div>
              <span class="text-sm text-gray-600">Nombre de tickets</span>
              <p class="font-medium">{{ payment.ticket_count }}</p>
            </div>
          </div>

          <!-- Ticket Types Summary -->
          <div v-if="payment.ticket_types_summary && payment.ticket_types_summary.length > 0" class="mt-4 pt-4 border-t">
            <p class="text-sm font-medium text-gray-700 mb-2">Types de tickets:</p>
            <div class="flex flex-wrap gap-2">
              <span 
                v-for="(typeSum, index) in payment.ticket_types_summary" 
                :key="index"
                class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm"
              >
                {{ typeSum.count }}x {{ typeSum.type }}
              </span>
            </div>
          </div>
        </div>

        <!-- Tickets List -->
        <div class="space-y-4 mb-8">
          <h2 class="text-xl font-semibold">Vos Tickets</h2>
          <div v-for="ticket in tickets" :key="ticket.id" class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-semibold text-lg">{{ ticket.ticket_type.name }}</p>
                <p class="text-sm text-gray-600">Code: {{ ticket.code }}</p>
                <p class="text-sm">
                  Statut: <span class="text-green-600 font-medium">✅ Payé</span>
                </p>
              </div>
              <div class="flex gap-2">
                <RouterLink 
                  :to="`/public/tickets/${ticket.id}?token=${ticket.magic_link_token}`"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
                >
                  <EyeIcon class="w-4 h-4" />
                  Voir
                </RouterLink>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
          <RouterLink 
            to="/" 
            class="flex-1 py-3 text-center border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium"
          >
            🏠 Retour à l'accueil
          </RouterLink>
        </div>

        <!-- Help Text -->
        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
          <p class="text-sm text-blue-800">
            <strong>💡 Conseils:</strong>
          </p>
          <ul class="text-sm text-blue-700 mt-2 space-y-1 list-disc list-inside">
            <li>Sauvegardez vos tickets sur votre téléphone</li>
            <li>Présentez le QR code à l'entrée de l'événement</li>
            <li>Un email de confirmation a été envoyé à {{ payment.customer.email }}</li>
          </ul>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <XCircleIcon class="w-12 h-12 text-red-600" />
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Erreur</h1>
        <p class="text-gray-600 mb-6">{{ error }}</p>
        <RouterLink 
          to="/" 
          class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Retour à l'accueil
        </RouterLink>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { CheckCircleIcon, EyeIcon, XCircleIcon } from 'lucide-vue-next'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import api from '@/services/api'

const route = useRoute()
const loading = ref(true)
const error = ref('')
const payment = ref<any>(null)
const tickets = ref<any[]>([])

onMounted(async () => {
  try {
    const paymentId = route.params.id as string
    const response = await api.get(`/payments/${paymentId}`)
    
    payment.value = response.data.payment
    tickets.value = response.data.tickets
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Impossible de charger les détails du paiement'
    console.error('Error loading payment:', e)
  } finally {
    loading.value = false
  }
})

function formatDate(dateString: string) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatCurrency(amount: number) {
  return new Intl.NumberFormat('fr-FR').format(amount)
}
</script>
