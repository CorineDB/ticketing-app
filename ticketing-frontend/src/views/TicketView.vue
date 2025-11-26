<template>
  <v-container>
    <v-card v-if="ticket" class="mx-auto my-12" max-width="374">
      <v-card-title class="text-center">{{ event?.name }}</v-card-title>
      <v-card-subtitle class="text-center">{{ new Date(event?.date || '').toLocaleString() }}</v-card-subtitle>
      
      <v-img height="250" :src="ticket.qr_code_url" class="my-4"></v-img>

      <v-card-text>
        <div class="text-h6 text-center">{{ ticketType?.name }}</div>
        <div v-if="ticket.participantName" class="text-center">{{ ticket.participantName }}</div>
      </v-card-text>

      <v-divider class="mx-4"></v-divider>

      <v-card-actions class="d-flex justify-center">
        <v-chip :color="statusColor" text-color="white">
          {{ ticket.status.toUpperCase() }}
        </v-chip>
      </v-card-actions>
    </v-card>
    <div v-else-if="loading" class="text-center">
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
      <p>Loading ticket...</p>
    </div>
    <v-alert v-else type="error">
      Ticket not found or invalid link.
    </v-alert>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { apiService } from '@/services/apiService';
import type { Ticket } from '@/types/Ticket';
import type { Event, TicketType } from '@/types/Event';

const route = useRoute();
const ticket = ref<Ticket | null>(null);
const event = ref<Event | null>(null);
const loading = ref(true);

const ticketId = route.params.id as string;
const token = route.query.token as string;

const ticketType = computed((): TicketType | undefined => {
  return event.value?.ticket_types.find(tt => tt.id === ticket.value?.ticketTypeId);
});

const statusColor = computed(() => {
  switch (ticket.value?.status) {
    case 'valid': return 'success';
    case 'used': return 'warning';
    case 'invalid': return 'error';
    case 'pending_payment': return 'info';
    default: return 'grey';
  }
});

onMounted(async () => {
  if (!token) {
    loading.value = false;
    return;
  }
  
  const fetchedTicket = await apiService.getTicketByIdAndToken(ticketId, token);
  
  if (fetchedTicket) {
    ticket.value = fetchedTicket;
    const fetchedEvent = await apiService.getEventById(fetchedTicket.eventId);
    if(fetchedEvent) {
      event.value = fetchedEvent;
    }
  }
  loading.value = false;
});
</script>
