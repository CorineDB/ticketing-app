<template>
  <v-container fluid>
    <!-- Event Header -->
    <v-row>
      <v-col>
        <v-card>
          <v-card-title class="text-h4">{{ event?.name }}</v-card-title>
          <v-card-subtitle>{{ new Date(event?.date || '').toLocaleString() }} - {{ event?.location }}</v-card-subtitle>
        </v-card>
      </v-col>
    </v-row>

    <v-row>
      <!-- Ticket Types -->
      <v-col cols="12" md="4">
        <v-card>
          <v-card-title>Ticket Types</v-card-title>
          <v-list lines="two">
            <v-list-item
              v-for="ticketType in event?.ticket_types"
              :key="ticketType.id"
              :title="ticketType.name"
              :subtitle="`Price: ${ticketType.price} | Payment URL: ${ticketType.payment_url || 'N/A'}`"
            ></v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- Sold Tickets -->
      <v-col cols="12" md="8">
        <v-card>
          <v-card-title>Sold Tickets</v-card-title>
          <v-card-text>
            <v-data-table
              :headers="ticketHeaders"
              :items="tickets"
              :loading="loading"
              class="elevation-1"
            >
              <template v-slot:item.status="{ item }">
                <v-chip :color="getStatusColor(item.status)" dark>{{ item.status }}</v-chip>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { apiService } from '@/services/apiService';
import type { Event } from '@/types/Event';
import type { Ticket, TicketStatus } from '@/types/Ticket';

const props = defineProps<{
  id: string; // Event ID from route
}>();

const event = ref<Event | null>(null);
const tickets = ref<Ticket[]>([]);
const loading = ref(true);

const ticketHeaders = [
  { title: 'Ticket ID', key: 'id' },
  { title: 'Participant Name', key: 'participantName' },
  { title: 'Participant Email', key: 'participantEmail' },
  { title: 'Status', key: 'status' },
];

onMounted(async () => {
  loading.value = true;
  const [fetchedEvent, fetchedTickets] = await Promise.all([
    apiService.getEventById(props.id),
    apiService.getTicketsByEventId(props.id),
  ]);

  if (fetchedEvent) {
    event.value = fetchedEvent;
  }
  if (fetchedTickets) {
    tickets.value = fetchedTickets;
  }
  loading.value = false;
});

function getStatusColor(status: TicketStatus) {
  switch (status) {
    case 'valid': return 'success';
    case 'used': return 'orange';
    case 'invalid': return 'error';
    case 'pending_payment': return 'info';
    default: return 'grey';
  }
}
</script>
