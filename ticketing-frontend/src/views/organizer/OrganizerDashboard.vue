<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4">Organizer Dashboard</h1>
        <p>Welcome, {{ authStore.currentUser?.name }}</p>
      </v-col>
    </v-row>

    <!-- Stats Cards -->
    <v-row>
       <v-col cols="12" md="4">
        <v-card color="blue" theme="dark">
          <v-card-text>
            <div class="text-h5">{{ myEvents.length }}</div>
            <div>Total Events</div>
          </v-card-text>
        </v-card>
      </v-col>
       <v-col cols="12" md="4">
        <v-card color="green" theme="dark">
          <v-card-text>
            <div class="text-h5">{{ totalTicketsSold }}</div>
            <div>Tickets Sold</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Events List -->
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-card-title>My Events</v-card-title>
          <v-card-text>
            <v-data-table
              :headers="headers"
              :items="myEvents"
              :loading="loading"
              class="elevation-1"
            >
              <template v-slot:item.date="{ item }">
                {{ new Date(item.date).toLocaleString() }}
              </template>
              <template v-slot:item.actions="{ item }">
                <v-btn small color="primary" @click="editEvent(item.id)">Edit</v-btn>
                <v-btn small color="secondary" class="ml-2" @click="viewTickets(item.id)">View Tickets</v-btn>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { apiService } from '@/services/apiService';
import type { Event } from '@/types/Event';
import type { Ticket } from '@/types/Ticket';

const authStore = useAuthStore();
const router = useRouter();
const myEvents = ref<Event[]>([]);
const allTickets = ref<Ticket[]>([]); // To calculate stats
const loading = ref(true);

const headers = [
  { title: 'Event Name', key: 'name' },
  { title: 'Date', key: 'date' },
  { title: 'Location', key: 'location' },
  { title: 'Capacity', key: 'capacity' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const totalTicketsSold = computed(() => {
  const eventIds = myEvents.value.map(e => e.id);
  return allTickets.value.filter(t => eventIds.includes(t.eventId) && t.status !== 'pending_payment').length;
});

onMounted(async () => {
  loading.value = true;
  const organizerId = authStore.currentUser?.id;
  if (organizerId) {
    // In a real app, you might have a dedicated endpoint for tickets per organizer
    const [fetchedEvents, fetchedTickets] = await Promise.all([
      apiService.getEventsByOrganizerId(organizerId),
      Promise.all(mockEvents.filter(e => e.organizerId === organizerId).map(e => apiService.getTicketsByEventId(e.id))).then(res => res.flat())
    ]);
    myEvents.value = fetchedEvents;
    allTickets.value = fetchedTickets;
  }
  loading.value = false;
});

function editEvent(eventId: string) {
  router.push({ name: 'organizer-edit-event', params: { id: eventId } });
}

function viewTickets(eventId: string) {
  // We can create a dedicated page for this later
  alert(`Navigating to view tickets for event ${eventId}`);
}
</script>
