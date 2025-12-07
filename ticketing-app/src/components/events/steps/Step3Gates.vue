<template>
  <div class="step-content">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-gray-900">🚪 Gates (Portes d'accès)</h2>
      <div class="flex gap-2">
        <button
          @click="showGateSelector = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center gap-2"
        >
          <PlusIcon class="w-5 h-5" />
          Assigner une gate
        </button>
        <button
          @click="showCreateGate = true"
          class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors flex items-center gap-2"
        >
          <PlusIcon class="w-5 h-5" />
          Créer nouvelle gate
        </button>
      </div>
    </div>

    <!-- Assigned Gates List -->
    <div v-if="localData.gates && localData.gates.length > 0" class="space-y-4">
      <div
        v-for="(gateAssignment, index) in localData.gates"
        :key="index"
        class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <h3 class="font-semibold text-gray-900">{{ getGateName(gateAssignment.gate_id) }}</h3>
            <span
              :class="[
                'px-2 py-1 text-xs font-semibold rounded-full',
                gateAssignment.operational_status === 'active' ? 'bg-green-100 text-green-700' :
                gateAssignment.operational_status === 'paused' ? 'bg-orange-100 text-orange-700' :
                'bg-red-100 text-red-700'
              ]"
            >
              {{ gateAssignment.operational_status === 'active' ? '🟢 Actif' : 
                 gateAssignment.operational_status === 'paused' ? '🟡 En pause' : '🔴 Inactif' }}
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
              Statut opérationnel
            </label>
            <select
              v-model="gateAssignment.operational_status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="active">🟢 Actif</option>
              <option value="inactive">🔴 Inactif</option>
              <option value="paused">🟡 En pause</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Agent assigné
            </label>
            <div class="flex gap-2">
              <select
                v-model="gateAssignment.agent_id"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option :value="undefined">Aucun agent</option>
                <option v-for="agent in availableAgents" :key="agent.id" :value="agent.id">
                  {{ agent.name }}
                </option>
              </select>
              <button
                @click="showCreateAgent = true"
                class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                title="Créer un nouvel agent"
              >
                <PlusIcon class="w-5 h-5" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Heure d'ouverture
            </label>
            <input
              v-model="gateAssignment.schedule.start_time"
              type="time"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Heure de fermeture
            </label>
            <input
              v-model="gateAssignment.schedule.end_time"
              type="time"
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
                v-model="gateAssignment.ticket_type_names"
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
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune gate assignée</h3>
      <p class="text-gray-600 mb-4">Assignez des portes d'accès existantes ou créez-en de nouvelles</p>
      <button
        @click="showGateSelector = true"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors inline-flex items-center gap-2"
      >
        <PlusIcon class="w-5 h-5" />
        Assigner une gate
      </button>
    </div>

    <!-- Gate Selector Modal -->
    <div v-if="showGateSelector" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showGateSelector = false">
      <div class="bg-white rounded-xl p-6 max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
        <h3 class="text-xl font-bold mb-4">Sélectionner une gate</h3>
        
        <div v-if="loadingGates" class="text-center py-8">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        </div>

        <div v-else-if="availableGates.length === 0" class="text-center py-8">
          <p class="text-gray-600">Aucune gate disponible. Créez-en une nouvelle.</p>
        </div>

        <div v-else class="space-y-2">
          <button
            v-for="gate in availableGates"
            :key="gate.id"
            @click="assignGate(gate)"
            class="w-full p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors text-left"
          >
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-semibold">{{ gate.name }}</h4>
                <p class="text-sm text-gray-600">{{ gate.location }}</p>
              </div>
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
          </button>
        </div>

        <button
          @click="showGateSelector = false"
          class="mt-4 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          Annuler
        </button>
      </div>
    </div>

    <!-- Create Gate Modal -->
    <div v-if="showCreateGate" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showCreateGate = false">
      <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Créer une nouvelle gate</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
            <input
              v-model="newGate.name"
              type="text"
              placeholder="Ex: Porte A"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Emplacement</label>
            <input
              v-model="newGate.location"
              type="text"
              placeholder="Ex: Bâtiment Nord"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select
              v-model="newGate.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="entrance">🟢 Entrée</option>
              <option value="exit">🔴 Sortie</option>
              <option value="vip">💎 VIP</option>
              <option value="other">🟡 Autre</option>
            </select>
          </div>

          <div class="flex gap-2">
            <button
              @click="createNewGate"
              :disabled="!newGate.name"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Créer et assigner
            </button>
            <button
              @click="showCreateGate = false"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Annuler
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Agent Modal -->
    <div v-if="showCreateAgent" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showCreateAgent = false">
      <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Créer un nouvel agent</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
            <input
              v-model="newAgent.name"
              type="text"
              placeholder="Ex: Jean Dupont"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="newAgent.email"
              type="email"
              placeholder="Ex: jean.dupont@example.com"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
            <input
              v-model="newAgent.phone"
              type="tel"
              placeholder="Ex: +33 6 12 34 56 78"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="flex gap-2">
            <button
              @click="createNewAgent"
              :disabled="!newAgent.name || !newAgent.email"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Créer l'agent
            </button>
            <button
              @click="showCreateAgent = false"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Annuler
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { PlusIcon, XIcon, DoorOpenIcon } from 'lucide-vue-next'
import gateService from '@/services/gateService'
import agentService from '@/services/agentService'

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

