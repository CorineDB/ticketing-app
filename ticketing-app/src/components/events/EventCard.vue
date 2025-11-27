<template>
  <RouterLink
    :to="`/events/${event.id}`"
    class="block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group"
  >
    <!-- Event Banner -->
    <div class="relative h-48 bg-gradient-to-br from-blue-500 to-purple-500 overflow-hidden">
      <img
        v-if="event.banner"
        :src="event.banner"
        :alt="event.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <CalendarIcon class="w-16 h-16 text-white opacity-50" />
      </div>

      <!-- Status Badge -->
      <div class="absolute top-3 right-3">
        <StatusBadge :status="event.status" type="event" />
      </div>
    </div>

    <!-- Event Info -->
    <div class="p-6">
      <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
        {{ event.name }}
      </h3>

      <div class="space-y-2 mb-4">
        <!-- Date & Time -->
        <div class="flex items-center text-sm text-gray-600">
          <CalendarIcon class="w-4 h-4 mr-2 flex-shrink-0" />
          <span>{{ formatDate(event.start_date) }} at {{ event.start_time }}</span>
        </div>

        <!-- Venue -->
        <div class="flex items-center text-sm text-gray-600">
          <MapPinIcon class="w-4 h-4 mr-2 flex-shrink-0" />
          <span class="truncate">{{ event.venue }}, {{ event.city }}</span>
        </div>

        <!-- Organization -->
        <div v-if="event.organization" class="flex items-center text-sm text-gray-600">
          <BuildingIcon class="w-4 h-4 mr-2 flex-shrink-0" />
          <span class="truncate">{{ event.organization.name }}</span>
        </div>
      </div>

      <!-- Stats -->
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

        <!-- CTA Badge -->
        <Badge
          v-if="showCTA"
          :variant="event.status === 'published' ? 'primary' : 'secondary'"
        >
          {{ getCtaText() }}
        </Badge>
      </div>
    </div>
  </RouterLink>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import type { Event } from '@/types/api'
import { formatDate, formatCurrency } from '@/utils/formatters'
import StatusBadge from '@/components/common/StatusBadge.vue'
import Badge from '@/components/common/Badge.vue'
import {
  CalendarIcon,
  MapPinIcon,
  BuildingIcon,
  TicketIcon,
  DollarSignIcon
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

const getCtaText = () => {
  if (props.event.status === 'published') {
    return 'Book Now'
  } else if (props.event.status === 'ongoing') {
    return 'Happening Now'
  } else if (props.event.status === 'completed') {
    return 'Ended'
  }
  return 'View Details'
}
</script>
