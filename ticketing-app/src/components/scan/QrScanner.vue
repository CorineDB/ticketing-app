<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Html5Qrcode } from 'html5-qrcode'
import { XIcon } from 'lucide-vue-next'

const emit = defineEmits<{
  scanned: [qrData: string]
  error: [error: string]
  close: []
}>()

const scanning = ref(false)
const loading = ref(true)
let html5QrCode: Html5Qrcode | null = null

async function startScanner() {
  scanning.value = true
  loading.value = true

  try {
    // Ensure element exists
    const element = document.getElementById('qr-reader')
    if (!element) return

    html5QrCode = new Html5Qrcode('qr-reader')

    await html5QrCode.start(
      { facingMode: 'environment' }, // Caméra arrière
      {
        fps: 10,
        qrbox: { width: 250, height: 250 }
      },
      onScanSuccess,
      onScanFailure
    )
    loading.value = false
  } catch (err: any) {
    console.error('Scanner error:', err)
    emit('error', 'Impossible d\'accéder à la caméra. Vérifiez les permissions.')
    scanning.value = false
    loading.value = false
  }
}

function onScanSuccess(decodedText: string) {
  console.log('QR Code détecté:', decodedText)
  stopScanner()
  emit('scanned', decodedText)
}

function onScanFailure(error: string) {
  // Ignorer les erreurs de scan en cours (trop verbeux)
}

async function stopScanner() {
  if (html5QrCode && html5QrCode.isScanning) {
    await html5QrCode.stop()
    html5QrCode.clear()
  }
  html5QrCode = null
  scanning.value = false
}

function close() {
  stopScanner()
  emit('close')
}

onMounted(() => {
  // Delay start to ensure DOM is ready
  setTimeout(() => {
    startScanner()
  }, 100)
})

onUnmounted(() => {
  stopScanner()
})
</script>

<template>
  <div class="qr-scanner-overlay">
    <div class="scanner-modal">
      <div class="scanner-header">
        <h2>Scanner un Ticket</h2>
        <button @click="close" class="btn-close">
          <XIcon class="w-6 h-6" />
        </button>
      </div>

      <div class="scanner-viewport">
        <div id="qr-reader" class="qr-reader-box"></div>
        <div v-if="loading" class="scanner-loading">
          <div class="spinner"></div>
          <p>Démarrage de la caméra...</p>
        </div>
      </div>

      <div class="scanner-footer">
        <p>📷 Pointez la caméra vers le QR code</p>
        <button @click="close" class="btn-cancel">Annuler</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.qr-scanner-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  z-index: 2000;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.scanner-modal {
  background: white;
  width: 100%;
  max-width: 500px;
  border-radius: 16px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}

.scanner-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #2a2a2a;
  color: white;
}

.scanner-header h2 {
  margin: 0;
  font-size: 1.25rem;
}

.btn-close {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 0.5rem;
}

.scanner-viewport {
  position: relative;
  background: black;
  min-height: 300px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.qr-reader-box {
  width: 100%;
}

.scanner-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #000;
  color: white;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.scanner-footer {
  padding: 1.5rem;
  text-align: center;
  background: white;
}

.btn-cancel {
  margin-top: 1rem;
  width: 100%;
  padding: 1rem;
  background: #f1f3f5;
  color: #333;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}
</style>
