<template>
  <DashboardLayout>
    <div class="event-form-container max-w-7xl mx-auto px-4 py-6">
      <!-- Header with Title -->
      <div class="form-header mb-6">
        <input 
          v-model="formData.title" 
          type="text"
          placeholder="Titre de l'événement"
          class="title-input w-full text-4xl font-bold border-none focus:outline-none focus:ring-0 placeholder-gray-300"
          required
        />
      </div>

      <!-- Two Column Layout -->
      <div class="two-column-layout grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (70% - 2 columns) -->
        <div class="main-content lg:col-span-2 space-y-6">
          <!-- Step Progress Indicator -->
          <div class="step-progress bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
              <div 
                v-for="(step, index) in steps" 
                :key="index"
                class="flex items-center flex-1"
              >
                <div class="flex flex-col items-center flex-1">
                  <div 
                    :class="[
                      'w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-all',
                      currentStep >= index + 1 
                        ? 'bg-blue-600 text-white' 
                        : 'bg-gray-200 text-gray-500'
                    ]"
                  >
                    {{ index + 1 }}
                  </div>
                  <span 
                    :class="[
                      'text-xs mt-2 font-medium',
                      currentStep >= index + 1 ? 'text-blue-600' : 'text-gray-500'
                    ]"
                  >
                    {{ step.label }}
                  </span>
                </div>
                <div 
                  v-if="index < steps.length - 1"
                  :class="[
                    'h-1 flex-1 mx-2',
                    currentStep > index + 1 ? 'bg-blue-600' : 'bg-gray-200'
                  ]"
                />
              </div>
            </div>
          </div>

          <!-- Step Content -->
          <div class="step-content bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <component 
              :is="currentStepComponent" 
              v-model="formData"
              @next="nextStep"
              @previous="previousStep"
            />
          </div>

          <!-- Navigation Buttons -->
          <div class="step-navigation flex justify-between">
            <button
              v-if="currentStep > 1"
              @click="previousStep"
              class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors"
            >
              ← Précédent
            </button>
            <div v-else></div>

            <button
              v-if="currentStep < steps.length"
              @click="nextStep"
              :disabled="!isCurrentStepValid"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Suivant →
            </button>
            <button
              v-else
              @click="handleSubmit"
              :disabled="loading"
              class="px-6 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
            >
              <LoaderIcon v-if="loading" class="w-5 h-5 animate-spin" />
              <span>{{ isEditing ? 'Mettre à jour' : 'Créer l\'événement' }}</span>
            </button>
          </div>
        </div>

        <!-- Sidebar (30% - 1 column) -->
        <div class="sidebar-content space-y-4">
          <!-- Publication Panel -->
          <div class="panel bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Publication</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select 
                  v-model="formData.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="draft">📝 Brouillon</option>
                  <option value="published">🚀 Publié</option>
                  <option value="ongoing">🟢 En cours</option>
                  <option value="completed">✅ Terminé</option>
                  <option value="cancelled">❌ Annulé</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Location Panel -->
          <div class="panel bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📍 Localisation</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lieu</label>
                <textarea
                  v-model="formData.location"
                  rows="3"
                  placeholder="Adresse complète de l'événement"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Capacité</label>
                <input
                  v-model.number="formData.capacity"
                  type="number"
                  min="1"
                  placeholder="Nombre de participants"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dress Code</label>
                <input
                  v-model="formData.dress_code"
                  type="text"
                  placeholder="Optionnel"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
              <div class="flex items-center">
                <input
                  v-model="formData.allow_reentry"
                  type="checkbox"
                  id="allow_reentry"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <label for="allow_reentry" class="ml-2 text-sm text-gray-700">
                  Autoriser la re-entrée
                </label>
              </div>
            </div>
          </div>

          <!-- Social Links Panel -->
          <div class="panel bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🔗 Réseaux Sociaux</h3>
            <div class="space-y-3">
              <input
                v-model="formData.social_links.facebook"
                type="url"
                placeholder="Lien Facebook"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
              />
              <input
                v-model="formData.social_links.instagram"
                type="url"
                placeholder="Lien Instagram"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
              />
              <input
                v-model="formData.social_links.twitter"
                type="url"
                placeholder="Lien Twitter"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
              />
            </div>
          </div>

          <!-- Actions Panel -->
          <div class="panel bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Actions</h3>
            <div class="space-y-2">
              <button
                @click="saveDraft"
                :disabled="loading"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors disabled:opacity-50"
              >
                💾 Sauvegarder brouillon
              </button>
              <RouterLink
                :to="{ name: 'events' }"
                class="block w-full px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors"
              >
                Annuler
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Step1Description from '@/components/events/steps/Step1Description.vue'
import Step2TicketTypes from '@/components/events/steps/Step2TicketTypes.vue'
import Step3Gates from '@/components/events/steps/Step3Gates.vue'
import Step4Media from '@/components/events/steps/Step4Media.vue'
import { LoaderIcon } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()

const isEditing = ref(false)
const loading = ref(false)
const currentStep = ref(1)

const steps = [
  { label: 'Description', component: 'Step1Description' },
  { label: 'Tickets', component: 'Step2TicketTypes' },
  { label: 'Gates', component: 'Step3Gates' },
  { label: 'Médias', component: 'Step4Media' }
]

const formData = ref({
  title: '',
  description: '',
  status: 'draft',
  start_datetime: '',
  end_datetime: '',
  location: '',
  capacity: 100,
  dress_code: '',
  allow_reentry: false,
  social_links: {
    facebook: '',
    instagram: '',
    twitter: '',
    linkedin: '',
    tiktok: '',
    website: ''
  },
  ticket_types: [],
  auto_create_gates: false,
  gates: [],
  banner: null,
  gallery: []
})

const currentStepComponent = computed(() => {
  const stepMap: Record<number, any> = {
    1: Step1Description,
    2: Step2TicketTypes,
    3: Step3Gates,
    4: Step4Media
  }
  return stepMap[currentStep.value]
})

const isCurrentStepValid = computed(() => {
  if (currentStep.value === 1) {
    return formData.value.title && formData.value.start_datetime
  }
  if (currentStep.value === 2) {
    return formData.value.ticket_types.length > 0
  }
  return true
})

function nextStep() {
  if (currentStep.value < steps.length && isCurrentStepValid.value) {
    currentStep.value++
  }
}

function previousStep() {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

async function saveDraft() {
  formData.value.status = 'draft'
  await handleSubmit()
}

async function handleSubmit() {
  loading.value = true
  try {
    // TODO: Implement API call
    console.log('Submitting:', formData.value)
    
    // Redirect after success
    // router.push({ name: 'events' })
  } catch (error) {
    console.error('Error submitting event:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (route.params.id) {
    isEditing.value = true
    // TODO: Load event data
  }
})
</script>

<style scoped>
.title-input::placeholder {
  color: #d1d5db;
}

.title-input:focus {
  outline: none;
  border-bottom: 2px solid #3b82f6;
}
</style>
