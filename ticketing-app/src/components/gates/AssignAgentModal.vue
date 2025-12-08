<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/25 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
              <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 flex items-center justify-between">
                <span>Assigner un agent</span>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                  <XIcon class="w-5 h-5" />
                </button>
              </DialogTitle>

              <div class="mt-4">
                <p class="text-sm text-gray-500 mb-4">
                  Sélectionnez un agent de contrôle pour la porte <strong>{{ gate?.name }}</strong>.
                </p>

                <div v-if="loading" class="flex justify-center py-8">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>

                <div v-else-if="error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4">
                  {{ error }}
                </div>

                <div v-else class="space-y-4">
                  <!-- Search -->
                  <div class="relative">
                    <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                      v-model="searchQuery"
                      type="text"
                      placeholder="Rechercher un agent..."
                      class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>

                  <!-- Agent List -->
                  <div class="max-h-60 overflow-y-auto space-y-2">
                    <div
                      v-for="agent in filteredAgents"
                      :key="agent.id"
                      @click="selectedAgentId = agent.id"
                      :class="[
                        'p-3 rounded-lg border cursor-pointer transition-colors flex items-center gap-3',
                        selectedAgentId === agent.id
                          ? 'border-blue-500 bg-blue-50'
                          : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'
                      ]"
                    >
                      <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-medium text-xs">
                        {{ getInitials(agent.name) }}
                      </div>
                      <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">{{ agent.name }}</div>
                        <div class="text-xs text-gray-500">{{ agent.email }}</div>
                      </div>
                      <div v-if="selectedAgentId === agent.id" class="text-blue-600">
                        <CheckCircleIcon class="w-5 h-5" />
                      </div>
                    </div>

                    <div v-if="filteredAgents.length === 0" class="text-center py-4 text-gray-500 text-sm">
                      Aucun agent trouvé
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-6 flex justify-end gap-3">
                <button
                  type="button"
                  class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                  @click="closeModal"
                >
                  Annuler
                </button>
                <button
                  type="button"
                  class="inline-flex justify-center rounded-lg border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="!selectedAgentId || submitting"
                  @click="handleSubmit"
                >
                  <span v-if="submitting">Assignation...</span>
                  <span v-else>Assigner</span>
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue'
import { XIcon, SearchIcon, CheckCircleIcon } from 'lucide-vue-next'
import type { Gate } from '@/types/api'
import agentService, { type Agent } from '@/services/agentService'

const props = defineProps<{
  modelValue: boolean
  gate: Gate | null
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'submit', agentId: string): void
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const loading = ref(false)
const submitting = ref(false)
const error = ref('')
const agents = ref<Agent[]>([])
const searchQuery = ref('')
const selectedAgentId = ref<string | null>(null)

const filteredAgents = computed(() => {
  if (!searchQuery.value) return agents.value
  const query = searchQuery.value.toLowerCase()
  return agents.value.filter(agent => 
    agent.name.toLowerCase().includes(query) || 
    agent.email.toLowerCase().includes(query)
  )
})

function getInitials(name: string): string {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

async function fetchAgents() {
  loading.value = true
  error.value = ''
  try {
    const response = await agentService.getAll()
    agents.value = response
  } catch (e) {
    console.error('Failed to fetch agents:', e)
    error.value = 'Impossible de charger la liste des agents.'
  } finally {
    loading.value = false
  }
}

function closeModal() {
  isOpen.value = false
  searchQuery.value = ''
  selectedAgentId.value = null
  error.value = ''
}

async function handleSubmit() {
  if (!selectedAgentId.value) return
  
  submitting.value = true
  try {
    emit('submit', selectedAgentId.value)
    closeModal()
  } catch (e) {
    console.error(e)
  } finally {
    submitting.value = false
  }
}

watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    fetchAgents()
    if (props.gate?.pivot?.agent_id) {
      selectedAgentId.value = props.gate.pivot.agent_id
    }
  }
})
</script>
