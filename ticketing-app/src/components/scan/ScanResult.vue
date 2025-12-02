<script setup lang="ts">
import { computed } from 'vue'
import type { ScanConfirmResponse } from '@/types/scan'
import { CheckCircleIcon, XCircleIcon, ArrowRightIcon } from 'lucide-vue-next'

const props = defineProps<{
  result: ScanConfirmResponse
}>()

const emit = defineEmits<{
  'new-scan': []
}>()

const isSuccess = computed(() => props.result.valid)
</script>

<template>
  <div class="scan-result-card" :class="{ 'success': isSuccess, 'error': !isSuccess }">
    <div class="icon-container">
      <CheckCircleIcon v-if="isSuccess" class="w-24 h-24 text-green-500" />
      <XCircleIcon v-else class="w-24 h-24 text-red-500" />
    </div>

    <h2 class="result-title">
      {{ isSuccess ? 'Entrée Autorisée' : 'Entrée Refusée' }}
    </h2>

    <p class="result-message">{{ result.message }}</p>

    <div class="ticket-summary" v-if="result.ticket">
      <p class="ticket-owner">{{ result.ticket.buyer_name }}</p>
      <p class="ticket-code">{{ result.ticket.code }}</p>
    </div>

    <button @click="emit('new-scan')" class="btn-new-scan">
      Scanner un nouveau ticket
      <ArrowRightIcon class="w-5 h-5 ml-2 inline-block" />
    </button>
  </div>
</template>

<style scoped>
.scan-result-card {
  background: white;
  border-radius: 16px;
  padding: 3rem 2rem;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  max-width: 400px;
  width: 100%;
  margin: 0 auto;
  animation: slideUp 0.4s ease-out;
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.icon-container {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.result-title {
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: 1rem;
}

.success .result-title { color: #28a745; }
.error .result-title { color: #dc3545; }

.result-message {
  font-size: 1.125rem;
  color: #666;
  margin-bottom: 2rem;
}

.ticket-summary {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.ticket-owner {
  font-weight: 700;
  font-size: 1.25rem;
  color: #333;
}

.ticket-code {
  font-family: monospace;
  color: #666;
  font-size: 1rem;
}

.btn-new-scan {
  background: #007bff;
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: 8px;
  font-size: 1.125rem;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-new-scan:hover {
  background: #0056b3;
}
</style>
