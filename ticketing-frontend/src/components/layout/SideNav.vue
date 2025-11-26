<template>
  <v-navigation-drawer :model-value="drawer" @update:modelValue="$emit('update:drawer', $event)" app>
    <v-list dense>
      <v-list-item link to="/" prepend-icon="mdi-home">
        <v-list-item-title>Home</v-list-item-title>
      </v-list-item>

      <!-- Super Admin Links -->
      <template v-if="authStore.superAdmin">
        <v-list-subheader>Platform Admin</v-list-subheader>
        <v-list-item link to="/admin/events" prepend-icon="mdi-calendar-multiple">
          <v-list-item-title>Manage Events</v-list-item-title>
        </v-list-item>
        <v-list-item link to="/admin/organizers" prepend-icon="mdi-account-group">
          <v-list-item-title>Manage Organizers</v-list-item-title>
        </v-list-item>
        <v-list-item link to="/admin/stats" prepend-icon="mdi-chart-bar">
          <v-list-item-title>Global Stats</v-list-item-title>
        </v-list-item>
      </template>

      <!-- Organizer Links -->
      <template v-if="authStore.organizer">
        <v-list-subheader>Organizer Menu</v-list-subheader>
        <v-list-item link to="/organizer/dashboard" prepend-icon="mdi-view-dashboard">
          <v-list-item-title>Dashboard</v-list-item-title>
        </v-list-item>
        <v-list-item link to="/organizer/events/new" prepend-icon="mdi-calendar-plus">
          <v-list-item-title>Create Event</v-list-item-title>
        </v-list-item>
         <v-list-item link to="/organizer/staff" prepend-icon="mdi-account-tie">
          <v-list-item-title>Manage Staff</v-list-item-title>
        </v-list-item>
      </template>
      
      <!-- Controller Links -->
      <template v-if="authStore.controller">
        <v-list-subheader>Control</v-list-subheader>
        <v-list-item link to="/scan" prepend-icon="mdi-qrcode-scan">
          <v-list-item-title>Scan Ticket</v-list-item-title>
        </v-list-item>
      </template>

    </v-list>
  </v-navigation-drawer>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';

defineProps({
  drawer: Boolean,
});
defineEmits(['update:drawer']);

const authStore = useAuthStore();
</script>
