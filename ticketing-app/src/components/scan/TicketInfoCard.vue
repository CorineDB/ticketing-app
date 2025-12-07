<script setup lang="ts">
import type { TicketInfo } from '@/types/scan'

defineProps<{
  ticket: TicketInfo
}>()

const statusColors: Record<string, string> = {
  issued: '#ffc107', // Yellow
  paid: '#28a745',   // Green
  in: '#17a2b8',     // Cyan
  out: '#6c757d',    // Grey
  reserved: '#007bff', // Blue
  invalid: '#dc3545', // Red
  refunded: '#343a40' // Dark Grey
}

const statusLabels: Record<string, string> = {
  issued: 'Émis',
  paid: 'Payé',
  in: 'À l\'intérieur',
  out: 'Sorti',
  reserved: 'Réservé',
  invalid: 'Invalide',
  refunded: 'Remboursé'
}
</script>

<template>
  <div class="ticket-card">
    <div class="ticket-header">
      <div class="status-badge" :style="{ background: statusColors[ticket.status] || '#6c757d' }">
        {{ statusLabels[ticket.status] || ticket.status }}
      </div>
    </div>

    <div class="buyer-info">
      <h2>{{ ticket.buyer_name }}</h2>
      <p class="text-gray-600">{{ ticket.buyer_email }}</p>
      <p v-if="ticket.buyer_phone" class="text-gray-600">{{ ticket.buyer_phone }}</p>
    </div>

    <div class="ticket-details">
      <div class="detail-row">
        <span class="label">Code:</span>
        <span class="value font-mono">{{ ticket.code }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Type:</span>
        <span class="value">{{ ticket.ticket_type.name }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Prix:</span>
        <span class="value">{{ ticket.ticket_type.price }} XOF</span>
      </div>
    </div>

    <div class="event-info">
      <h3>{{ ticket.event.title }}</h3>
      <p>
        {{ new Date(ticket.event.start_datetime).toLocaleDateString() }}
        -
        {{ new Date(ticket.event.end_datetime).toLocaleDateString() }}
      </p>
    </div>
  </div>
</template>

<style scoped>
.ticket-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  width: 100%;
}

.ticket-header {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 1rem;
}

.status-badge {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
}

.buyer-info {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e0e0e0;
  text-align: center;
}

.buyer-info h2 {
  margin: 0 0 0.5rem 0;
  color: #333;
  font-size: 1.5rem;
  font-weight: 700;
}

.ticket-details {
  margin: 1rem 0;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px dashed #eee;
}

.label {
  color: #666;
  font-weight: 500;
}

.value {
  color: #333;
  font-weight: 600;
}

.event-info {
  margin-top: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  text-align: center;
}

.event-info h3 {
  margin: 0 0 0.5rem 0;
  color: #333;
  font-weight: 600;
}

.event-info p {
  margin: 0;
  color: #666;
  font-size: 0.875rem;
}
</style>
