<template>
  <v-container>
    <v-card>
      <v-card-title>{{ formTitle }}</v-card-title>
      <v-form @submit.prevent="saveEvent">
        <v-card-text>
          <v-text-field v-model="editableEvent.name" label="Event Name" required></v-text-field>
          <v-text-field v-model="editableEvent.date" label="Event Date" type="datetime-local" required></v-text-field>
          <v-text-field v-model="editableEvent.location" label="Location" required></v-text-field>
          <v-text-field v-model.number="editableEvent.capacity" label="Capacity" type="number" required></v-text-field>
          <v-text-field v-model="editableEvent.dress_code" label="Dress Code (Optional)"></v-text-field>
          
          <v-divider class="my-4"></v-divider>
          <h3 class="text-h6 mb-2">Ticket Types</h3>
          <div v-for="(ticketType, index) in editableEvent.ticket_types" :key="index" class="d-flex align-center mb-2">
            <v-text-field v-model="ticketType.name" label="Ticket Name" dense class="mr-2"></v-text-field>
            <v-text-field v-model.number="ticketType.price" label="Price" type="number" dense class="mr-2"></v-text-field>
            <v-text-field v-model="ticketType.payment_url" label="Payment URL" dense class="mr-2"></v-text-field>
            <v-btn icon="mdi-delete" size="small" color="error" @click="removeTicketType(index)"></v-btn>
          </div>
          <v-btn color="secondary" @click="addTicketType">Add Ticket Type</v-btn>

        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="cancel">Cancel</v-btn>
          <v-btn type="submit" color="primary">{{ isEditing ? 'Save Changes' : 'Create Event' }}</v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { apiService } from '@/services/apiService';
import type { Event, TicketType } from '@/types/Event';

const props = defineProps<{
  id?: string; // The ID of the event to edit
}>();

const router = useRouter();
const authStore = useAuthStore();
const isEditing = computed(() => !!props.id);
const formTitle = computed(() => isEditing.value ? 'Edit Event' : 'Create New Event');

const blankEvent: Event = {
  id: '',
  name: '',
  date: '',
  location: '',
  capacity: 0,
  organizerId: authStore.currentUser?.id || '',
  ticket_types: [],
};

const editableEvent = ref<Event>({ ...blankEvent });

onMounted(async () => {
  if (isEditing.value) {
    const fetchedEvent = await apiService.getEventById(props.id!);
    if (fetchedEvent) {
      // Format date for datetime-local input
      const d = new Date(fetchedEvent.date);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      const hours = String(d.getHours()).padStart(2, '0');
      const minutes = String(d.getMinutes()).padStart(2, '0');
      fetchedEvent.date = `${year}-${month}-${day}T${hours}:${minutes}`;
      editableEvent.value = fetchedEvent;
    }
  }
});

function addTicketType() {
  editableEvent.value.ticket_types.push({ id: `new-${Date.now()}`, name: '', price: 0, payment_url: '' });
}

function removeTicketType(index: number) {
  editableEvent.value.ticket_types.splice(index, 1);
}

function saveEvent() {
  // In a real app, this would call apiService.createEvent or apiService.updateEvent
  console.log('Saving event:', editableEvent.value);
  alert(`Event "${editableEvent.value.name}" ${isEditing.value ? 'updated' : 'created'}! (Simulation)`);
  router.push({ name: 'organizer-dashboard' });
}

function cancel() {
  router.back();
}
</script>
