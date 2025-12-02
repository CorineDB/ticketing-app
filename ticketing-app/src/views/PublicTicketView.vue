<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useScan } from '@/composables/useScan'
import TicketInfoCard from '@/components/scan/TicketInfoCard.vue'
import { AlertTriangleIcon, CheckCircleIcon } from 'lucide-vue-next'

const route = useRoute()
const { scanTicket, ticketInfo, loading, error } = useScan()

const isValid = ref(false)

onMounted(async () => {
  const ticketId = route.params.id as string
  const signature = route.query.sig as string

  if (ticketId && signature) {
    try {
      // We use scanTicket (Level 1) just to fetch and display info
      // This endpoint is public
      await scanTicket(ticketId, signature)
      isValid.value = true
    } catch (e) {
      // Error handled in composable
    }
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
      
      <!-- Header / Status -->
      <div class="p-6 text-center border-b border-gray-100" :class="isValid ? 'bg-green-50' : 'bg-gray-50'">
        <div v-if="loading" class="flex flex-col items-center">
          <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4"></div>
          <h2 class="text-xl font-semibold text-gray-700">Vérification du billet...</h2>
        </div>

        <div v-else-if="error" class="flex flex-col items-center text-red-600">
          <AlertTriangleIcon class="w-16 h-16 mb-2" />
          <h2 class="text-2xl font-bold">Billet Invalide</h2>
          <p class="mt-2 text-sm text-gray-600">{{ error }}</p>
        </div>

        <div v-else-if="ticketInfo" class="flex flex-col items-center text-green-700">
          <CheckCircleIcon class="w-16 h-16 mb-2" />
          <h2 class="text-2xl font-bold">Billet Authentique</h2>
          <p class="mt-2 text-sm text-green-800">Ce billet est valide et enregistré dans notre système.</p>
        </div>
      </div>

      <!-- Ticket Details -->
      <div v-if="ticketInfo && !loading" class="p-6">
        <TicketInfoCard :ticket="ticketInfo" />
        
        <div class="mt-8 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Sécurisé par TicketingApp</p>
        </div>
      </div>

      <!-- Footer Actions -->
      <div v-if="!loading" class="bg-gray-50 p-4 text-center border-t border-gray-100">
         <router-link to="/" class="text-blue-600 font-medium hover:underline">
           Retour à l'accueil
         </router-link>
      </div>

    </div>
  </div>
</template>
