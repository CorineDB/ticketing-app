<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <Header
      :show-menu-toggle="false"
      :show-search="false"
    />

    <!-- Main Content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <!-- About -->
          <div>
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                <TicketIcon class="w-6 h-6 text-white" />
              </div>
              <span class="text-xl font-bold text-gray-900">Ticketing</span>
            </div>
            <p class="text-sm text-gray-600">
              Your trusted platform for event ticketing and management.
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <h4 class="font-semibold text-gray-900 mb-4">Quick Links</h4>
            <ul class="space-y-2 text-sm">
              <li>
                <RouterLink to="/" class="text-gray-600 hover:text-blue-600">
                  Home
                </RouterLink>
              </li>
              <li>
                <RouterLink to="/events" class="text-gray-600 hover:text-blue-600">
                  Browse Events
                </RouterLink>
              </li>
              <li>
                <RouterLink to="/login" class="text-gray-600 hover:text-blue-600">
                  Login
                </RouterLink>
              </li>
            </ul>
          </div>

          <!-- Support -->
          <div>
            <h4 class="font-semibold text-gray-900 mb-4">Support</h4>
            <ul class="space-y-2 text-sm">
              <li>
                <a href="#" class="text-gray-600 hover:text-blue-600">
                  Help Center
                </a>
              </li>
              <li>
                <a href="#" class="text-gray-600 hover:text-blue-600">
                  Contact Us
                </a>
              </li>
              <li>
                <a href="#" class="text-gray-600 hover:text-blue-600">
                  FAQs
                </a>
              </li>
            </ul>
          </div>

          <!-- Legal -->
          <div>
            <h4 class="font-semibold text-gray-900 mb-4">Legal</h4>
            <ul class="space-y-2 text-sm">
              <li>
                <a href="#" class="text-gray-600 hover:text-blue-600">
                  Terms of Service
                </a>
              </li>
              <li>
                <a href="#" class="text-gray-600 hover:text-blue-600">
                  Privacy Policy
                </a>
              </li>
              <li>
                <a href="#" class="text-gray-600 hover:text-blue-600">
                  Cookie Policy
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div class="mt-8 pt-8 border-t border-gray-200 text-center text-sm text-gray-600">
          &copy; {{ currentYear }} Ticketing App. All rights reserved.
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import Header from './Header.vue'
import { TicketIcon } from 'lucide-vue-next'

const currentYear = computed(() => new Date().getFullYear())
</script>
