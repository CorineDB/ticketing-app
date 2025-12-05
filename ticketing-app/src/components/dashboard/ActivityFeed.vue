<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
      <slot name="actions"></slot>
    </div>
    
    <div class="activity-feed space-y-3 max-h-96 overflow-y-auto">
      <TransitionGroup name="slide-fade">
        <div
          v-for="activity in activities"
          :key="activity.id"
          class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <div :class="[
            'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-lg',
            getActivityColor(activity.type)
          ]">
            {{ activity.icon }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900">{{ activity.message }}</p>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-xs text-gray-500">{{ formatTime(activity.timestamp) }}</span>
              <span v-if="activity.amount" class="text-xs font-semibold text-gray-700">
                {{ formatCurrency(activity.amount) }}
              </span>
            </div>
          </div>
        </div>
      </TransitionGroup>
      
      <div v-if="activities.length === 0" class="text-center py-8 text-gray-500">
        <RadioIcon class="w-12 h-12 mx-auto mb-2 opacity-50" />
        <p class="text-sm">No recent activity</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { RadioIcon } from 'lucide-vue-next'
import { formatCurrency } from '@/utils/formatters'

interface Activity {
  id: string
  type: 'purchase' | 'scan' | 'alert' | 'event' | 'other'
  icon: string
  message: string
  timestamp: Date
  amount?: number
}

interface Props {
  title: string
  activities: Activity[]
}

defineProps<Props>()

function getActivityColor(type: string): string {
  const colors = {
    purchase: 'bg-blue-100 text-blue-600',
    scan: 'bg-green-100 text-green-600',
    alert: 'bg-orange-100 text-orange-600',
    event: 'bg-purple-100 text-purple-600',
    other: 'bg-gray-100 text-gray-600'
  }
  return colors[type as keyof typeof colors] || colors.other
}

function formatTime(date: Date): string {
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const seconds = Math.floor(diff / 1000)
  const minutes = Math.floor(seconds / 60)
  const hours = Math.floor(minutes / 60)
  const days = Math.floor(hours / 24)

  if (seconds < 60) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  return `${days}d ago`
}
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from {
  transform: translateY(-10px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(10px);
  opacity: 0;
}

.activity-feed::-webkit-scrollbar {
  width: 6px;
}

.activity-feed::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.activity-feed::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.activity-feed::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
