<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <Header
      :show-menu-toggle="true"
      :show-search="true"
      @toggle-sidebar="sidebarOpen = !sidebarOpen"
    />

    <div class="flex">
      <!-- Sidebar -->
      <Sidebar
        :is-open="sidebarOpen"
        @close="sidebarOpen = false"
      />

      <!-- Main Content -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Header from './Header.vue'
import Sidebar from './Sidebar.vue'

const sidebarOpen = ref(false)
</script>
