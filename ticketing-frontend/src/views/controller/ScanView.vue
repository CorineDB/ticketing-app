<template>
  <v-container class="d-flex justify-center align-center" style="height: 80vh;">
    <v-card width="500">
      <v-card-title class="text-center text-h5">Ticket Scanner</v-card-title>
      
      <v-card-text>
        <!-- Simulated scanner -->
        <v-text-field
          v-model="scannedData"
          label="Paste Scanned QR Code Data"
          placeholder="e.g., ticket-1"
          clearable
          @keyup.enter="checkTicket"
        ></v-text-field>
        <v-btn block color="primary" @click="checkTicket" :loading="loading">Check Ticket</v-btn>
      </v-card-text>

      <!-- Result Display -->
      <div v-if="scannedTicket">
        <v-divider></v-divider>
        <v-card-text>
          <v-alert v-if="scannedTicket.status === 'valid'" type="success" prominent border="start">
            <div class="text-h6">Ticket Valid</div>
            <p><strong>Event:</strong> {{ eventName }}</p>
            <p><strong>Type:</strong> {{ ticketTypeName }}</p>
            <v-btn class="mt-2" small @click="markAsUsed">Mark as 'Used'</v-btn>
          </v-alert>

          <v-alert v-else-if="scannedTicket.status === 'used'" type="warning" prominent border="start">
            <div class="text-h6">Ticket Already Used</div>
            <p>This ticket has already been scanned and admitted.</p>
          </v-alert>

          <v-alert v-else type="error" prominent border="start">
            <div class="text-h6">Ticket Invalid</div>
            <p>This ticket is not valid for entry.</p>
          </v-alert>

        </v-card-text>
      </div>
       <div v-if="error">
          <v-divider></v-divider>
          <v-alert type="error" class="ma-4">{{ error }}</v-alert>
       </div>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { apiService } from '@/services/apiService';
import { mockTickets } from '@/mocks/tickets'; // Direct access for simulation
import { mockEvents } from '@/mocks/events';
import type { Ticket } from '@/types/Ticket';

const scannedData = ref('');
const loading = ref(false);
const error = ref<string | null>(null);
const scannedTicket = ref<Ticket | null>(null);

const eventName = computed(() => {
  if (!scannedTicket.value) return '';
  const event = mockEvents.find(e => e.id === scannedTicket.value?.eventId);
  return event?.name || 'Unknown Event';
});

const ticketTypeName = computed(() => {
  if (!scannedTicket.value) return '';
  const event = mockEvents.find(e => e.id === scannedTicket.value?.eventId);
  const ticketType = event?.ticket_types.find(tt => tt.id === scannedTicket.value?.ticketTypeId);
  return ticketType?.name || 'Unknown Type';
});


async function checkTicket() {
  if (!scannedData.value) return;
  loading.value = true;
  scannedTicket.value = null;
  error.value = null;
  
  // Simulation: In a real app, you'd make a single API call.
  // Here we search the mock data directly.
  await new Promise(res => setTimeout(res, 500)); // Simulate API call
  const ticketId = scannedData.value.trim();
  const foundTicket = mockTickets.find(t => t.id === ticketId);

  if (foundTicket) {
    scannedTicket.value = foundTicket;
  } else {
    error.value = "Ticket ID not found.";
  }
  loading.value = false;
}

function markAsUsed() {
  if (scannedTicket.value) {
    // Simulation: In a real app, this would be an API call
    // apiService.updateTicketStatus(scannedTicket.value.id, 'used')
    const index = mockTickets.findIndex(t => t.id === scannedTicket.value!.id);
    if (index !== -1) {
      mockTickets[index].status = 'used';
      scannedTicket.value.status = 'used'; // Update the local copy
      alert(`Ticket ${scannedTicket.value.id} marked as used. (Simulation)`);
    }
  }
}
</script>
