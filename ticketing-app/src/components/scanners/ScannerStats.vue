<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <StatCard title="Total Scans" :value="stats.totalScans" icon="BarcodeIcon" />
    <StatCard title="Successful Scans" :value="stats.successfulScans" icon="CheckCircleIcon" type="success" />
    <StatCard title="Failed Scans" :value="stats.failedScans" icon="XCircleIcon" type="error" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import StatCard from '@/components/dashboard/StatCard.vue' // Assuming this component exists

// Assuming useScanner provides statistics or this component fetches its own
import { useScanner } from '@/composables/useScanner'

const { fetchScanStats, scanStats, loading, error } = useScanner()

const stats = ref({
  totalScans: 0,
  successfulScans: 0,
  failedScans: 0
})

onMounted(() => {
  fetchScanStats() // Fetch stats on component mount
})

watch(scanStats, (newStats) => {
  if (newStats) {
    stats.value = newStats
  }
}, { immediate: true }) // Populate initially if data is already there

// Optionally accept props if stats are passed down from a parent view
// const props = defineProps<{
//   initialStats?: {
//     totalScans: number;
//     successfulScans: number;
//     failedScans: number;
//   }
// }>();

// watch(() => props.initialStats, (newVal) => {
//   if (newVal) {
//     stats.value = newVal;
//   }
// }, { immediate: true });
</script>
