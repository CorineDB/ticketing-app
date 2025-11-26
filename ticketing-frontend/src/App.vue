<template>
  <v-app>
    <template v-if="authStore.isLoggedIn">
      <AppHeader @toggle-drawer="drawer = !drawer" />
      <SideNav v-model:drawer="drawer" />
    </template>
    
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import AppHeader from '@/components/layout/AppHeader.vue';
import SideNav from '@/components/layout/SideNav.vue';

const authStore = useAuthStore();
const drawer = ref(true); // Drawer is open by default
</script>