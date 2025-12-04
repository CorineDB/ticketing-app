<template>
  <div class="qr-upload">
    <!-- Upload Area -->
    <div
      v-if="!imagePreview"
      @click="triggerFileInput"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      :class="[
        'upload-area',
        isDragging ? 'dragging' : ''
      ]"
    >
      <UploadIcon class="w-12 h-12 text-gray-400 mb-3" />
      <p class="text-lg font-medium text-gray-700 mb-1">Téléverser un QR Code</p>
      <p class="text-sm text-gray-500 mb-3">Cliquez ou glissez-déposez une image</p>
      <p class="text-xs text-gray-400">PNG, JPG, JPEG (max 5MB)</p>
      
      <input
        ref="fileInput"
        type="file"
        accept="image/png,image/jpeg,image/jpg"
        @change="handleFileSelect"
        class="hidden"
      />
    </div>

    <!-- Image Preview & Processing -->
    <div v-else class="preview-area">
      <div class="relative">
        <img :src="imagePreview" alt="QR Code Preview" class="preview-image" />
        <button
          @click="reset"
          class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600"
        >
          <XIcon class="w-5 h-5" />
        </button>
      </div>

      <!-- Processing Status -->
      <div v-if="processing" class="mt-4 text-center">
        <div class="w-8 h-8 border-3 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-2"></div>
        <p class="text-sm text-gray-600">Décodage du QR code...</p>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-700">{{ error }}</p>
        <button @click="reset" class="mt-2 text-sm text-red-600 hover:text-red-700 font-medium">
          Réessayer
        </button>
      </div>

      <!-- Success Message -->
      <div v-if="success" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-700">✓ QR code détecté avec succès!</p>
      </div>
    </div>

    <!-- Hidden canvas for image processing -->
    <canvas ref="canvas" class="hidden"></canvas>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import jsQR from 'jsqr'
import { UploadIcon, XIcon } from 'lucide-vue-next'

const emit = defineEmits<{
  scanned: [qrData: string]
  error: [message: string]
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const canvas = ref<HTMLCanvasElement | null>(null)
const imagePreview = ref<string | null>(null)
const isDragging = ref(false)
const processing = ref(false)
const error = ref<string | null>(null)
const success = ref(false)

function triggerFileInput() {
  fileInput.value?.click()
}

function handleFileSelect(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    processFile(file)
  }
}

function handleDrop(event: DragEvent) {
  isDragging.value = false
  const file = event.dataTransfer?.files[0]
  if (file) {
    processFile(file)
  }
}

async function processFile(file: File) {
  // Validate file type
  if (!file.type.match(/image\/(png|jpeg|jpg)/)) {
    error.value = 'Format de fichier non supporté. Utilisez PNG, JPG ou JPEG.'
    emit('error', error.value)
    return
  }

  // Validate file size (5MB max)
  if (file.size > 5 * 1024 * 1024) {
    error.value = 'Fichier trop volumineux. Maximum 5MB.'
    emit('error', error.value)
    return
  }

  // Reset states
  error.value = null
  success.value = false
  processing.value = true

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target?.result as string
    decodeQRCode(e.target?.result as string)
  }
  reader.readAsDataURL(file)
}

function decodeQRCode(imageSrc: string) {
  const img = new Image()
  img.onload = () => {
    if (!canvas.value) return

    const ctx = canvas.value.getContext('2d')
    if (!ctx) return

    // Set canvas size to image size
    canvas.value.width = img.width
    canvas.value.height = img.height

    // Draw image on canvas
    ctx.drawImage(img, 0, 0)

    // Get image data
    const imageData = ctx.getImageData(0, 0, canvas.value.width, canvas.value.height)

    // Decode QR code
    const code = jsQR(imageData.data, imageData.width, imageData.height, {
      inversionAttempts: 'dontInvert'
    })

    processing.value = false

    if (code) {
      success.value = true
      emit('scanned', code.data)
      
      // Auto-reset after 2 seconds
      setTimeout(() => {
        reset()
      }, 2000)
    } else {
      error.value = 'Aucun QR code détecté dans cette image. Assurez-vous que le QR code est visible et net.'
      emit('error', error.value)
    }
  }

  img.onerror = () => {
    processing.value = false
    error.value = 'Erreur lors du chargement de l\'image.'
    emit('error', error.value)
  }

  img.src = imageSrc
}

function reset() {
  imagePreview.value = null
  error.value = null
  success.value = false
  processing.value = false
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}
</script>

<style scoped>
.upload-area {
  border: 2px dashed #cbd5e0;
  border-radius: 12px;
  padding: 3rem 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f7fafc;
}

.upload-area:hover {
  border-color: #4299e1;
  background: #ebf8ff;
}

.upload-area.dragging {
  border-color: #3182ce;
  background: #bee3f8;
  transform: scale(1.02);
}

.preview-area {
  text-align: center;
}

.preview-image {
  max-width: 100%;
  max-height: 400px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>
