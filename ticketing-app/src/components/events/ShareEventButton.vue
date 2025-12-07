<template>
  <div class="relative inline-block">
    <!-- Share Button -->
    <button
      @click="toggleShareMenu"
      class="p-2 bg-white text-gray-700 rounded-lg hover:bg-gray-50 shadow-sm border border-gray-200 transition-colors"
      :class="{ 'bg-blue-50 text-blue-600': showMenu }"
      title="Partager l'événement"
    >
      <Share2Icon class="w-5 h-5" />
    </button>

    <!-- Share Menu -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showMenu"
        v-click-outside="closeMenu"
        class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
      >
        <div class="p-3 border-b border-gray-200">
          <h3 class="text-sm font-semibold text-gray-900">Partager l'événement</h3>
        </div>

        <div class="p-2">
          <!-- Copy Link -->
          <button
            @click="copyLink"
            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <div class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-lg">
              <CopyIcon class="w-4 h-4 text-gray-600" />
            </div>
            <span class="flex-1 text-left">{{ copied ? 'Lien copié !' : 'Copier le lien' }}</span>
            <CheckIcon v-if="copied" class="w-4 h-4 text-green-600" />
          </button>

          <div class="my-2 border-t border-gray-200"></div>

          <!-- Social Media Buttons -->
          <button
            @click="shareOn('whatsapp')"
            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <div class="w-8 h-8 flex items-center justify-center bg-green-100 rounded-lg">
              <MessageCircleIcon class="w-4 h-4 text-green-600" />
            </div>
            <span class="flex-1 text-left">WhatsApp</span>
          </button>

          <button
            @click="shareOn('facebook')"
            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <div class="w-8 h-8 flex items-center justify-center bg-blue-100 rounded-lg">
              <FacebookIcon class="w-4 h-4 text-blue-600" />
            </div>
            <span class="flex-1 text-left">Facebook</span>
          </button>

          <button
            @click="shareOn('telegram')"
            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <div class="w-8 h-8 flex items-center justify-center bg-blue-100 rounded-lg">
              <SendIcon class="w-4 h-4 text-blue-500" />
            </div>
            <span class="flex-1 text-left">Telegram</span>
          </button>

          <button
            @click="shareOn('twitter')"
            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <div class="w-8 h-8 flex items-center justify-center bg-sky-100 rounded-lg">
              <TwitterIcon class="w-4 h-4 text-sky-500" />
            </div>
            <span class="flex-1 text-left">Twitter / X</span>
          </button>

          <button
            @click="shareOn('email')"
            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <div class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-lg">
              <MailIcon class="w-4 h-4 text-gray-600" />
            </div>
            <span class="flex-1 text-left">Email</span>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  Share2Icon,
  CopyIcon,
  CheckIcon,
  MessageCircleIcon,
  FacebookIcon,
  SendIcon,
  TwitterIcon,
  MailIcon
} from 'lucide-vue-next'

const props = defineProps<{
  eventSlug: string
  eventTitle: string
  eventDescription?: string
}>()

const showMenu = ref(false)
const copied = ref(false)

const publicUrl = computed(() => {
  return `${window.location.origin}/events/${props.eventSlug}`
})

const toggleShareMenu = () => {
  showMenu.value = !showMenu.value
}

const closeMenu = () => {
  showMenu.value = false
}

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(publicUrl.value)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (error) {
    console.error('Failed to copy link:', error)
  }
}

const shareOn = (platform: string) => {
  const url = encodeURIComponent(publicUrl.value)
  const title = encodeURIComponent(props.eventTitle)
  const description = encodeURIComponent(props.eventDescription || '')

  let shareUrl = ''

  switch (platform) {
    case 'whatsapp':
      shareUrl = `https://wa.me/?text=${title}%20${url}`
      break
    case 'facebook':
      shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`
      break
    case 'telegram':
      shareUrl = `https://t.me/share/url?url=${url}&text=${title}`
      break
    case 'twitter':
      shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`
      break
    case 'email':
      shareUrl = `mailto:?subject=${title}&body=${description}%0A%0A${url}`
      break
  }

  if (shareUrl) {
    window.open(shareUrl, '_blank', 'width=600,height=400')
    closeMenu()
  }
}

// Click outside directive
const vClickOutside = {
  mounted(el: any, binding: any) {
    el.clickOutsideEvent = (event: Event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el: any) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>
