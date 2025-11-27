<template>
  <slot v-if="!hasPermission" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePermissions } from '@/composables/usePermissions'

const props = defineProps<{
  permission: string
}>()

const { can } = usePermissions()

const hasPermission = computed(() => can(props.permission))
</script>
