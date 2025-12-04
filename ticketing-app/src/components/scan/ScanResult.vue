<script setup lang="ts">
import { computed } from 'vue'
import type { ScanConfirmResponse } from '@/types/scan'
import { CheckCircleIcon, XCircleIcon, AlertCircleIcon, AlertTriangleIcon, ArrowRightIcon } from 'lucide-vue-next'

const props = defineProps<{
  result: ScanConfirmResponse
}>()

const emit = defineEmits<{
  'new-scan': []
}>()

// Determine the visual state based on response code
const visualState = computed(() => {
  switch (props.result.code) {
    case 'OK':
      return {
        type: 'success',
        icon: CheckCircleIcon,
        iconColor: 'text-green-500',
        bgColor: 'bg-green-50',
        borderColor: 'border-green-500',
        titleColor: 'text-green-700',
        title: 'Entrée Autorisée'
      }
    case 'ALREADY_IN':
      return {
        type: 'warning',
        icon: AlertCircleIcon,
        iconColor: 'text-yellow-500',
        bgColor: 'bg-yellow-50',
        borderColor: 'border-yellow-500',
        titleColor: 'text-yellow-700',
        title: 'Déjà à l\'intérieur'
      }
    case 'ALREADY_OUT':
      return {
        type: 'warning',
        icon: AlertCircleIcon,
        iconColor: 'text-orange-500',
        bgColor: 'bg-orange-50',
        borderColor: 'border-orange-500',
        titleColor: 'text-orange-700',
        title: 'Pas à l\'intérieur'
      }
    case 'CAPACITY_FULL':
      return {
        type: 'error',
        icon: AlertTriangleIcon,
        iconColor: 'text-orange-600',
        bgColor: 'bg-orange-50',
        borderColor: 'border-orange-600',
        titleColor: 'text-orange-800',
        title: 'Capacité Atteinte'
      }
    case 'EXPIRED':
      return {
        type: 'error',
        icon: XCircleIcon,
        iconColor: 'text-red-500',
        bgColor: 'bg-red-50',
        borderColor: 'border-red-500',
        titleColor: 'text-red-700',
        title: 'Ticket Expiré'
      }
    case 'INVALID':
    default:
      return {
        type: 'error',
        icon: XCircleIcon,
        iconColor: 'text-red-500',
        bgColor: 'bg-red-50',
        borderColor: 'border-red-500',
        titleColor: 'text-red-700',
        title: 'Entrée Refusée'
      }
  }
})
</script>

<template>
  <div 
    class="scan-result-card" 
    :class="[visualState.bgColor, visualState.borderColor]"
  >
    <div class="icon-container">
      <component 
        :is="visualState.icon" 
        class="w-24 h-24" 
        :class="visualState.iconColor"
      />
    </div>

    <h2 class="result-title" :class="visualState.titleColor">
      {{ visualState.title }}
    </h2>

    <p class="result-message">{{ result.message }}</p>

    <div class="ticket-summary" v-if="result.ticket">
      <p class="ticket-owner">{{ result.ticket.buyer_name }}</p>
      <p class="ticket-code">{{ result.ticket.code }}</p>
      <div class="ticket-status">
        <span class="status-label">Statut:</span>
        <span class="status-value" :class="{
          'text-green-600': result.ticket.status === 'in',
          'text-blue-600': result.ticket.status === 'out',
          'text-gray-600': result.ticket.status === 'paid'
        }">
          {{ result.ticket.status.toUpperCase() }}
        </span>
      </div>
    </div>

    <button @click="emit('new-scan')" class="btn-new-scan">
      Scanner un nouveau ticket
      <ArrowRightIcon class="w-5 h-5 ml-2 inline-block" />
    </button>
  </div>
</template>

<style scoped>
.scan-result-card {
  border-radius: 16px;
  padding: 3rem 2rem;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  max-width: 400px;
  width: 100%;
  margin: 0 auto;
  animation: slideUp 0.4s ease-out;
  border: 3px solid;
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.icon-container {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
  animation: scaleIn 0.5s ease-out 0.2s both;
}

@keyframes scaleIn {
  from { transform: scale(0); }
  to { transform: scale(1); }
}

.result-title {
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: 1rem;
}

.result-message {
  font-size: 1.125rem;
  color: #666;
  margin-bottom: 2rem;
}

.ticket-summary {
  background: white;
  padding: 1.25rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.ticket-owner {
  font-weight: 700;
  font-size: 1.25rem;
  color: #333;
  margin-bottom: 0.5rem;
}

.ticket-code {
  font-family: monospace;
  color: #666;
  font-size: 1rem;
  margin-bottom: 0.75rem;
}

.ticket-status {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e5e7eb;
}

.status-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.status-value {
  font-size: 0.875rem;
  font-weight: 700;
  letter-spacing: 0.05em;
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
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-new-scan:hover {
  background: #0056b3;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.btn-new-scan:active {
  transform: translateY(0);
}
</style>
