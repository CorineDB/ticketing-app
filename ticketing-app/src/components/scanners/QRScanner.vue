<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="text-center">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Scan QR Code</h2>

      <!-- Scanner Container -->
      <div class="relative mx-auto max-w-md">
        <div
          v-if="!scanning"
          class="aspect-square bg-gray-100 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:bg-gray-200 transition-colors"
          @click="startScanning"
        >
          <QrCodeIcon class="w-24 h-24 text-gray-400 mb-4" />
          <p class="text-gray-600 font-medium">Click to start scanning</p>
          <p class="text-sm text-gray-500 mt-2">Position QR code within frame</p>
        </div>

        <div v-else class="relative">
          <!-- Video Preview -->
          <div id="qr-reader" class="aspect-square rounded-lg overflow-hidden"></div>

          <!-- Scanning Overlay -->
          <div class="absolute inset-0 pointer-events-none">
            <div class="absolute inset-0 border-4 border-blue-500 rounded-lg animate-pulse"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white bg-black bg-opacity-50 px-4 py-2 rounded-lg">
              <p class="text-sm font-medium">Scanning...</p>
            </div>
          </div>

          <!-- Stop Button -->
          <button
            @click="stopScanning"
            class="absolute bottom-4 left-1/2 -translate-x-1/2 px-6 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700"
          >
            Stop Scanning
          </button>
        </div>
      </div>

      <!-- Manual Input Option -->
      <div class="mt-6">
        <button
          @click="showManualInput = !showManualInput"
          class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-2 mx-auto"
        >
          <KeyboardIcon class="w-4 h-4" />
          {{ showManualInput ? 'Hide' : 'Enter code manually' }}
        </button>

        <div v-if="showManualInput" class="mt-4">
          <input
            v-model="manualCode"
            type="text"
            placeholder="Enter ticket code"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            @keyup.enter="handleManualScan"
          />
          <button
            @click="handleManualScan"
            :disabled="!manualCode || processing"
            class="w-full mt-2 py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ processing ? 'Processing...' : 'Scan Ticket' }}
          </button>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex gap-3">
          <AlertCircleIcon class="w-5 h-5 text-red-600 flex-shrink-0" />
          <p class="text-sm text-red-700 text-left">{{ error }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick, onUnmounted } from 'vue'
import { Html5QrcodeScanner, Html5Qrcode } from 'html5-qrcode'
import {
  QrCodeIcon,
  KeyboardIcon,
  AlertCircleIcon
} from 'lucide-vue-next'

const emit = defineEmits<{
  scan: [qrData: string]
}>()

const scanning = ref(false)
const showManualInput = ref(false)
const manualCode = ref('')
const processing = ref(false)
const error = ref('')

let scanner: Html5QrcodeScanner | null = null

let html5Qr: Html5Qrcode | null = null

async function startScanning() {
  scanning.value = true
  error.value = ''

  await nextTick()

  html5Qr = new Html5Qrcode("qr-reader")

  html5Qr.start(
    { facingMode: "environment" },
    {
      fps: 15,
      qrbox: 300
    },
    (decodedText) => {
      console.log("Scan successful:", decodedText)
      emit('scan', decodedText)
      stopScanning()
      if ('vibrate' in navigator) navigator.vibrate(200)
      //onScanSuccess(decodedText)
    },
    (errorMessage) => {
      onScanError(errorMessage)
    }
  )
}
async function startscanning() {
  scanning.value = true
  error.value = ''

  console.log("Scanning started ...");

  await nextTick() // DOM ready

  // Initialize scanner
  scanner = new Html5QrcodeScanner(
    'qr-reader',
    {
      fps: 15,
      qrbox: { width: 300, height: 300 },
      aspectRatio: 1.0,
      disableFlip: false // si QR est retourné sur papier
    },
    false
  )

  scanner.render(onScanSuccess, onScanError)
}

async function stopScanning() {
  console.log("Scanning stopped.");
  if (html5Qr && html5Qr.isScanning) {
    await html5Qr.stop();
    await html5Qr.clear();
    html5Qr = null
  }
  scanning.value = false
}

function stopscanning() {

  console.log("Scanning stopped.");
  if (scanner) {
    scanner.clear()
    scanner = null
  }
  scanning.value = false
}

function onScanSuccess(decodedText: string) {
  console.log("Scan successful: ", decodedText);
  // Emit scan event
  emit('scan', decodedText)

  // Stop scanning after successful scan
  stopScanning()

  // Optional: Add vibration feedback if supported
  if ('vibrate' in navigator) {
    navigator.vibrate(200)
  }
}

function onScanError(errorMessage: string) {
  // Ignore common scanning errors (no QR in frame, etc.)
  // Only show persistent errors
  console.log("Scan error: ", errorMessage);  
}

function handleManualScan() {
  if (!manualCode.value.trim()) return

  processing.value = true
  error.value = ''

  try {
    emit('scan', manualCode.value.trim())
    manualCode.value = ''
  } catch (err: any) {
    error.value = err.message || 'Failed to process ticket code'
  } finally {
    processing.value = false
  }
}

onUnmounted(() => {
  stopScanning()
})
</script>
