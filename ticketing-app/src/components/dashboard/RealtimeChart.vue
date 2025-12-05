<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
      <div class="flex items-center gap-2">
        <span :class="[
          'w-2 h-2 rounded-full',
          isLive ? 'bg-green-500 animate-pulse' : 'bg-gray-400'
        ]"></span>
        <span class="text-sm text-gray-600">{{ isLive ? 'Live' : 'Paused' }}</span>
      </div>
    </div>

    <div class="relative h-64">
      <canvas ref="chartCanvas"></canvas>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import {
  Chart,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

// Register Chart.js components
Chart.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

interface DataPoint {
  label: string
  value: number
}

interface Props {
  title: string
  data: DataPoint[]
  maxPoints?: number
  isLive?: boolean
  color?: string
}

const props = withDefaults(defineProps<Props>(), {
  maxPoints: 20,
  isLive: true,
  color: '#3B82F6'
})

const chartCanvas = ref<HTMLCanvasElement | null>(null)
let chart: Chart | null = null

onMounted(() => {
  if (chartCanvas.value) {
    initChart()
  }
})

onUnmounted(() => {
  if (chart) {
    chart.destroy()
  }
})

watch(() => props.data, (newData) => {
  if (chart) {
    updateChart(newData)
  }
}, { deep: true })

function initChart() {
  if (!chartCanvas.value) return

  const ctx = chartCanvas.value.getContext('2d')
  if (!ctx) return

  chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.data.map(d => d.label),
      datasets: [{
        label: props.title,
        data: props.data.map(d => d.value),
        borderColor: props.color,
        backgroundColor: `${props.color}20`,
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointRadius: 3,
        pointHoverRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          mode: 'index',
          intersect: false,
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: '#f3f4f6'
          }
        }
      },
      animation: {
        duration: 750,
        easing: 'easeInOutQuart'
      }
    }
  })
}

function updateChart(newData: DataPoint[]) {
  if (!chart) return

  // Limit data points
  const limitedData = newData.slice(-props.maxPoints)

  chart.data.labels = limitedData.map(d => d.label)
  chart.data.datasets[0].data = limitedData.map(d => d.value)
  chart.update('active')
}
</script>
