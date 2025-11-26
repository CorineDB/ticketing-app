<template>
  <v-app-bar app color="primary" dark>
    <v-app-bar-nav-icon @click="$emit('toggle-drawer')"></v-app-bar-nav-icon>
    <v-toolbar-title>Ticketing Platform</v-toolbar-title>
    <v-spacer></v-spacer>
    <div v-if="authStore.isLoggedIn" class="d-flex align-center">
      <span class="mr-2">Welcome, {{ authStore.currentUser?.name }} ({{ authStore.currentUser?.role }})</span>
      <v-btn icon @click="handleLogout">
        <v-icon>mdi-logout</v-icon>
      </v-btn>
    </div>
  </v-app-bar>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

defineEmits(['toggle-drawer']);

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = () => {
  authStore.logout();
  router.push({ name: 'login' });
};
</script>
