<template>
  <PublicLayout>
    <div v-if="loading" class="max-w-6xl mx-auto px-4 py-12">
      <div class="animate-pulse space-y-6">
        <div class="h-96 bg-gray-200 rounded-xl"></div>
        <div class="h-12 bg-gray-200 rounded w-3/4"></div>
        <div class="h-64 bg-gray-200 rounded-xl"></div>
      </div>
    </div>

    <div v-else-if="event" class="max-w-6xl mx-auto px-4 py-12">
      <!-- Event Banner with Gallery -->
      <div class="relative h-96 rounded-xl overflow-hidden mb-8">
        <img
          v-if="currentImage"
          :src="getImageUrl(currentImage)"
          :alt="event.title"
          class="w-full h-full object-cover transition-opacity duration-500"
        />
        <div v-else class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
          <CalendarIcon class="w-32 h-32 text-white opacity-50" />
        </div>

        <!-- Gallery Navigation -->
        <div v-if="allImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2">
          <button
            v-for="(img, index) in allImages"
            :key="index"
            @click="currentImageIndex = index"
            :class="[
              'w-3 h-3 rounded-full transition-all',
              currentImageIndex === index ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/75'
            ]"
          ></button>
        </div>

        <!-- Status Badge Overlay -->
        <div class="absolute top-6 right-6">
          <StatusBadge :status="event.status" type="event" class="text-lg" />
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Event Header -->
          <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ event.title }}</h1>
            <div class="flex items-center gap-4 text-gray-600">
              <div class="flex items-center gap-2">
                <BuildingIcon class="w-5 h-5" />
                <span>{{ event.organisateur?.name }}</span>
              </div>
            </div>

            <!-- Social Links -->
            <div v-if="event.social_links && hasSocialLinks" class="flex gap-3 mt-4">
              <a
                v-if="event.social_links.facebook"
                :href="event.social_links.facebook"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors"
                title="Facebook"
              >
                <FacebookIcon class="w-5 h-5" />
              </a>
              <a
                v-if="event.social_links.instagram"
                :href="event.social_links.instagram"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 bg-pink-600 text-white rounded-full hover:bg-pink-700 transition-colors"
                title="Instagram"
              >
                <InstagramIcon class="w-5 h-5" />
              </a>
              <a
                v-if="event.social_links.twitter"
                :href="event.social_links.twitter"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 bg-sky-500 text-white rounded-full hover:bg-sky-600 transition-colors"
                title="Twitter"
              >
                <TwitterIcon class="w-5 h-5" />
              </a>
              <a
                v-if="event.social_links.linkedin"
                :href="event.social_links.linkedin"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 bg-blue-700 text-white rounded-full hover:bg-blue-800 transition-colors"
                title="LinkedIn"
              >
                <LinkedinIcon class="w-5 h-5" />
              </a>
              <a
                v-if="event.social_links.website"
                :href="event.social_links.website"
                target="_blank"
                rel="noopener noreferrer"
                class="p-2 bg-gray-700 text-white rounded-full hover:bg-gray-800 transition-colors"
                title="Website"
              >
                <GlobeIcon class="w-5 h-5" />
              </a>
            </div>
          </div>

          <!-- Event Description -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">About This Event</h2>
            <p class="text-gray-700 whitespace-pre-wrap">{{ event.description || 'No description available.' }}</p>

            <div v-if="event.dress_code" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
              <div class="flex items-center gap-2 text-blue-900">
                <InfoIcon class="w-5 h-5" />
                <span class="font-medium">Dress Code: {{ event.dress_code }}</span>
              </div>
            </div>

            <div v-if="event.allow_reentry" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
              <div class="flex items-center gap-2 text-green-900">
                <CheckCircleIcon class="w-5 h-5" />
                <span class="font-medium">Re-entry allowed</span>
              </div>
            </div>
          </div>

          <!-- Gates Information -->
          <div v-if="event.gates && event.gates.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Entry Points</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="gate in event.gates"
                :key="gate.id"
                class="p-4 border border-gray-200 rounded-lg"
              >
                <div class="flex items-center gap-2 mb-2">
                  <DoorOpenIcon class="w-5 h-5 text-gray-600" />
                  <h3 class="font-semibold text-gray-900">{{ gate.name }}</h3>
                </div>
                <div v-if="gate.location" class="text-sm text-gray-600 mb-2">
                  📍 {{ gate.location }}
                </div>
                <div class="flex items-center gap-2">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-semibold rounded-full',
                      gate.type === 'entry' ? 'bg-green-100 text-green-700' :
                      gate.type === 'exit' ? 'bg-red-100 text-red-700' :
                      'bg-orange-100 text-orange-700'
                    ]"
                  >
                    {{ gate.type === 'entry' ? '🟢 Entry' : gate.type === 'exit' ? '🔴 Exit' : '🟡 Entry/Exit' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Available Tickets -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Ticket Types</h2>

            <div v-if="event.ticket_types && event.ticket_types.length > 0" class="space-y-4">
              <div
                v-for="ticketType in event.ticket_types"
                :key="ticketType.id"
                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-blue-500 transition-colors"
              >
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900">{{ ticketType.name }}</h3>
                  <p v-if="ticketType.description" class="text-sm text-gray-600 mt-1">
                    {{ ticketType.description }}
                  </p>
                  <div class="mt-2 text-sm text-gray-500">
                    {{ ticketType.quantity_available || 0 }} available
                  </div>
                </div>
                <div class="text-right ml-4">
                  <div class="text-2xl font-bold text-gray-900">
                    {{ formatCurrency(ticketType.price) }}
                  </div>
                  <button
                    v-if="!ticketType.quantity_available || ticketType.quantity_available > 0"
                    @click.prevent="selectTicketType(ticketType)"
                    type="button"
                    class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer"
                  >
                    Buy Ticket
                  </button>
                  <span v-else class="text-sm text-red-600 font-medium">Sold Out</span>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              No tickets available at this time
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Event Details Card -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
            <h3 class="font-semibold text-gray-900 mb-4">Event Details</h3>

            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <CalendarIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                <div>
                  <div class="text-sm text-gray-600">Date & Time</div>
                  <div class="font-medium text-gray-900">{{ formatDate(event.start_datetime) }}</div>
                  <div v-if="event.end_datetime" class="text-sm text-gray-600">
                    to {{ formatDate(event.end_datetime) }}
                  </div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <MapPinIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                <div>
                  <div class="text-sm text-gray-600">Location</div>
                  <div class="font-medium text-gray-900">{{ event.location || 'TBA' }}</div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <UsersIcon class="w-5 h-5 text-gray-400 mt-0.5" />
                <div>
                  <div class="text-sm text-gray-600">Capacity</div>
                  <div class="font-medium text-gray-900">{{ event.capacity }} people</div>
                  <div v-if="event.tickets_sold" class="text-sm text-gray-600">
                    {{ event.tickets_sold }} tickets sold
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
              <button
                @click="scrollToTickets"
                class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"
              >
                Get Tickets
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="max-w-2xl mx-auto px-4 py-24 text-center">
      <XCircleIcon class="w-16 h-16 text-red-500 mx-auto mb-4" />
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Event Not Found</h1>
      <p class="text-gray-600 mb-6">The event you're looking for doesn't exist or has been removed.</p>
      <RouterLink to="/" class="text-blue-600 hover:text-blue-700 font-medium">
        Browse all events
      </RouterLink>
    </div>
  </PublicLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import eventService from '@/services/eventService'
import { formatDate, formatCurrency, getImageUrl } from '@/utils/formatters'
import type { Event, TicketType } from '@/types/api'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import {
  CalendarIcon,
  MapPinIcon,
  BuildingIcon,
  UsersIcon,
  InfoIcon,
  XCircleIcon,
  CheckCircleIcon,
  DoorOpenIcon,
  Facebook as FacebookIcon,
  Instagram as InstagramIcon,
  Twitter as TwitterIcon,
  Linkedin as LinkedinIcon,
  Globe as GlobeIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const event = ref<Event | null>(null)
const loading = ref(true)
const currentImageIndex = ref(0)
let imageInterval: number | null = null

const allImages = computed(() => {
  const images: string[] = []
  if (event.value?.image_url) {
    images.push(event.value.image_url)
  }
  if (event.value?.gallery_images && Array.isArray(event.value.gallery_images)) {
    images.push(...event.value.gallery_images)
  }
  return images
})

const currentImage = computed(() => {
  return allImages.value[currentImageIndex.value] || null
})

const hasSocialLinks = computed(() => {
  if (!event.value?.social_links) return false
  const links = event.value.social_links
  return !!(links.facebook || links.instagram || links.twitter || links.linkedin || links.website)
})

onMounted(async () => {
  try {
    const param = route.params.slug as string
    const isUUID = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(param)

    if (isUUID) {
      event.value = await eventService.getPublicById(param)
    } else {
      event.value = await eventService.getPublicBySlug(param)
    }

    // Auto-rotate gallery images
    if (allImages.value.length > 1) {
      imageInterval = window.setInterval(() => {
        currentImageIndex.value = (currentImageIndex.value + 1) % allImages.value.length
      }, 5000)
    }
  } catch (error) {
    console.error('Failed to fetch event:', error)
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  if (imageInterval) {
    clearInterval(imageInterval)
  }
})

function selectTicketType(ticketType: TicketType) {
  if (event.value) {
    router.push({ 
      name: 'checkout', 
      params: { 
        eventId: event.value.id.toString(), 
        ticketTypeId: ticketType.id.toString() 
      } 
    })
  }
}

function scrollToTickets() {
  const ticketsSection = document.querySelector('.ticket-types')
  ticketsSection?.scrollIntoView({ behavior: 'smooth' })
}
</script>
