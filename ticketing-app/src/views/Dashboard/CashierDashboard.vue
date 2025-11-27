<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Cashier Dashboard</h1>
      <p class="mt-2 text-gray-600">
        Manage manual ticket sales and payments
      </p>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <RouterLink
        to="/tickets/create"
        class="group bg-gradient-to-br from-green-600 to-emerald-600 text-white rounded-xl p-8 hover:shadow-lg transition-all"
      >
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-bold mb-2">Create Ticket</h3>
            <p class="text-green-100">Manual ticket creation</p>
          </div>
          <PlusCircleIcon class="w-12 h-12 opacity-80 group-hover:scale-110 transition-transform" />
        </div>
      </RouterLink>

      <RouterLink
        to="/orders"
        class="bg-white border border-gray-200 rounded-xl p-8 hover:shadow-lg transition-all group"
      >
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">View Orders</h3>
            <p class="text-gray-600">All transaction history</p>
          </div>
          <ListIcon class="w-12 h-12 text-gray-400 group-hover:text-gray-600 transition-colors" />
        </div>
      </RouterLink>
    </div>

    <!-- Today's Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <StatCard
        title="Sales Today"
        :value="formatCurrency(stats?.sales_today || 0)"
        :icon="DollarSignIcon"
        color="green"
        :loading="loading"
      />
      <StatCard
        title="Tickets Sold"
        :value="stats?.tickets_sold_today || 0"
        :icon="TicketIcon"
        color="blue"
        :loading="loading"
      />
      <StatCard
        title="Cash Payments"
        :value="formatCurrency(stats?.cash_payments || 0)"
        :icon="BanknoteIcon"
        color="emerald"
        :loading="loading"
      />
      <StatCard
        title="Total Orders"
        :value="stats?.total_orders_today || 0"
        :icon="ShoppingCartIcon"
        color="purple"
        :loading="loading"
      />
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Recent Transactions</h2>

      <div v-if="loading" class="space-y-3">
        <div v-for="i in 5" :key="i" class="animate-pulse">
          <div class="h-16 bg-gray-200 rounded"></div>
        </div>
      </div>

      <div v-else-if="recentTransactions.length > 0" class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Order #</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Customer</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Tickets</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Payment</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Amount</th>
              <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900">Time</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="order in recentTransactions"
              :key="order.id"
              class="border-b border-gray-100 hover:bg-gray-50"
            >
              <td class="py-3 px-4 text-sm text-gray-900">#{{ order.order_number }}</td>
              <td class="py-3 px-4 text-sm text-gray-900">{{ order.customer_name }}</td>
              <td class="py-3 px-4 text-sm text-gray-600">{{ order.tickets_count }}</td>
              <td class="py-3 px-4">
                <Badge variant="secondary">{{ order.payment_method }}</Badge>
              </td>
              <td class="py-3 px-4 text-sm font-medium text-gray-900">
                {{ formatCurrency(order.total_amount) }}
              </td>
              <td class="py-3 px-4 text-sm text-gray-600">
                {{ formatTime(order.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-8 text-gray-500">
        No transactions yet today
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import dashboardService from '@/services/dashboardService'
import { formatCurrency, formatTime } from '@/utils/formatters'
import StatCard from '@/components/dashboard/StatCard.vue'
import Badge from '@/components/common/Badge.vue'
import {
  PlusCircleIcon,
  ListIcon,
  DollarSignIcon,
  TicketIcon,
  BanknoteIcon,
  ShoppingCartIcon
} from 'lucide-vue-next'

const stats = ref<any>(null)
const recentTransactions = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const data = await dashboardService.getCashierDashboard()
    stats.value = data.stats
    recentTransactions.value = data.recent_transactions || []
  } catch (error) {
    console.error('Failed to fetch dashboard:', error)
  } finally {
    loading.value = false
  }
})
</script>
