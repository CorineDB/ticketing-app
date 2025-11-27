<template>
  <StatusBadge :status="status" type="ticket" />
</template>

<script setup lang="ts">
import type { TicketStatus } from '@/types/api'
import StatusBadge from '@/components/common/StatusBadge.vue'

defineProps<{
  status: TicketStatus
}>()
</script>
