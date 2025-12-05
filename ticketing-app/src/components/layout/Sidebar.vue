<template>
  <aside
    :class="[
      'fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out',
      isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]"
  >
    <!-- Overlay for mobile -->
    <div
      v-if="isOpen"
      @click="$emit('close')"
      class="fixed inset-0 bg-black bg-opacity-50 lg:hidden"
      style="z-index: -1"
    />

    <!-- Sidebar Content -->
    <div class="h-full flex flex-col">
      <!-- Close button (mobile) -->
      <div class="p-4 border-b border-gray-200 lg:hidden">
        <button
          @click="$emit('close')"
          class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
        >
          <XIcon class="w-6 h-6" />
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto p-4 space-y-1">
        <!-- Dashboard -->
        <NavLink
          to="/dashboard"
          :icon="LayoutDashboardIcon"
          label="Dashboard"
          @click="$emit('close')"
        />

        <!-- Events (Organizer, Super Admin) -->
        <template v-if="isSuperAdmin || isOrganizer">
          <div class="pt-4 pb-2">
            <div class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              Events
            </div>
          </div>

          <NavLink
            to="/dashboard/events"
            :icon="CalendarIcon"
            label="All Events"
            @click="$emit('close')"
          />
          <NavLink
            to="/dashboard/events/new"
            :icon="PlusCircleIcon"
            label="Create Event"
            @click="$emit('close')"
          />
        </template>

        <!-- Tickets -->
        <template v-if="isOrganizer || isScanner">
          <div class="pt-4 pb-2">
            <div class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              Tickets
            </div>
          </div>

          <NavLink
            to="/dashboard/tickets"
            :icon="TicketIcon"
            label="All Tickets"
            @click="$emit('close')"
          />
        </template>

        <!-- Scanner (Scanner role) -->
        <template v-if="isScanner || isOrganizer || isSuperAdmin">
          <div class="pt-4 pb-2">
            <div class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              Scanner
            </div>
          </div>

          <NavLink
            to="/scanner"
            :icon="ScanIcon"
            label="Scan Tickets"
            @click="$emit('close')"
          />
          <NavLink
            to="/dashboard/scanner/history"
            :icon="HistoryIcon"
            label="Scan History"
            @click="$emit('close')"
          />
        </template>

        <!-- Organizations (Super Admin) -->
        <template v-if="isSuperAdmin">
          <div class="pt-4 pb-2">
            <div class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              Organizations
            </div>
          </div>

          <NavLink
            to="/dashboard/organisateurs"
            :icon="BuildingIcon"
            label="All Organizations"
            @click="$emit('close')"
          />
        </template>

        <!-- Users (Super Admin, Organizer) -->
        <template v-if="isSuperAdmin || isOrganizer">
          <div class="pt-4 pb-2">
            <div class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              Users
            </div>
          </div>

          <NavLink
            to="/dashboard/users"
            :icon="UsersIcon"
            label="All Users"
            @click="$emit('close')"
          />
        </template>

        <!-- Reports -->
        <template v-if="isSuperAdmin || isOrganizer">
          <div class="pt-4 pb-2">
            <div class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              Reports
            </div>
          </div>

          <NavLink
            to="/dashboard/reports"
            :icon="BarChartIcon"
            label="Analytics & Reports"
            @click="$emit('close')"
          />
        </template>
      </nav>

      <!-- Footer -->
      <div class="p-4 border-t border-gray-200">
        <div class="text-xs text-gray-500 text-center">
          Ticketing App v1.0.0
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions'
import {
  XIcon,
  LayoutDashboardIcon,
  CalendarIcon,
  PlusCircleIcon,
  TicketIcon,
  ScanIcon,
  HistoryIcon,
  BuildingIcon,
  UsersIcon,
  BarChartIcon
} from 'lucide-vue-next'
import NavLink from './NavLink.vue'

defineProps<{
  isOpen: boolean
}>()

defineEmits<{
  close: []
}>()

const { isSuperAdmin, isOrganizer, isScanner } = usePermissions()
</script>
