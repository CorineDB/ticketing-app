<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PublicLayout from '@/components/layout/PublicLayout.vue';
import { CheckCircleIcon, XCircleIcon, ClockIcon, CircleQuestionMarkIcon } from 'lucide-vue-next';//'@heroicons/vue/24/solid';
import { useAuthStore } from '@/stores/auth'; // Import du store d'authentification

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore(); // Utilisation du store

// Correction: Suppression des génériques explicites <string> pour éviter les erreurs de parsing TSX
const status = ref('');
const message = ref('');
const transactionId = ref('');
const loading = ref(true);

onMounted(() => {
  // Extract parameters from URL
  status.value = (route.query.status as string) || 'unknown';
  message.value = (route.query.message as string) || '';
  transactionId.value = (route.query.transaction_id as string) || '';
  
  loading.value = false;
});

// Logique stricte basée sur les statuts: 'approved', 'pending', 'canceled'
const isSuccess = computed(() => status.value === 'approved');
const isPending = computed(() => status.value === 'pending');
const isCanceled = computed(() => status.value === 'canceled');
const isUnknown = computed(() => !isSuccess.value && !isPending.value && !isCanceled.value);

// Vérifie si l'utilisateur est authentifié pour afficher le bouton Dashboard
const canAccessDashboard = computed(() => authStore.isAuthenticated);

const statusIcon = computed(() => {
  if (isSuccess.value) return CheckCircleIcon;
  if (isCanceled.value) return XCircleIcon;
  if (isPending.value) return ClockIcon;
  return CircleQuestionMarkIcon; // Icône pour statut inconnu
});

const statusColor = computed(() => {
  if (isSuccess.value) return 'text-green-500';
  if (isCanceled.value) return 'text-red-500';
  if (isPending.value) return 'text-yellow-500';
  return 'text-gray-500';
});

const title = computed(() => {
  if (isSuccess.value) return 'Paiement Réussi !';
  if (isPending.value) return 'Paiement en Attente';
  if (isCanceled.value) return 'Paiement Annulé';
  return 'Statut Inconnu';
});

const description = computed(() => {
  if (isSuccess.value) return 'Votre transaction a été validée avec succès. Vous allez recevoir votre billet par email dans quelques instants.';
  if (isPending.value) return 'Votre paiement est en cours de traitement. Vous recevrez une confirmation par email dès qu\'il sera validé. Veuillez ne pas repayer tout de suite.';
  if (isCanceled.value) return message.value || 'Le paiement a été annulé. Vous pouvez réessayer si vous le souhaitez.';
  return `Nous n'avons pas pu déterminer le statut exact de votre transaction (Statut: ${status.value}). Veuillez vérifier vos emails ou contacter le support.`;
});

function goToEvents() {
  router.push('/events');
}

function goToDashboard() {
  router.push('/dashboard/tickets'); // Redirection plus précise vers la liste des billets
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
          
          <!-- Guest Message (Only for guests on success/pending) -->
          <div v-if="!canAccessDashboard && (isSuccess || isPending)" class="bg-blue-50 p-4 rounded-md text-sm text-blue-700">
            <p><strong>Important :</strong> Vérifiez votre boîte de réception (et vos spams) pour récupérer votre billet.</p>
          </div>

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
            
            <!-- Afficher le bouton Dashboard UNIQUEMENT si l'utilisateur est connecté et que ce n'est pas un échec -->
            <button
              v-if="(isSuccess || isPending) && canAccessDashboard" 
              @click="goToDashboard"
              class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-black bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 w-full sm:w-auto"
            >
              {{ isPending ? 'Suivre mon statut' : 'Voir mes billets' }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>