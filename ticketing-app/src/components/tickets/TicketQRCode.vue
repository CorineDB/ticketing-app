<template>
  <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 text-center">
    <!-- Ticket Info Header -->
    <div class="mb-6">
      <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ ticket.event?.title }}</h3>
      <p class="text-gray-600">{{ ticket.buyer_name }}</p>
      <TicketStatusBadge :status="ticket.status" class="mt-2" />
    </div>

    <!-- QR Code -->
    <div class="bg-white p-6 rounded-lg inline-block">
      <img
        v-if="qr_code.url"
        :src="qr_code.url"
        alt="Ticket QR Code"
        class="w-64 h-64"
      />
      <div v-else class="w-64 h-64 bg-gray-100 flex items-center justify-center rounded-lg">
        <QrCodeIcon class="w-16 h-16 text-gray-400" />
      </div>
    </div>

    <!-- Ticket Code -->
    <div class="mt-6">
      <p class="text-sm text-gray-600 mb-2">Ticket Code</p>
      <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-lg">
        <code class="text-lg font-mono font-bold text-gray-900">{{ ticket.code }}</code>
        <button
          @click="copyCode"
          class="p-1 hover:bg-gray-200 rounded"
          :title="copied ? 'Copied!' : 'Copy code'"
        >
          <CheckIcon v-if="copied" class="w-4 h-4 text-green-600" />
          <CopyIcon v-else class="w-4 h-4 text-gray-600" />
        </button>
      </div>
    </div>

    <!-- Ticket Details -->
    <div class="mt-6 pt-6 border-t border-gray-200 space-y-3 text-left">
      <div class="flex items-center justify-between text-sm">
        <span class="text-gray-600">Ticket Type</span>
        <span class="font-medium text-gray-900">{{ ticket.ticket_type?.name }}</span>
      </div>
      <div class="flex items-center justify-between text-sm">
        <span class="text-gray-600">Date</span>
        <span class="font-medium text-gray-900">{{ formatDate(ticket.event?.start_datetime) }}</span>
      </div>
      <div class="flex items-center justify-between text-sm">
        <span class="text-gray-600">Venue</span>
        <span class="font-medium text-gray-900">{{ ticket.event?.venue }}</span>
      </div>
      <div v-if="ticket.price" class="flex items-center justify-between text-sm">
        <span class="text-gray-600">Price</span>
        <span class="font-medium text-gray-900">{{ formatCurrency(ticket.price) }}</span>
      </div>
    </div>

    <!-- Download Button -->
    <button
      @click="$emit('download')"
      class="mt-6 w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 flex items-center justify-center gap-2"
    >
      <DownloadIcon class="w-5 h-5" />
      Download Ticket
    </button>

    <!-- Warning for invalid tickets -->
    <div v-if="ticket.status === 'invalid' || ticket.status === 'refunded'" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-sm text-red-700">
        This ticket is {{ ticket.status }} and cannot be used for entry.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { Ticket } from '@/types/api'
import { formatDate, formatCurrency } from '@/utils/formatters'
import TicketStatusBadge from './TicketStatusBadge.vue'
import { useTickets } from '@/composables/useTickets'
import {
  QrCodeIcon,
  CopyIcon,
  CheckIcon,
  DownloadIcon
} from 'lucide-vue-next'
import ticketService from '@/services/ticketService'

const props = defineProps<{
  ticket: Ticket
}>()

defineEmits<{
  qr: string
  download: []
}>()

const copied = ref(false)

function copyCode() {
  navigator.clipboard.writeText(props.ticket.code)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2000)
}

const qr_code = ref<{ url: string | null }>({ url: null })
const loading = ref(true)

onMounted(async () => {
  console.log(props.ticket.id);
  try {
    // Fetch ticket by code (magic link token)
    const code = await ticketService.downloadQR(props.ticket.id, props.ticket.magic_link_token);
    console.log("Fetched QR code:", code);
    qr_code.value = { url: URL.createObjectURL(code) };
    console.log("Fetched QR code:", qr_code.value);
  } catch (error) {
    console.error('Failed to fetch ticket:', error)
  } finally {
    loading.value = false
  }
})
</script>
