<template>
  <StatusBadge :status="status" type="event" />
</template>

<script setup lang="ts">
import type { EventStatus } from '@/types/api'
import StatusBadge from '@/components/common/StatusBadge.vue'

defineProps<{
  status: EventStatus
}>()
</script>
