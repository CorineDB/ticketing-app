<template>
  <DashboardLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Scan Tickets</h1>
        <p class="text-gray-600">Scan QR codes to validate entry/exit</p>
      </div>

      <!-- Scan Type Selector -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-center gap-4">
          <button
            @click="scanType = 'entry'"
            :class="[
              'flex-1 py-3 px-6 rounded-lg font-medium transition-colors',
              scanType === 'entry'
                ? 'bg-green-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            <div class="flex items-center justify-center gap-2">
              <LogInIcon class="w-5 h-5" />
              Entry Scan
            </div>
          </button>
          <button
            @click="scanType = 'exit'"
            :class="[
              'flex-1 py-3 px-6 rounded-lg font-medium transition-colors',
              scanType === 'exit'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            <div class="flex items-center justify-center gap-2">
              <LogOutIcon class="w-5 h-5" />
              Exit Scan
            </div>
          </button>
        </div>
      </div>

      <!-- QR Scanner -->
      <QRScanner @scan="handleScan" />

      <!-- Scan Result -->
      <ScanResult
        :result="lastScanResult"
        @close="lastScanResult = null"
        @scan-next="lastScanResult = null"
      />

      <!-- Recent Scans -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Scans</h2>

        <div v-if="scans.length > 0" class="space-y-3">
          <div
            v-for="scan in scans.slice(0, 5)"
            :key="scan.id"
            class="flex items-center justify-between p-4 rounded-lg border border-gray-200"
          >
            <div class="flex items-center gap-4">
              <div
                :class="[
                  'w-10 h-10 rounded-full flex items-center justify-center',
                  scan.result === 'valid' ? 'bg-green-100' : 'bg-red-100'
                ]"
              >
                <component
                  :is="scan.result === 'valid' ? CheckCircleIcon : XCircleIcon"
                  :class="[
                    'w-5 h-5',
                    scan.result === 'valid' ? 'text-green-600' : 'text-red-600'
                  ]"
                />
              </div>
              <div>
                <div class="font-medium text-gray-900">{{ scan.ticket?.buyer_name }}</div>
                <div class="text-sm text-gray-500">{{ formatTime(scan.scanned_at) }}</div>
              </div>
            </div>
            <Badge :variant="scan.result === 'valid' ? 'success' : 'danger'">
              {{ scan.scan_type }}
            </Badge>
          </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
          No scans yet. Start scanning tickets above.
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useScanner } from '@/composables/useScanner'
import { formatTime } from '@/utils/formatters'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import QRScanner from '@/components/scanners/QRScanner.vue'
import ScanResult from '@/components/scanners/ScanResult.vue'
import Badge from '@/components/common/Badge.vue'
import {
  LogInIcon,
  LogOutIcon,
  CheckCircleIcon,
  XCircleIcon
} from 'lucide-vue-next'

const scanType = ref<'entry' | 'exit'>('entry')
const lastScanResult = ref<any>(null)

const {
  scans,
  scanning,
  parseQRCode,
  performTwoStepScan
} = useScanner()

async function handleScan(qrData: string) {
  try {
    console.log("Handling scanned data: ", qrData);
    // Parse QR code data
    const scanRequest = parseQRCode(qrData)

    if (!scanRequest) {
      lastScanResult.value = {
        success: false,
        result: 'invalid',
        message: 'Invalid QR code format s'
      }
      return
    }

    // Set scan type
    scanRequest.scan_type = scanType.value

    // Perform 2-step scan
    const result = await performTwoStepScan(scanRequest)

    if (result) {
      lastScanResult.value = result
    }
  } catch (error: any) {
    lastScanResult.value = {
      success: false,
      result: 'invalid',
      message: error.message || 'Failed to scan ticket'
    }
  }
}
</script>
