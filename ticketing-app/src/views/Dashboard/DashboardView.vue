<template>
  <DashboardLayout>
    <!-- Route to role-specific dashboards -->
    <SuperAdminDashboard v-if="authStore.isSuperAdmin" />
    <OrganizerDashboard v-else-if="authStore.isOrganizer" />
    <ScannerDashboard v-else-if="authStore.isScanner" />
    <CashierDashboard v-else-if="authStore.isCashier" />
    <ParticipantDashboard v-else />
  </DashboardLayout>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import SuperAdminDashboard from './SuperAdminDashboard.vue'
import OrganizerDashboard from './OrganizerDashboard.vue'
import ScannerDashboard from './ScannerDashboard.vue'
import CashierDashboard from './CashierDashboard.vue'
import ParticipantDashboard from './ParticipantDashboard.vue'

const authStore = useAuthStore()
</script>
