<template>
  <v-container fluid>
    <v-card>
      <v-card-title>Manage Organizers</v-card-title>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="organizers"
          :loading="loading"
          class="elevation-1"
        >
          <!-- Add slots here for actions like 'edit' or 'delete' in a real app -->
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { apiService } from '@/services/apiService';
import type { User } from '@/types/User';

const organizers = ref<User[]>([]);
const loading = ref(true);

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'ID', key: 'id' },
];

onMounted(async () => {
  loading.value = true;
  const allUsers = await apiService.getUsers();
  organizers.value = allUsers.filter(user => user.role === 'organizer');
  loading.value = false;
});
</script>
