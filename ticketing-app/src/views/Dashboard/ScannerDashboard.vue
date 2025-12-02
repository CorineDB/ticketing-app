<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useScan } from '@/composables/useScan'
import TicketInfoCard from '@/components/scan/TicketInfoCard.vue'
import CountdownTimer from '@/components/scan/CountdownTimer.vue'
import ScanResult from '@/components/scan/ScanResult.vue'
import QrScanner from '@/components/scan/QrScanner.vue'
import { ScanIcon, LogOutIcon } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()

const showScanner = ref(false)
const showTicketModal = ref(false)

const {
  status,
  ticketInfo,
  expiresIn,
  error,
  loading,
  scanResult,
  scanTicket,
  confirmEntry,
  reset
} = useScan()

// Check for query params on mount (External Scan flow)
onMounted(async () => {
  const ticketId = route.query.t as string
  const signature = route.query.sig as string

  if (ticketId && signature) {
    await handleExternalScan(ticketId, signature)
  }
})

async function handleExternalScan(ticketId: string, signature: string) {
  // Clear query params to avoid re-triggering on refresh (optional, but cleaner)
  // router.replace({ query: {} }) 
  
  showTicketModal.value = true
  try {
    await scanTicket(ticketId, signature)
  } catch (e) {
    // Error handled in composable
  }
}

function openScanner() {
  showScanner.value = true
}

async function handleQrScanned(qrData: string) {
  showScanner.value = false

  try {
    let ticketId, signature

    // Try parsing as URL first
    try {
      const url = new URL(qrData)
      ticketId = url.searchParams.get('t')
      signature = url.searchParams.get('sig')
    } catch (e) {
      // Not a URL, maybe JSON? (Legacy support)
      try {
        const data = JSON.parse(qrData)
        ticketId = data.ticket_id
        signature = data.qr_hmac || data.sig
      } catch (jsonErr) {
        throw new Error('Format QR non reconnu')
      }
    }

    if (!ticketId || !signature) {
      throw new Error('QR code invalide ou incomplet')
    }

    showTicketModal.value = true
    await scanTicket(ticketId, signature)

  } catch (err: any) {
    console.error('Erreur scan:', err)
    // Error will be shown in modal via status/error state
  }
}

async function handleConfirm(action: 'in' | 'out' = 'in') {
  await confirmEntry(action)
}

function handleClose() {
  showTicketModal.value = false
  reset()
  // If came from external link, clear query params
  if (route.query.t) {
     router.replace({ query: {} })
  }
}

function handleNewScan() {
  handleClose()
  // Small delay to allow modal to close before opening scanner
  setTimeout(() => {
    openScanner()
  }, 300)
}
</script>

<template>
  <div class="scanner-dashboard p-6 max-w-4xl mx-auto">
    <div class="dashboard-header flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Agent</h1>
        <p class="text-gray-600">Scannez les billets pour valider l'entrée</p>
      </div>
      <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-lg font-medium">
        Porte Principale
      </div>
    </div>

    <!-- Main Action Area -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <button @click="openScanner" 
        class="flex flex-col items-center justify-center p-8 bg-blue-600 text-white rounded-xl shadow-lg hover:bg-blue-700 transition-all transform hover:scale-105">
        <ScanIcon class="w-12 h-12 mb-4" />
        <span class="text-xl font-bold">SCANNER UN TICKET</span>
      </button>
      
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold mb-4 text-gray-800">Statistiques Session</h3>
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-green-50 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-700">--</div>
            <div class="text-sm text-green-600">Entrées</div>
          </div>
          <div class="bg-red-50 p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-red-700">--</div>
            <div class="text-sm text-red-600">Refus</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Scans (Placeholder) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="p-4 border-b border-gray-200 bg-gray-50">
        <h3 class="font-semibold text-gray-700">Historique Récent</h3>
      </div>
      <div class="p-8 text-center text-gray-500">
        Les derniers scans apparaîtront ici
      </div>
    </div>

    <!-- Scanner Widget -->
    <QrScanner
      v-if="showScanner"
      @scanned="handleQrScanned"
      @error="(msg) => console.error(msg)"
      @close="showScanner = false"
    />

    <!-- Ticket Processing Modal -->
    <div v-if="showTicketModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-75 backdrop-blur-sm" @click="handleClose">
      <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl" @click.stop>
        
        <!-- Loading State -->
        <div v-if="loading" class="p-12 text-center">
          <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-lg font-medium text-gray-600">Vérification du billet...</p>
        </div>

        <!-- Error State (Level 1) -->
        <div v-else-if="status === 'error' && !ticketInfo" class="p-8 text-center">
          <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-4xl">✕</span>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Erreur de Scan</h2>
          <p class="text-gray-600 mb-6">{{ error }}</p>
          <button @click="handleNewScan" class="w-full py-3 bg-gray-900 text-white rounded-xl font-semibold hover:bg-gray-800">
            Réessayer
          </button>
        </div>

        <!-- Success/Confirm Result (Level 2) -->
        <div v-else-if="status === 'success' || (status === 'error' && scanResult)" class="p-6">
          <ScanResult :result="scanResult!" @new-scan="handleNewScan" />
        </div>

        <!-- Ticket Info & Action (Level 1 Success) -->
        <div v-else-if="ticketInfo" class="p-0">
          <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Billet Détecté</h2>
            <button @click="handleClose" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
          </div>

          <div class="p-6">
            <CountdownTimer :seconds="expiresIn" />
            
            <TicketInfoCard :ticket="ticketInfo" class="mb-6" />

            <div class="grid grid-cols-1 gap-3">
              <button 
                @click="handleConfirm('in')" 
                class="w-full py-4 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold text-lg shadow-md transition-colors flex items-center justify-center gap-2"
                :disabled="expiresIn <= 0"
              >
                <ScanIcon class="w-6 h-6" />
                AUTORISER L'ENTRÉE
              </button>
              
              <button 
                @click="handleClose" 
                class="w-full py-3 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl font-semibold transition-colors"
              >
                REFUSER / ANNULER
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>