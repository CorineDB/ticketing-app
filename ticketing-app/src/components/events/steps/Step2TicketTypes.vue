<template>
  <div class="step-content">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-gray-900">🎫 Types de Tickets</h2>
      <button
        @click="addTicketType"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center gap-2"
      >
        <PlusIcon class="w-5 h-5" />
        Ajouter un type
      </button>
    </div>

    <!-- Auto-create Gates Option -->
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
      <label class="flex items-start gap-3 cursor-pointer">
        <input
          v-model="localData.auto_create_gates"
          type="checkbox"
          class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5"
        />
        <div>
          <span class="font-medium text-gray-900">
            Créer automatiquement une gate par type de ticket
          </span>
          <p class="text-sm text-gray-600 mt-1">
            Une porte sera créée pour chaque type de ticket avec assignation automatique
          </p>
        </div>
      </label>
    </div>

    <!-- Ticket Types List -->
    <div v-if="localData.ticket_types.length > 0" class="space-y-4">
      <div
        v-for="(ticket, index) in localData.ticket_types"
        :key="index"
        class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
      >
        <div class="flex items-start justify-between mb-3">
          <h3 class="font-semibold text-gray-900">Type de ticket {{ index + 1 }}</h3>
          <button
            @click="removeTicketType(index)"
            class="text-red-600 hover:text-red-700 text-sm font-medium"
          >
            <XIcon class="w-5 h-5" />
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Nom <span class="text-red-500">*</span>
            </label>
            <input
              v-model="ticket.name"
              type="text"
              placeholder="Ex: VIP, Standard, Étudiant"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Prix (FCFA)
            </label>
            <input
              v-model.number="ticket.price"
              type="number"
              min="0"
              step="100"
              placeholder="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Quota disponible
            </label>
            <input
              v-model.number="ticket.quantity"
              type="number"
              min="0"
              placeholder="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Limite d'utilisation
            </label>
            <input
              v-model.number="ticket.usage_limit"
              type="number"
              min="1"
              placeholder="1"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Description -->
        <div class="mt-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Description
          </label>
          <textarea
            v-model="ticket.description"
            rows="2"
            placeholder="Description de ce type de ticket..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
      <TicketIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun type de ticket</h3>
      <p class="text-gray-600 mb-4">Commencez par ajouter un type de ticket pour votre événement</p>
      <button
        @click="addTicketType"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors inline-flex items-center gap-2"
      >
        <PlusIcon class="w-5 h-5" />
        Ajouter le premier type
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { PlusIcon, XIcon, TicketIcon } from 'lucide-vue-next'

const props = defineProps<{
  modelValue: any
}>()

const emit = defineEmits<{
  'update:modelValue': [value: any]
}>()

const localData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

function addTicketType() {
  if (!localData.value.ticket_types) {
    localData.value.ticket_types = []
  }
  
  localData.value.ticket_types.push({
    name: '',
    description: '',
    price: 0,
    quantity: 100,
    usage_limit: 1,
    is_active: true
  })
}

function removeTicketType(index: number) {
  localData.value.ticket_types.splice(index, 1)
}
</script>
