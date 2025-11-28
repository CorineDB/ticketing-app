<template>
  <Modal :show="show" @close="closeModal">
    <template #title>
      <h3 class="text-lg font-medium text-gray-900">Ticket QR Code</h3>
    </template>
    <template #body>
      <div v-if="ticket" class="flex flex-col items-center justify-center space-y-4">
        <p class="text-gray-700">Scan this QR code for entry.</p>
        <div class="p-4 bg-white rounded-lg shadow-md">
          <TicketQRCode :ticket="ticket" :size="250" />
        </div>
        <p class="text-sm text-gray-500">{{ ticket.code }}</p>
        <button
          @click="downloadQRCode"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <DownloadIcon class="w-5 h-5 mr-2" />
          Download QR Code
        </button>
      </div>
      <div v-else class="text-gray-500">
        <p>No ticket selected for QR code display.</p>
      </div>
    </template>
    <template #footer>
      <button
        type="button"
        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-100 border border-transparent rounded-md hover:bg-blue-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500"
        @click="closeModal"
      >
        Close
      </button>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import Modal from '@/components/common/Modal.vue' // Assuming a common Modal component exists
import TicketQRCode from '@/components/tickets/TicketQRCode.vue' // Reusing the existing TicketQRCode component
import type { Ticket } from '@/types/api'
import { DownloadIcon } from 'lucide-vue-next' // Assuming DownloadIcon is available

const props = defineProps<{
  show: boolean;
  ticket?: Ticket | null;
}>();

const emit = defineEmits(['update:show']);

const closeModal = () => {
  emit('update:show', false);
};

const downloadQRCode = () => {
  // Logic to download the QR code image
  if (props.ticket?.qr_code) {
    const link = document.createElement('a');
    link.href = props.ticket.qr_code;
    link.download = `ticket-${props.ticket.code}-qrcode.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
};
</script>
