<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
    <!-- Header -->
    <div class="flex items-start justify-between mb-4">
      <div class="flex items-center gap-3">
        <!-- Gate Icon -->
        <div :class="[
          'w-12 h-12 rounded-lg flex items-center justify-center',
          gateTypeColors.bg
        ]">
          <component :is="gateIcon" :class="['w-6 h-6', gateTypeColors.icon]" />
        </div>

        <div>
          <h3 class="text-lg font-semibold text-gray-900">{{ gate.name }}</h3>
          <div class="flex items-center gap-2 mt-1">
            <span :class="[
              'px-2 py-1 text-xs font-medium rounded',
              gateTypeColors.badge
            ]">
              {{ gateTypeLabel }}
            </span>
            <GateStatusBadge :status="gate.status" />
          </div>
        </div>
      </div>

      <!-- Actions Menu -->
      <div class="relative" v-if="showActions">
        <button
          @click="showMenu = !showMenu"
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
        >
          <MoreVerticalIcon class="w-5 h-5" />
        </button>

        <!-- Dropdown Menu -->
        <Transition name="fade">
          <div
            v-if="showMenu"
            v-click-away="() => showMenu = false"
            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10"
          >
            <button
              @click="$emit('edit', gate)"
              class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
            >
              <EditIcon class="w-4 h-4" />
              Edit Gate
            </button>
            <button
              v-if="gate.status !== 'active'"
              @click="$emit('updateStatus', gate.id, 'active')"
              class="w-full px-4 py-2 text-left text-sm text-green-700 hover:bg-green-50 flex items-center gap-2"
            >
              <PlayIcon class="w-4 h-4" />
              Activate
            </button>
            <button
              v-if="gate.status === 'active'"
              @click="$emit('updateStatus', gate.id, 'pause')"
              class="w-full px-4 py-2 text-left text-sm text-yellow-700 hover:bg-yellow-50 flex items-center gap-2"
            >
              <PauseIcon class="w-4 h-4" />
              Pause
            </button>
            <button
              v-if="gate.status !== 'inactive'"
              @click="$emit('updateStatus', gate.id, 'inactive')"
              class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
            >
              <StopCircleIcon class="w-4 h-4" />
              Deactivate
            </button>
            <button
              @click="$emit('assignScanner', gate)"
              class="w-full px-4 py-2 text-left text-sm text-blue-700 hover:bg-blue-50 flex items-center gap-2"
            >
              <UserIcon class="w-4 h-4" />
              Assign Scanner
            </button>
            <hr class="my-1" />
            <button
              @click="$emit('delete', gate.id)"
              class="w-full px-4 py-2 text-left text-sm text-red-700 hover:bg-red-50 flex items-center gap-2"
            >
              <TrashIcon class="w-4 h-4" />
              Delete Gate
            </button>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Gate Info -->
    <div class="space-y-3">
      <!-- Location -->
      <div v-if="gate.location" class="flex items-center gap-2 text-sm text-gray-600">
        <MapPinIcon class="w-4 h-4 text-gray-400" />
        <span>{{ gate.location }}</span>
      </div>

      <!-- Agent/Scanner Info -->
      <div class="mt-3 pt-3 border-t border-gray-100">
        <div v-if="gate.pivot?.agent" class="space-y-2">
          <div class="flex items-center gap-3">
            <!-- Agent Avatar -->
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
              {{ getInitials(gate.pivot.agent.name) }}
            </div>
            
            <!-- Agent Details -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <p class="text-sm font-medium text-gray-900 truncate">
                  {{ gate.pivot.agent.name }}
                </p>
                <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded">
                  Agent
                </span>
              </div>
              <div class="flex items-center gap-3 mt-1">
                <p v-if="gate.pivot.agent.email" class="text-xs text-gray-500 truncate flex items-center gap-1">
                  <MailIcon class="w-3 h-3" />
                  {{ gate.pivot.agent.email }}
                </p>
                <p v-if="gate.pivot.agent.phone" class="text-xs text-gray-500 flex items-center gap-1">
                  <PhoneIcon class="w-3 h-3" />
                  {{ gate.pivot.agent.phone }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- No Agent Assigned -->
        <div v-else class="flex items-center gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
          <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
            <UserIcon class="w-5 h-5 text-amber-600" />
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-amber-900">Aucun agent assigné</p>
            <p class="text-xs text-amber-700">Assignez un agent de contrôle</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats (if provided) -->
    <div v-if="stats" class="mt-4 pt-4 border-t border-gray-200">
      <div class="grid grid-cols-3 gap-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900">{{ stats.total_scans || 0 }}</div>
          <div class="text-xs text-gray-500">Total Scans</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-green-600">{{ stats.valid_scans || 0 }}</div>
          <div class="text-xs text-gray-500">Valid</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-red-600">{{ stats.invalid_scans || 0 }}</div>
          <div class="text-xs text-gray-500">Invalid</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import type { Gate, GateType } from '@/types/api'
import GateStatusBadge from './GateStatusBadge.vue'
import {
  MoreVerticalIcon,
  EditIcon,
  TrashIcon,
  UserIcon,
  MapPinIcon,
  MailIcon,
  PhoneIcon,
  PlayIcon,
  PauseIcon,
  StopCircleIcon,
  DoorOpenIcon,
  DoorClosedIcon,
  CrownIcon,
  CircleDotIcon
} from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    gate: Gate
    stats?: {
      total_scans: number
      valid_scans: number
      invalid_scans: number
    }
    showActions?: boolean
  }>(),
  {
    showActions: true
  }
)

defineEmits<{
  edit: [gate: Gate]
  delete: [gateId: string]
  updateStatus: [gateId: string, status: 'active' | 'pause' | 'inactive']
  assignScanner: [gate: Gate]
}>()

const showMenu = ref(false)

// Helper function to get initials from name
function getInitials(name: string): string {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const gateIcon = computed(() => {
  const icons: Record<GateType, any> = {
    entrance: DoorOpenIcon,
    exit: DoorClosedIcon,
    vip: CrownIcon,
    other: CircleDotIcon
  }
  return icons[props.gate.type]
})

const gateTypeLabel = computed(() => {
  const labels: Record<GateType, string> = {
    entrance: 'Entrance',
    exit: 'Exit',
    vip: 'VIP',
    other: 'Other'
  }
  return labels[props.gate.type]
})

const gateTypeColors = computed(() => {
  const colors: Record<GateType, { bg: string; icon: string; badge: string }> = {
    entrance: {
      bg: 'bg-green-100',
      icon: 'text-green-600',
      badge: 'bg-green-50 text-green-700'
    },
    exit: {
      bg: 'bg-red-100',
      icon: 'text-red-600',
      badge: 'bg-red-50 text-red-700'
    },
    vip: {
      bg: 'bg-purple-100',
      icon: 'text-purple-600',
      badge: 'bg-purple-50 text-purple-700'
    },
    other: {
      bg: 'bg-gray-100',
      icon: 'text-gray-600',
      badge: 'bg-gray-50 text-gray-700'
    }
  }
  return colors[props.gate.type]
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
