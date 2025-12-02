<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useScan } from '@/composables/useScan'
import TicketInfoCard from '@/components/scan/TicketInfoCard.vue'
import CountdownTimer from '@/components/scan/CountdownTimer.vue'
import ScanResult from '@/components/scan/ScanResult.vue'
import { ScanIcon, ArrowLeftIcon } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()

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

onMounted(async () => {
  const ticketId = route.query.t as string
  const signature = route.query.sig as string

  if (!ticketId || !signature) {
    router.replace({ name: 'ScannerDashboard' })
    return
  }

  try {
    await scanTicket(ticketId, signature)
  } catch (e) {
    // Error handled in composable
  }
})

async function handleConfirm(action: 'in' | 'out' = 'in') {
  await confirmEntry(action)
}

function handleCancel() {
  router.push({ name: 'ScannerDashboard' })
}

function handleNewScan() {
  reset()
  router.push({ name: 'ScannerDashboard' })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-4 sm:p-6">
    <div class="max-w-lg mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <button @click="handleCancel" class="flex items-center text-gray-600 hover:text-gray-900 mb-4">
          <ArrowLeftIcon class="w-5 h-5 mr-2" />
          Retour au Dashboard
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Traitement du Billet</h1>
      </div>

      <!-- Content -->
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        
        <!-- Loading State -->
        <div v-if="loading" class="p-12 text-center">
          <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-lg font-medium text-gray-600">Vérification du billet...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="status === 'error' && !ticketInfo" class="p-8 text-center">
          <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-4xl">✕</span>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Erreur de Scan</h2>
          <p class="text-gray-600 mb-6">{{ error }}</p>
          <button @click="handleNewScan" class="w-full py-3 bg-gray-900 text-white rounded-xl font-semibold hover:bg-gray-800">
            Retourner au Dashboard
          </button>
        </div>

        <!-- Success/Confirm Result -->
        <div v-else-if="status === 'success' || (status === 'error' && scanResult)" class="p-6">
          <ScanResult :result="scanResult!" @new-scan="handleNewScan" />
        </div>

        <!-- Ticket Info & Action -->
        <div v-else-if="ticketInfo" class="p-0">
          <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800 text-green-600 flex items-center gap-2">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span>
              Billet Valide
            </h2>
          </div>

          <div class="p-6">
            <CountdownTimer :seconds="expiresIn" />
            
            <TicketInfoCard :ticket="ticketInfo" class="mb-6 border border-gray-100 shadow-sm" />

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
                @click="handleCancel" 
                class="w-full py-3 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-xl font-semibold transition-colors"
              >
                Annuler
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>
