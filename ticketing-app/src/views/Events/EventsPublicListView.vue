<template>
  <PublicLayout>
    <div class="max-w-7xl mx-auto px-4 py-12">
      <h1 class="text-4xl font-bold text-gray-900 mb-8">Upcoming Events</h1>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="animate-pulse">
          <div class="h-48 bg-gray-200 rounded-t-xl"></div>
          <div class="p-4 space-y-3 bg-white rounded-b-xl">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-3 bg-gray-200 rounded"></div>
          </div>
        </div>
      </div>

      <div v-else-if="events.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <RouterLink
          v-for="event in events"
          :key="event.id"
          :to="{ name: 'event-public', params: { slug: event.slug || event.id } }"
          class="bg-white rounded-xl shadow-sm border hover:shadow-lg transition-shadow overflow-hidden"
        >
          <div class="h-48 bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
            <img v-if="event.image_url" :src="getImageUrl(event.image_url)" :alt="event.title" class="w-full h-full object-cover" />
            <CalendarIcon v-else class="w-16 h-16 text-white opacity-50" />
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ event.title }}</h3>
            <div class="space-y-2 text-sm text-gray-600">
              <div class="flex items-center gap-2">
                <CalendarIcon class="w-4 h-4" />
                <span>{{ formatDate(event.start_datetime) }}</span>
              </div>
              <div class="flex items-center gap-2">
                <MapPinIcon class="w-4 h-4" />
                <span>{{ event.location }}</span>
              </div>
            </div>
          </div>
        </RouterLink>
      </div>

      <div v-else class="text-center py-12">
        <CalendarIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-xl font-medium text-gray-900 mb-2">No events available</h3>
        <p class="text-gray-600">Check back soon for upcoming events!</p>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useEvents } from '@/composables/useEvents'
import { formatDate, getImageUrl } from '@/utils/formatters'
import PublicLayout from '@/components/layout/PublicLayout.vue'
import { CalendarIcon, MapPinIcon } from 'lucide-vue-next'

const { events, loading, fetchEvents } = useEvents()

onMounted(async () => {
  await fetchEvents()
  console.log('Events loaded:', events.value)
})
</script>
