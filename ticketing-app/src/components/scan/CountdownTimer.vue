<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  seconds: number
}>()

const isLow = computed(() => props.seconds <= 10)
const isCritical = computed(() => props.seconds <= 5)
</script>

<template>
  <div class="countdown-timer" :class="{ 'low': isLow, 'critical': isCritical }">
    <div class="timer-icon">⏱️</div>
    <div class="timer-text">
      Session expire dans: <strong>{{ seconds }}s</strong>
    </div>
    <div class="progress-bar">
      <div class="progress-fill" :style="{ width: `${(seconds / 20) * 100}%` }"></div>
    </div>
  </div>
</template>

<style scoped>
.countdown-timer {
  background: #e3f2fd;
  color: #0d47a1;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;
}

.countdown-timer.low {
  background: #fff3cd;
  color: #856404;
}

.countdown-timer.critical {
  background: #f8d7da;
  color: #721c24;
  animation: pulse 1s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.8; }
  100% { opacity: 1; }
}

.timer-text {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 1.125rem;
  z-index: 1;
  position: relative;
}

.timer-icon {
  display: none;
}

.progress-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: rgba(0, 0, 0, 0.1);
}

.progress-fill {
  height: 100%;
  background: currentColor;
  transition: width 1s linear;
}
</style>
