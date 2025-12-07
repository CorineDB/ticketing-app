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
import { Html5QrcodeScanner, Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode'
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

// We only use Html5Qrcode for custom UI, not Html5QrcodeScanner
let html5Qr: Html5Qrcode | null = null

async function startScanning() {
  scanning.value = true
  error.value = ''

  await nextTick()

  const readerEl = document.getElementById("qr-reader")
  if (!readerEl) {
    console.error("Reader element not found")
    scanning.value = false
    return
  }

  // Cleanup existing instance if any
  if (html5Qr) {
    try {
      if (html5Qr.isScanning) await html5Qr.stop()
      html5Qr.clear()
    } catch (e) {
      console.warn("Error cleaning up previous scanner:", e)
    }
    html5Qr = null
  }

  const size = Math.min(readerEl.clientWidth, readerEl.clientHeight, 300)

  try {
    html5Qr = new Html5Qrcode("qr-reader")

    await html5Qr.start(
      { facingMode: "environment" },
      {
        fps: 10,
        qrbox: { width: size, height: size },
        aspectRatio: 1.0
      },
      (decodedText) => {
        onScanSuccess(decodedText)
      },
      (errorMessage) => {
        onScanError(errorMessage)
      }
    )
  } catch (err: any) {
    console.error("Failed to start scanner:", err)
    error.value = "Could not start camera. " + (err.message || "")
    scanning.value = false
  }
}

async function stopScanning() {
  console.log("Stopping scanner...");
  if (html5Qr) {
    try {
      if (html5Qr.isScanning) {
        await html5Qr.stop()
      }
      html5Qr.clear()
    } catch (err) {
      console.warn("Error stopping scanner:", err)
    }
    html5Qr = null
  }
  scanning.value = false
}

function onScanSuccess(decodedText: string) {
  console.log("Scan successful:", decodedText);
  
  // Vibrate if supported
  if (typeof navigator !== 'undefined' && navigator.vibrate) {
    navigator.vibrate(200)
  }
  
  emit('scan', decodedText)
  stopScanning()
}

function onScanError(errorMessage: string) {
  // Filter out common "scanning..." errors to keep logs clean
  const ignoredErrors = [
    "No barcode or QR code detected",
    "NotFoundException",
    "No MultiFormat Readers were able to detect the code"
  ];

  if (ignoredErrors.some(err => errorMessage.includes(err))) {
    return;
  }

  console.warn("Scan error:", errorMessage);  
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
