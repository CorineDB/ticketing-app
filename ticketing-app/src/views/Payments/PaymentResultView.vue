<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PublicLayout from '@/components/layout/PublicLayout.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import { CheckCircleIcon, XCircleIcon, ClockIcon } from 'lucide-vue-next';//'@heroicons/vue/24/solid';

const route = useRoute();
const router = useRouter();

const status = ref<string>('');
const message = ref<string>('');
const transactionId = ref<string>('');
const loading = ref(true);

onMounted(() => {
  // Extract parameters from URL
  status.value = (route.query.status as string) || 'unknown';
  message.value = (route.query.message as string) || '';
  transactionId.value = (route.query.transaction_id as string) || '';
  
  loading.value = false;
});

const isSuccess = computed(() => status.value === 'approved' || status.value === 'approved');
const isFailed = computed(() => status.value === 'declined' || status.value === 'canceled' || status.value === 'error');

const statusIcon = computed(() => {
  if (isSuccess.value) return CheckCircleIcon;
  if (isFailed.value) return XCircleIcon;
  return ClockIcon;
});

const statusColor = computed(() => {
  if (isSuccess.value) return 'text-green-500';
  if (isFailed.value) return 'text-red-500';
  return 'text-yellow-500';
});

const title = computed(() => {
  if (isSuccess.value) return 'Paiement Réussi !';
  if (isFailed.value) return 'Paiement Échoué';
  return 'Statut Inconnu';
});

const description = computed(() => {
  if (isSuccess.value) return 'Votre transaction a été validée avec succès. Vous recevrez votre billet par email dans quelques instants.';
  if (isFailed.value) return message.value || 'Une erreur est survenue lors du traitement de votre paiement. Veuillez réessayer.';
  return 'Nous vérifions le statut de votre transaction. Veuillez patienter.';
});

function goToEvents() {
  router.push('/events');
}

function goToDashboard() {
  router.push('/dashboard');
}
</script>

<template>
  <PublicLayout>
    <div class="min-h-[60vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg text-center">
        
        <div v-if="loading" class="flex flex-col items-center justify-center space-y-4">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
          <p class="text-gray-500">Vérification du paiement...</p>
        </div>

        <div v-else class="flex flex-col items-center space-y-6">
          <!-- Icon -->
          <component 
            :is="statusIcon" 
            class="h-24 w-24" 
            :class="statusColor"
          />

          <!-- Title -->
          <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
            {{ title }}
          </h2>

          <!-- Description -->
          <p class="text-lg text-gray-600">
            {{ description }}
          </p>

          <!-- Transaction ID -->
          <div v-if="transactionId" class="bg-gray-50 px-4 py-2 rounded-md">
            <span class="text-sm text-gray-500">ID Transaction : </span>
            <span class="text-sm font-mono font-medium text-gray-700">{{ transactionId }}</span>
          </div>

          <!-- Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 w-full justify-center mt-8">
            <button
              @click="goToEvents"
              class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 w-full sm:w-auto"
            >
              Retour aux événements
            </button>
            
            <button
              v-if="isSuccess"
              @click="goToDashboard"
              class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 w-full sm:w-auto"
            >
              Voir mes billets
            </button>
          </div>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>