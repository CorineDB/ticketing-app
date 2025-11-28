<template>
  <RouterLink
    :to="`/tickets/${ticket.code}`"
    class="block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
  >
    <!-- Event Banner (Small) -->
    <div class="relative h-24 bg-gradient-to-br from-blue-500 to-purple-500">
      <img
        v-if="ticket.event?.image_url"
        :src="getImageUrl(ticket.event.image_url)"
        :alt="ticket.event.title"
        class="w-full h-full object-cover"
      />
      <div class="absolute top-2 right-2">
        <TicketStatusBadge :status="ticket.status" />
      </div>
    </div>

    <!-- Ticket Info -->
    <div class="p-4">
      <h3 class="font-bold text-gray-900 mb-1 truncate">
        {{ ticket.event?.title }}
      </h3>
      <p class="text-sm text-gray-600 mb-3">{{ ticket.ticket_type?.name }}</p>

      <!-- Holder Info -->
      <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
        <UserIcon class="w-4 h-4" />
        <span class="truncate">{{ ticket.holder_name }}</span>
      </div>

      <!-- Date -->
      <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
        <CalendarIcon class="w-4 h-4" />
        <span>{{ formatDate(ticket.event?.start_datetime) }}</span>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between pt-3 border-t border-gray-200">
        <code class="text-xs font-mono text-gray-500">{{ ticket.code }}</code>
        <Badge
          v-if="ticket.status === 'paid'"
          variant="success"
        >
          Ready to use
        </Badge>
        <Badge
          v-else-if="ticket.status === 'in'"
          variant="primary"
        >
          Checked in
        </Badge>
      </div>
    </div>
  </RouterLink>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import type { Ticket } from '@/types/api'
import { formatDate, getImageUrl } from '@/utils/formatters'
import TicketStatusBadge from './TicketStatusBadge.vue'
import Badge from '@/components/common/Badge.vue'
import {
  UserIcon,
  CalendarIcon
} from 'lucide-vue-next'

defineProps<{
  ticket: Ticket
}>()
</script>
