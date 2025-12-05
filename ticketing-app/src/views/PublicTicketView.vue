<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import ticketService from '@/services/ticketService'
import { 
  AlertTriangleIcon, 
  CheckCircleIcon, 
  CopyIcon, 
  CheckIcon,
  DownloadIcon,
  QrCodeIcon,
  CalendarIcon,
  MapPinIcon,
  UserIcon,
  MailIcon,
  PhoneIcon,
  ArrowLeftIcon
} from 'lucide-vue-next'

interface Ticket {
  id: string
  code: string
  status: string
  buyer_name: string
  buyer_email: string
  buyer_phone?: string
  ticket_type: {
    id: string
    name: string
    price: number
  }
  event: {
    id: string
    title: string
    start_datetime: string
    end_datetime: string
    location: string
    description?: string
  }
  qr_code_url?: string
  magic_link_token?: string
}

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

// Full ticket view mode (with token or signature)
const loading = ref(true)
const loadingQR = ref(false)
const error = ref<string | null>(null)
const ticket = ref<Ticket | null>(null)
const copied = ref(false)
const qrCodeUrl = ref<string | null>(null)
const isValid = ref(false)


// Detect mode based on query params


onMounted(async () => {
  const ticketId = route.params.id as string
  const signature = route.query.sig as string
  const token = route.query.token as string
  
  // If user is authenticated and in scan mode, redirect appropriately
  if (signature && authStore.isAuthenticated) {
    // Check if user is an agent (scanner role)
    const isAgent = authStore.user?.type === 'agent-de-controle'
    
    if (isAgent) {
      // Redirect to dashboard (scanner is integrated in ScannerDashboard)
      router.push({
        name: 'dashboard',
        query: {
          ticket_id: ticketId,
          qr_hmac: signature
        }
      })
    } else {
      // Redirect to ticket details page in dashboard
      router.push({
        name: 'ticket-details',
        params: { id: ticketId }
      })
    }
    return
  }
  
  // For non-authenticated users, show full ticket view with either sig or token
  if (signature) {
    // Scan mode - fetch ticket using signature
    if (!ticketId || !signature) {
      error.value = 'Lien invalide: Paramètres manquants'
      loading.value = false
      return
    }

    try {
      const response = await api.get(`/public/tickets/${ticketId}?sig=${signature}`)
      ticket.value = response.data
      isValid.value = true
      
      if (response.data.magic_link_token) {
        await loadQRCode(ticketId, response.data.magic_link_token)
      }
    } catch (e: any) {
      error.value = e.response?.data?.error || e.response?.data?.message || 'Impossible de charger le ticket'
      console.error('Error loading ticket:', e)
    } finally {
      loading.value = false
    }
  } else if (token) {
    // Token mode - fetch ticket using token
    if (!ticketId || !token) {
      error.value = 'Lien invalide: Paramètres manquants'
      loading.value = false
      return
    }

    try {
      const response = await api.get(`/public/tickets/${ticketId}?token=${token}`)
      ticket.value = response.data
      isValid.value = true
      
      await loadQRCode(ticketId, token)
    } catch (e: any) {
      error.value = e.response?.data?.error || e.response?.data?.message || 'Impossible de charger le ticket'
      console.error('Error loading ticket:', e)
    } finally {
      loading.value = false
    }
  } else {
    error.value = 'Lien invalide: Paramètres manquants'
    loading.value = false
  }
})

async function loadQRCode(ticketId: string, token: string) {
  loadingQR.value = true
  try {
    const blob = await ticketService.downloadQR(ticketId, token)
    qrCodeUrl.value = URL.createObjectURL(blob)
  } catch (e) {
    console.error('Failed to load QR code:', e)
  } finally {
    loadingQR.value = false
  }
}

async function downloadQRCode() {
  if (!ticket.value?.id || !ticket.value?.magic_link_token) return
  
  try {
    const blob = await ticketService.downloadQR(ticket.value.id, ticket.value.magic_link_token)
    
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `ticket-${ticket.value.code || ticket.value.id}-qr.png`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Failed to download QR code:', error)
    alert('Échec du téléchargement. Veuillez réessayer.')
  }
}

function copyCode() {
  if (!ticket.value?.code) return
  
  navigator.clipboard.writeText(ticket.value.code)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2000)
}

function formatDate(dateString: string) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    weekday: 'long',
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

