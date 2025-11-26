<template>
  <v-container fluid>
    <v-card>
      <v-card-title>Manage Staff</v-card-title>
      <v-card-subtitle>View controllers and cashiers.</v-card-subtitle>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="staff"
          :loading="loading"
          class="elevation-1"
        >
          <template v-slot:item.role="{ item }">
            <v-chip :color="item.role === 'controller' ? 'blue' : 'green'" dark>{{ item.role }}</v-chip>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { apiService } from '@/services/apiService';
import type { User } from '@/types/User';

const staff = ref<User[]>([]);
const loading = ref(true);

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Role', key: 'role' },
];

onMounted(async () => {
  loading.value = true;
  const allUsers = await apiService.getUsers();
  // In a real app, you'd filter by organizationId
  staff.value = allUsers.filter(user => user.role === 'controller' || user.role === 'cashier');
  loading.value = false;
});
</script>
