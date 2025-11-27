<template>
  <Modal :show="show" @close="closeModal">
    <template #title>
      <h3 class="text-lg font-medium text-gray-900">Organization Details</h3>
    </template>
    <template #body>
      <div v-if="organisateur" class="space-y-4">
        <div>
          <p class="text-sm font-medium text-gray-500">Name:</p>
          <p class="text-lg font-semibold text-gray-900">{{ organisateur.name }}</p>
        </div>
        <div v-if="organisateur.description">
          <p class="text-sm font-medium text-gray-500">Description:</p>
          <p class="text-gray-700">{{ organisateur.description }}</p>
        </div>
        <!-- Add more detail fields as necessary -->
      </div>
      <div v-else class="text-gray-500">
        <p>No organisateur selected.</p>
      </div>
    </template>
    <template #footer>
      <button
        type="button"
        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-100 border border-transparent rounded-md hover:bg-blue-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500"
        @click="closeModal"
      >
        Close
      </button>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import Modal from '@/components/common/Modal.vue' // Assuming a common Modal component exists

interface Organization {
  id?: string;
  name: string;
  description?: string;
  // Add other organisateur properties here
}

const props = defineProps<{
  show: boolean;
  organisateur?: User | null;
}>();

const emit = defineEmits(['update:show']);

const closeModal = () => {
  emit('update:show', false);
};
</script>