<template>
  <!-- FULL TICKET VIEW: For non-authenticated users or authenticated users with token -->
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
      
      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-2xl shadow-xl p-12 text-center">
        <div class="flex flex-col items-center">
          <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-6"></div>
          <h2 class="text-2xl font-semibold text-gray-700">Chargement du billet...</h2>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-white rounded-2xl shadow-xl p-12 text-center">
        <AlertTriangleIcon class="w-20 h-20 mb-4 text-red-500 mx-auto" />
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Erreur</h2>
        <p class="text-gray-600 text-lg">{{ error }}</p>
        <router-link 
          to="/" 
          class="inline-flex items-center gap-2 mt-8 px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition shadow-md"
        >
          <ArrowLeftIcon class="w-4 h-4" />
          Retour à l'accueil
        </router-link>
      </div>

      <!-- Ticket Content -->
      <div v-else-if="ticket" class="space-y-6">
        
        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-10 text-center text-white">
            <div class="flex justify-center mb-4">
              <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                <CheckCircleIcon class="w-12 h-12" />
              </div>
            </div>
            <h1 class="text-3xl font-bold mb-3">{{ ticket.event.title }}</h1>
            <div class="inline-flex items-center gap-3 px-5 py-2.5 bg-white/20 backdrop-blur-sm rounded-lg">
              <code class="text-xl font-mono font-bold">{{ ticket.code }}</code>
              <button
                @click="copyCode"
                class="p-1.5 hover:bg-white/20 rounded transition"
                :title="copied ? 'Copié!' : 'Copier le code'"
              >
                <CheckIcon v-if="copied" class="w-5 h-5" />
                <CopyIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-2 gap-6">
          
          <!-- QR Code Card -->
          <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 text-center">QR Code d'entrée</h2>
            
            <div class="flex flex-col items-center">
              <!-- QR Display -->
              <div class="bg-gray-50 p-6 rounded-xl border-2 border-gray-200 mb-6">
                <img
                  v-if="qrCodeUrl"
                  :src="qrCodeUrl"
                  alt="Ticket QR Code"
                  class="w-72 h-72 rounded-lg"
                />
                <div v-else-if="loadingQR" class="w-72 h-72 flex items-center justify-center">
                  <div class="flex flex-col items-center gap-3">
                    <div class="w-10 h-10 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="text-sm text-gray-600 font-medium">Chargement...</p>
                  </div>
                </div>
                <div v-else class="w-72 h-72 flex items-center justify-center">
                  <QrCodeIcon class="w-20 h-20 text-gray-300" />
                </div>
              </div>
              
              <!-- Download Button -->
              <button
                v-if="qrCodeUrl"
                @click="downloadQRCode"
                class="w-full py-4 px-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition flex items-center justify-center gap-3 shadow-lg hover:shadow-xl"
              >
                <DownloadIcon class="w-5 h-5" />
                Télécharger le QR Code
              </button>
              
              <p class="mt-4 text-center text-sm text-gray-500">
                Présentez ce code à l'entrée de l'événement
              </p>
            </div>
          </div>

          <!-- Details Card -->
          <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6">
            
            <!-- Event Details -->
            <div>
              <h2 class="text-xl font-bold text-gray-900 mb-4">Détails de l'événement</h2>
              
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <CalendarIcon class="w-5 h-5 text-blue-600" />
                  </div>
                  <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Date</p>
                    <p class="text-gray-900 font-medium">{{ formatDate(ticket.event.start_datetime) }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <MapPinIcon class="w-5 h-5 text-red-600" />
                  </div>
                  <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Lieu</p>
                    <p class="text-gray-900 font-medium">{{ ticket.event.location }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200"></div>

            <!-- Ticket Type -->
            <div>
              <h2 class="text-xl font-bold text-gray-900 mb-4">Votre billet</h2>
              
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-5 border border-blue-200">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-xs text-gray-600 uppercase tracking-wide font-semibold mb-1">Type</p>
                    <p class="text-xl font-bold text-gray-900">{{ ticket.ticket_type.name }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-gray-600 uppercase tracking-wide font-semibold mb-1">Prix</p>
                    <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(ticket.ticket_type.price) }} <span class="text-sm">XOF</span></p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200"></div>

            <!-- Buyer Info -->
            <div>
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Titulaire</h3>
              
              <div class="space-y-3">
                <div class="flex items-center gap-3">
                  <UserIcon class="w-4 h-4 text-gray-400 flex-shrink-0" />
                  <span class="text-gray-900">{{ ticket.buyer_name }}</span>
                </div>
                <div class="flex items-center gap-3">
                  <MailIcon class="w-4 h-4 text-gray-400 flex-shrink-0" />
                  <span class="text-gray-900 truncate">{{ ticket.buyer_email }}</span>
                </div>
                <div v-if="ticket.buyer_phone" class="flex items-center gap-3">
                  <PhoneIcon class="w-4 h-4 text-gray-400 flex-shrink-0" />
                  <span class="text-gray-900">{{ ticket.buyer_phone }}</span>
                </div>
              </div>
            </div>

            <!-- Status Badge -->
            <div class="pt-4">
              <span 
                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold"
                :class="{
                  'bg-green-100 text-green-800 ring-2 ring-green-200': ticket.status === 'paid',
                  'bg-blue-100 text-blue-800 ring-2 ring-blue-200': ticket.status === 'issued',
                  'bg-yellow-100 text-yellow-800 ring-2 ring-yellow-200': ticket.status === 'pending',
                  'bg-gray-100 text-gray-800 ring-2 ring-gray-200': ticket.status === 'used'
                }"
              >
                {{ ticket.status === 'paid' ? '✅ Payé' : 
                   ticket.status === 'issued' ? '📝 Émis' : 
                   ticket.status === 'pending' ? '⏳ En attente' : 
                   '✔️ Utilisé' }}
              </span>
            </div>
          </div>

        </div>

        <!-- Footer Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6 text-center">
          <router-link 
            to="/" 
            class="inline-flex items-center gap-2 px-6 py-3 text-gray-700 hover:text-gray-900 font-medium transition"
          >
            <ArrowLeftIcon class="w-4 h-4" />
            Retour à l'accueil
          </router-link>
          <p class="mt-4 text-xs text-gray-400 uppercase tracking-wide font-semibold">Sécurisé par TicketingApp</p>
        </div>

      </div>

    </div>
  </div>
</template>
