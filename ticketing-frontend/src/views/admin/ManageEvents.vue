<template>
  <v-container fluid>
    <v-card>
      <v-card-title>Manage All Events</v-card-title>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="events"
          :loading="loading"
          class="elevation-1"
        >
          <template v-slot:item.date="{ item }">
            {{ new Date(item.date).toLocaleString() }}
          </template>
           <template v-slot:item.organizer="{ item }">
            {{ getOrganizerName(item.organizerId) }}
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { apiService } from '@/services/apiService';
import type { Event } from '@/types/Event';
import type { User } from '@/types/User';

const events = ref<Event[]>([]);
const users = ref<User[]>([]);
const loading = ref(true);

const headers = [
  { title: 'Event Name', key: 'name' },
  { title: 'Date', key: 'date' },
  { title: 'Location', key: 'location' },
  { title: 'Organizer', key: 'organizer' },
  { title: 'Capacity', key: 'capacity' },
];

onMounted(async () => {
  loading.value = true;
  const [fetchedEvents, fetchedUsers] = await Promise.all([
    apiService.getEvents(),
    apiService.getUsers()
  ]);
  events.value = fetchedEvents;
  users.value = fetchedUsers;
  loading.value = false;
});

const getOrganizerName = (organizerId: string) => {
  return users.value.find(user => user.id === organizerId)?.name || 'Unknown';
};
</script>
