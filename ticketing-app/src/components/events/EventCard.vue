<template>
  <div class="block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group relative">
    <!-- Event Banner -->
    <RouterLink :to="`/dashboard/events/${event.id}`" class="block">
      <div class="relative h-48 bg-gradient-to-br from-blue-500 to-purple-500 overflow-hidden">
        <img
          v-if="event.image_url"
          :src="getImageUrl(event.image_url)"
          :alt="event.title"
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
        <div v-else class="w-full h-full flex items-center justify-center">
          <CalendarIcon class="w-16 h-16 text-white opacity-50" />
        </div>

        <!-- Status Badge -->
        <div class="absolute top-3 left-3">
          <StatusBadge :status="event.status" type="event" />
        </div>

        <!-- Share Button (Top Right - always visible) -->
        <div class="absolute top-3 right-3 z-10" @click.stop>
          <ShareEventButton
            :event-slug="event.slug"
            :event-title="event.title"
            :event-description="event.description"
          />
        </div>
      </div>
    </RouterLink>

    <!-- Event Info -->
    <div class="p-6">
      <RouterLink :to="`/dashboard/events/${event.id}`" class="block">
        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
          {{ event.title }}
        </h3>

        <div class="space-y-2 mb-4">
          <!-- Date & Time -->
          <div class="flex items-center text-sm text-gray-600">
            <CalendarIcon class="w-4 h-4 mr-2 flex-shrink-0" />
            <span>{{ formatDate(event.start_datetime) }} at {{ formatTime(event.start_datetime) }}</span>
          </div>

          <!-- Venue -->
          <div v-if="event.location" class="flex items-center text-sm text-gray-600">
            <MapPinIcon class="w-4 h-4 mr-2 flex-shrink-0" />
            <span class="truncate">{{ event.location }}</span>
          </div>

          <!-- Organization -->
          <div v-if="event.organisateur" class="flex items-center text-sm text-gray-600">
            <BuildingIcon class="w-4 h-4 mr-2 flex-shrink-0" />
            <span class="truncate">{{ event.organisateur.name }}</span>
          </div>
        </div>
      </RouterLink>

      <!-- Stats & Actions -->
      <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <div class="flex items-center gap-4 text-sm">
          <div class="flex items-center gap-1 text-gray-600">
            <TicketIcon class="w-4 h-4" />
            <span>{{ event.tickets_sold || 0 }}/{{ event.capacity }}</span>
          </div>
          <div v-if="event.revenue" class="flex items-center gap-1 text-gray-600">
            <DollarSignIcon class="w-4 h-4" />
            <span>{{ formatCurrency(event.revenue || 0) }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2" @click.stop>
          <!-- Publish Button (only for drafts) -->
          <button
            v-if="event.status === 'draft'"
            @click="$emit('publish', event.id)"
            class="p-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
            title="Publier l'événement"
          >
            <RocketIcon class="w-4 h-4" />
          </button>

          <!-- Edit Button -->
          <RouterLink
            :to="`/dashboard/events/${event.id}/edit`"
            class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            title="Modifier"
          >
            <EditIcon class="w-4 h-4" />
          </RouterLink>

          <!-- More Actions Dropdown -->
          <div class="relative">
            <button
              @click="showMoreMenu = !showMoreMenu"
              class="p-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
              title="Plus d'actions"
            >
              <MoreVerticalIcon class="w-4 h-4" />
            </button>

            <!-- Dropdown Menu -->
            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="showMoreMenu"
                v-click-outside="closeMoreMenu"
                class="absolute right-0 bottom-full mb-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
              >
                <a
                  :href="`/events/${event.slug}`"
                  target="_blank"
                  class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                >
                  <ExternalLinkIcon class="w-4 h-4" />
                  <span>Voir la page publique</span>
                </a>
                <button
                  @click="handleDelete"
                  class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                >
                  <TrashIcon class="w-4 h-4" />
                  <span>Supprimer</span>
                </button>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import type { Event } from '@/types/api'
import { formatDate, formatCurrency, formatTime, getImageUrl } from '@/utils/formatters'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ShareEventButton from '@/components/events/ShareEventButton.vue'
import {
  CalendarIcon,
  MapPinIcon,
  BuildingIcon,
  TicketIcon,
  DollarSignIcon,
  EditIcon,
  TrashIcon,
  RocketIcon,
  MoreVerticalIcon,
  ExternalLinkIcon
} from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    event: Event
    showCTA?: boolean
  }>(),
  {
    showCTA: true
  }
)

const emit = defineEmits<{
  publish: [eventId: string]
  delete: [eventId: string]
}>()

const showMoreMenu = ref(false)

function closeMoreMenu() {
  showMoreMenu.value = false
}

function handleDelete() {
  emit('delete', props.event.id)
  closeMoreMenu()
}

// Click outside directive
const vClickOutside = {
  mounted(el: any, binding: any) {
    el.clickOutsideEvent = (evt: MouseEvent) => {
      if (!(el === evt.target || el.contains(evt.target as Node))) {
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
