<template>
  <v-container class="fill-height d-flex justify-center align-center">
    <v-card width="400">
      <v-card-title class="text-center text-h5">Login</v-card-title>
      <v-card-text>
        <p class="mb-4">Enter a mock user's email to log in:</p>
        <ul class="mb-4">
          <li>super@ticketing.app (Super Admin)</li>
          <li>contact@eventcorp.io (Organizer)</li>
          <li>john.s@gate-control.co (Controller)</li>
        </ul>
        <v-form @submit.prevent="handleLogin">
          <v-text-field
            v-model="email"
            label="Email"
            type="email"
            :error-messages="authStore.error || ''"
            :loading="authStore.isLoading"
            required
          ></v-text-field>
          <v-btn
            type="submit"
            color="primary"
            block
            :loading="authStore.isLoading"
          >
            Login
          </v-btn>
        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const email = ref('');
const authStore = useAuthStore();
const router = useRouter();

const handleLogin = async () => {
  await authStore.login(email.value);
  if (authStore.isLoggedIn) {
    // Redirect to home page after successful login
    router.push({ name: 'home' });
  }
};
</script>

<style scoped>
.fill-height {
  min-height: 100vh;
}
</style>
