<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

onMounted(async () => {
  const ticketId = route.query.t as string
  const signature = route.query.sig as string

  // Check for required params
  if (!ticketId || !signature) {
    // If params missing, redirect to home or error
    router.replace('/')
    return
  }

  // Determine redirection based on auth state
  if (authStore.isAuthenticated) {
    // User is logged in
    // Check if user has 'agent-de-controle' role/permission
    if (authStore.user?.role?.slug === 'agent-de-controle' || authStore.isSuperAdmin) {
      // Redirect to Agent Scan Process View with params
      router.replace({
        name: 'ScanProcess', // Changed from ScannerDashboard
        query: { t: ticketId, sig: signature }
      })
    } else {
      // User logged in but NOT an agent (e.g. Organizer, Participant)
      // Redirect to Public Ticket View (or a "My Ticket" view if it's theirs?)
      // For now, treat as public view fallback
      router.replace({
        name: 'PublicTicketView',
        params: { id: ticketId }, // Or query depending on route definition
        query: { sig: signature }
      })
    }
  } else {
    // User NOT logged in
    // Redirect to Public Ticket View
    router.replace({
      name: 'PublicTicketView',
      params: { id: ticketId },
      query: { sig: signature }
    })
  }
})
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="text-center">
      <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
      <p class="text-gray-600 font-medium">Redirection en cours...</p>
    </div>
  </div>
</template>