const showGateSelector = ref(false)
const showCreateGate = ref(false)
const showCreateAgent = ref(false)
const loadingGates = ref(false)
const availableGates = ref<any[]>([])
const availableAgents = ref<any[]>([])
const newGate = ref({
  name: '',
  location: '',
  type: 'entrance' as 'entrance' | 'exit' | 'vip' | 'other',
  status: 'active' as 'active' | 'inactive'
})
const newAgent = ref({
  name: '',
  email: '',
  phone: '',
  availability: 'active' as 'active' | 'inactive' | 'on_break'
})

const hasTicketTypes = computed(() => {
  return localData.value.ticket_types && localData.value.ticket_types.length > 0
})

onMounted(async () => {
  await Promise.all([loadGates(), loadAgents()])
})

async function loadGates() {
  try {
    loadingGates.value = true
    const response = await gateService.getAll()
    availableGates.value = response.data || []
  } catch (error) {
    console.error('Error loading gates:', error)
  } finally {
    loadingGates.value = false
  }
}

async function loadAgents() {
  try {
    const agents = await agentService.getAll()
    availableAgents.value = agents
  } catch (error) {
    console.error('Error loading agents:', error)
  }
}

function getGateName(gateId: string): string {
  const gate = availableGates.value.find(g => g.id === gateId)
  return gate ? gate.name : 'Gate inconnue'
}

function assignGate(gate: any) {
  if (!localData.value.gates) {
    localData.value.gates = []
  }

  // Check if already assigned
  if (localData.value.gates.some((g: any) => g.gate_id === gate.id)) {
    alert('Cette gate est déjà assignée à cet événement')
    return
  }

  localData.value.gates.push({
    gate_id: gate.id,
    agent_id: undefined,
    operational_status: 'active',
    ticket_type_names: [],
    schedule: {
      start_time: '08:00',
      end_time: '23:00'
    },
    max_capacity: null
  })

  showGateSelector.value = false
}

async function createNewGate() {
  try {
    const createdGate = await gateService.create(newGate.value)
    availableGates.value.push(createdGate)
    assignGate(createdGate)
    
    // Reset form
    newGate.value = {
      name: '',
      location: '',
      type: 'entrance',
      status: 'active'
    }
    showCreateGate.value = false
  } catch (error) {
    console.error('Error creating gate:', error)
    alert('Erreur lors de la création de la gate')
  }
}

async function createNewAgent() {
  try {
    const createdAgent = await agentService.create(newAgent.value)
    availableAgents.value.push(createdAgent)
    
    // Auto-assign to current gate if we're in a gate context
    // (This could be improved with context tracking)
    
    // Reset form
    newAgent.value = {
      name: '',
      email: '',
      phone: '',
      availability: 'active'
    }
    showCreateAgent.value = false
    
    alert(`Agent ${createdAgent.name} créé avec succès!`)
  } catch (error: any) {
    console.error('Error creating agent:', error)
    const message = error.response?.data?.message || 'Erreur lors de la création de l\'agent'
    alert(message)
  }
}

function removeGate(index: number) {
  localData.value.gates.splice(index, 1)
}
</script>
```
