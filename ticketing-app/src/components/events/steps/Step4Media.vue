<template>
  <div class="step-content">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">🖼️ Médias et Visuels</h2>

    <!-- Banner Upload -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-3">Image principale (Banner)</h3>
      <p class="text-sm text-gray-600 mb-3">Dimensions recommandées: 1200x600px</p>
      
      <div
        @click="triggerBannerUpload"
        @dragover.prevent="isDraggingBanner = true"
        @dragleave.prevent="isDraggingBanner = false"
        @drop.prevent="handleBannerDrop"
        :class="[
          'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-all',
          isDraggingBanner ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-400'
        ]"
      >
        <input
          ref="bannerInput"
          type="file"
          accept="image/*"
          @change="handleBannerSelect"
          class="hidden"
        />
        
        <div v-if="bannerPreview">
          <img :src="bannerPreview" alt="Banner preview" class="max-h-64 mx-auto rounded-lg mb-3" />
          <button
            @click.stop="removeBanner"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Supprimer
          </button>
        </div>
        
        <div v-else>
          <UploadIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
          <p class="text-gray-700 font-medium mb-1">Cliquez ou glissez-déposez une image</p>
          <p class="text-sm text-gray-500">PNG, JPG jusqu'à 5MB</p>
        </div>
      </div>
    </div>

    <!-- Gallery Upload -->
    <div>
      <h3 class="text-lg font-semibold text-gray-900 mb-3">Galerie d'images (max 5)</h3>
      <p class="text-sm text-gray-600 mb-3">Images secondaires pour présenter votre événement</p>
      
      <div
        @click="triggerGalleryUpload"
        @dragover.prevent="isDraggingGallery = true"
        @dragleave.prevent="isDraggingGallery = false"
        @drop.prevent="handleGalleryDrop"
        :class="[
          'border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-all',
          isDraggingGallery ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-400'
        ]"
      >
        <input
          ref="galleryInput"
          type="file"
          accept="image/*"
          multiple
          @change="handleGallerySelect"
          class="hidden"
        />
        
        <div v-if="galleryPreviews.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div
            v-for="(preview, index) in galleryPreviews"
            :key="index"
            class="relative group"
          >
            <img :src="preview" alt="Gallery image" class="w-full h-32 object-cover rounded-lg" />
            <button
              @click.stop="removeGalleryImage(index)"
              class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
            >
              <XIcon class="w-4 h-4" />
            </button>
          </div>
          
          <div
            v-if="galleryPreviews.length < 5"
            class="border-2 border-dashed border-gray-300 rounded-lg h-32 flex items-center justify-center hover:border-blue-400 transition-colors"
          >
            <PlusIcon class="w-8 h-8 text-gray-400" />
          </div>
        </div>
        
        <div v-else>
          <ImageIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
          <p class="text-gray-700 font-medium mb-1">Ajoutez jusqu'à 5 images</p>
          <p class="text-sm text-gray-500">PNG, JPG jusqu'à 5MB chacune</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { UploadIcon, XIcon, PlusIcon, ImageIcon } from 'lucide-vue-next'

const props = defineProps<{
  modelValue: any
}>()

const emit = defineEmits<{
  'update:modelValue': [value: any]
}>()

const bannerInput = ref<HTMLInputElement>()
const galleryInput = ref<HTMLInputElement>()
const isDraggingBanner = ref(false)
const isDraggingGallery = ref(false)
const bannerPreview = ref<string>('')
const galleryPreviews = ref<string[]>([])

const localData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

function triggerBannerUpload() {
  bannerInput.value?.click()
}

function triggerGalleryUpload() {
  if (galleryPreviews.value.length < 5) {
    galleryInput.value?.click()
  }
}

function handleBannerSelect(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    processImage(file, 'banner')
  }
}

function handleBannerDrop(event: DragEvent) {
  isDraggingBanner.value = false
  const file = event.dataTransfer?.files[0]
  if (file) {
    processImage(file, 'banner')
  }
}

function handleGallerySelect(event: Event) {
  const target = event.target as HTMLInputElement
  const files = Array.from(target.files || [])
  files.forEach(file => processImage(file, 'gallery'))
}

function handleGalleryDrop(event: DragEvent) {
  isDraggingGallery.value = false
  const files = Array.from(event.dataTransfer?.files || [])
  files.forEach(file => processImage(file, 'gallery'))
}

function processImage(file: File, type: 'banner' | 'gallery') {
  if (!file.type.match(/image\/(png|jpeg|jpg)/)) {
    alert('Format non supporté. Utilisez PNG ou JPG.')
    return
  }
  
  if (file.size > 5 * 1024 * 1024) {
    alert('Fichier trop volumineux. Maximum 5MB.')
    return
  }
  
  const reader = new FileReader()
  reader.onload = (e) => {
    const result = e.target?.result as string
    if (type === 'banner') {
      bannerPreview.value = result
      localData.value.banner = file
    } else {
      if (galleryPreviews.value.length < 5) {
        galleryPreviews.value.push(result)
        if (!localData.value.gallery) {
          localData.value.gallery = []
        }
        localData.value.gallery.push(file)
      }
    }
  }
  reader.readAsDataURL(file)
}

function removeBanner() {
  bannerPreview.value = ''
  localData.value.banner = null
  if (bannerInput.value) {
    bannerInput.value.value = ''
  }
}

function removeGalleryImage(index: number) {
  galleryPreviews.value.splice(index, 1)
  localData.value.gallery.splice(index, 1)
}

onMounted(() => {
  // Load existing images if editing
  if (localData.value.banner && typeof localData.value.banner === 'string') {
    bannerPreview.value = localData.value.banner
  }
})
</script>
