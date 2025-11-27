<template>
  <PublicLayout>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-12">
      <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-lg text-center">
        <div v-if="loading" class="text-center text-gray-500">
          <LoaderIcon class="w-12 h-12 animate-spin mx-auto mb-4 text-blue-500" />
          <h1 class="text-2xl font-bold text-gray-900 mb-2">Processing Payment...</h1>
          <p class="text-gray-600">Please do not close this page.</p>
        </div>
        <div v-else-if="paymentStatus === 'success'">
          <CheckCircleIcon class="w-12 h-12 mx-auto mb-4 text-green-500" />
          <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
          <p class="text-gray-600 mb-6">Your tickets have been successfully purchased.</p>
          <RouterLink
            :to="{ name: 'tickets' }"
            class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            View My Tickets
          </RouterLink>
        </div>
        <div v-else-if="paymentStatus === 'cancelled'">
          <XCircleIcon class="w-12 h-12 mx-auto mb-4 text-red-500" />
          <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Cancelled</h1>
          <p class="text-gray-600 mb-6">Your payment was cancelled. You can try again.</p>
          <RouterLink
            :to="{ name: 'events' }"
            class="w-full py-3 px-4 bg-gray-300 text-gray-800 rounded-lg font-medium hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Back to Events
          </RouterLink>
        </div>
        <div v-else-if="paymentStatus === 'failed'">
          <AlertCircleIcon class="w-12 h-12 mx-auto mb-4 text-red-500" />
          <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h1>
          <p class="text-gray-600 mb-6">
            There was an issue processing your payment. Please try again or contact support.
          </p>
          <RouterLink
            :to="{ name: 'events' }"
            class="w-full py-3 px-4 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          >
            Try Again
          </RouterLink>
        </div>
        <div v-else>
          <QuestionMarkCircleIcon class="w-12 h-12 mx-auto mb-4 text-gray-500" />
          <h1 class="text-2xl font-bold text-gray-900 mb-2">Unknown Payment Status</h1>
          <p class="text-gray-600 mb-6">
            We are unable to determine the status of your payment. Please contact support.
          </p>
          <RouterLink
            :to="{ name: 'dashboard' }"
            class="w-full py-3 px-4 bg-gray-300 text-gray-800 rounded-lg font-medium hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Go to Dashboard
          </RouterLink>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import {
  LoaderIcon,
  CheckCircleIcon,
  XCircleIcon,
  AlertCircleIcon,
  QuestionMarkCircleIcon
} from 'lucide-vue-next'
import orderService from '@/services/orderService' // Assuming this service can verify payment status

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const paymentStatus = ref<'success' | 'cancelled' | 'failed' | 'unknown' | null>(null)
const orderId = ref<string | null>(null)

onMounted(async () => {
  const status = route.query.status as string
  orderId.value = route.query.orderId as string

  if (status) {
    paymentStatus.value = status as any
    // Potentially call a backend endpoint to finalize/verify payment here if needed
    // Example: const verificationResult = await orderService.verifyPayment(orderId.value, status);
    // Based on verificationResult, set paymentStatus.value
  } else {
    paymentStatus.value = 'unknown'
  }

  loading.value = false
})
</script>

<style scoped>
/* Scoped styles for this component */
</style>
