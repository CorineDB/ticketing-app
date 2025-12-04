<template>
  <div class="step-content">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-gray-900">🚪 Gates (Portes d'accès)</h2>
      <button
        @click="addGate"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center gap-2"
      >
        <PlusIcon class="w-5 h-5" />
        Ajouter une gate
      </button>
    </div>

    <!-- Gates List -->
    <div v-if="localData.gates && localData.gates.length > 0" class="space-y-4">
      <div
        v-for="(gate, index) in localData.gates"
        :key="index"
        class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <h3 class="font-semibold text-gray-900">{{ gate.name || `Gate ${index + 1}` }}</h3>
            <span
              :class="[
                'px-2 py-1 text-xs font-semibold rounded-full',
                gate.type === 'entry' ? 'bg-green-100 text-green-700' :
                gate.type === 'exit' ? 'bg-red-100 text-red-700' :
                'bg-orange-100 text-orange-700'
              ]"
            >
              {{ gate.type === 'entry' ? '🟢 Entrée' : gate.type === 'exit' ? '🔴 Sortie' : '🟡 Mixte' }}
            </span>
          </div>
          <button
            @click="removeGate(index)"
            class="text-red-600 hover:text-red-700"
          >
            <XIcon class="w-5 h-5" />
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Nom de la gate <span class="text-red-500">*</span>
            </label>
            <input
              v-model="gate.name"
              type="text"
              placeholder="Ex: Entrée VIP, Sortie Principale"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Type de gate <span class="text-red-500">*</span>
            </label>
            <select
              v-model="gate.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="entry">🟢 Entrée</option>
              <option value="exit">🔴 Sortie</option>
              <option value="mixed">🟡 Mixte (Entrée/Sortie)</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Statut opérationnel
            </label>
            <select
              v-model="gate.operational_status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="active">🟢 Actif</option>
              <option value="inactive">🔴 Inactif</option>
              <option value="paused">🟡 En pause</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Emplacement
            </label>
            <input
              v-model="gate.location"
              type="text"
              placeholder="Ex: Entrée Nord"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Ticket Types Assignment -->
        <div v-if="hasTicketTypes" class="mt-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Types de tickets autorisés
          </label>
          <div class="grid grid-cols-2 gap-2">
            <label
              v-for="ticketType in localData.ticket_types"
              :key="ticketType.name"
              class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
            >
              <input
                v-model="gate.ticket_type_names"
                type="checkbox"
                :value="ticketType.name"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span class="text-sm">{{ ticketType.name }}</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
      <DoorOpenIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune gate configurée</h3>
      <p class="text-gray-600 mb-4">Ajoutez des portes d'accès pour gérer l'entrée et la sortie</p>
      <button
        @click="addGate"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors inline-flex items-center gap-2"
      >
        <PlusIcon class="w-5 h-5" />
        Ajouter la première gate
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { PlusIcon, XIcon, DoorOpenIcon } from 'lucide-vue-next'

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

const hasTicketTypes = computed(() => {
  return localData.value.ticket_types && localData.value.ticket_types.length > 0
})

function addGate() {
  if (!localData.value.gates) {
    localData.value.gates = []
  }
  
  localData.value.gates.push({
    name: '',
    type: 'entry',
    location: '',
    operational_status: 'active',
    ticket_type_names: [],
    schedule: {
      start_time: '08:00',
      end_time: '23:00',
      days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
    }
  })
}

function removeGate(index: number) {
  localData.value.gates.splice(index, 1)
}
</script>
