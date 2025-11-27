<template>
  <DashboardLayout>
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Ticket Scanner</h1>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- QR Scanner area -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold mb-4">Scan Ticket</h2>
          <QRScanner @scan-success="handleScanSuccess" @scan-error="handleScanError" />
          <div v-if="loading" class="mt-4 text-center text-blue-600">Scanning...</div>
          <div v-if="errorMessage" class="mt-4 text-center text-red-600">
            Error: {{ errorMessage }}
          </div>
        </div>

        <!-- Scan Result display -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold mb-4">Scan Result</h2>
          <ScanResult v-if="scanResult" :result="scanResult" />
          <div v-else class="text-center text-gray-500">
            Awaiting scan...
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import QRScanner from '@/components/scanners/QRScanner.vue' // This component needs to be created
import ScanResult from '@/components/scanners/ScanResult.vue' // This component needs to be created
import { useScanner } from '@/composables/useScanner' // Assuming useScanner provides loading/error states and a scan function

const { scanTicket, loading, error } = useScanner()

const scanResult = ref<any>(null) // Will hold the result from useScanner or backend
const errorMessage = ref<string | null>(null)

const handleScanSuccess = async (scannedCode: string) => {
  errorMessage.value = null
  scanResult.value = null // Clear previous result

  try {
    const result = await scanTicket(scannedCode)
    scanResult.value = result // Assuming scanTicket returns the processed ticket info
  } catch (err: any) {
    errorMessage.value = err.message || 'Failed to process scan.'
  }
}

const handleScanError = (err: any) => {
  scanResult.value = null
  errorMessage.value = err.message || 'QR Scan failed.'
}
</script>

<style scoped>
/* Scoped styles for this component */
</style>
