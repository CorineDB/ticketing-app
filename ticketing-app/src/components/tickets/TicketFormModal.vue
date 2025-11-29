<template>
  <Modal :show="show" @close="closeModal">
    <template #title>
      <h3 class="text-lg font-medium text-gray-900">{{ ticket ? 'Edit Ticket' : 'Create Ticket' }}</h3>
    </template>
    <template #body>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div>
          <label for="holderName" class="block text-sm font-medium text-gray-700">Holder Name</label>
          <input
            type="text"
            id="holderName"
            v-model="form.buyer_name"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            required
          />
        </div>
        <div>
          <label for="holderEmail" class="block text-sm font-medium text-gray-700">Holder Email</label>
          <input
            type="email"
            id="holderEmail"
            v-model="form.buyer_email"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            required
          />
        </div>
        <!-- Add more fields as necessary -->
      </form>
    </template>
    <template #footer>
      <button
        type="button"
        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-transparent rounded-md hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500"
        @click="closeModal"
      >
        Cancel
      </button>
      <button
        type="submit"
        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500 ml-3"
        @click="submitForm"
      >
        {{ ticket ? 'Save Changes' : 'Create' }}
      </button>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, watch, reactive } from 'vue'
import Modal from '@/components/common/Modal.vue' // Assuming a common Modal component exists

interface Ticket {
  id?: string;
  buyer_name: string;
  buyer_email: string;
  // Add other ticket properties here
}

const props = defineProps<{
  show: boolean;
  ticket?: Ticket | null;
}>();

const emit = defineEmits(['update:show', 'submit']);

const form = reactive<Ticket>({
  buyer_name: '',
  buyer_email: '',
});

watch(() => props.ticket, (newTicket) => {
  if (newTicket) {
    Object.assign(form, newTicket);
  } else {
    // Reset form for new ticket
    form.id = undefined;
    form.buyer_name = '';
    form.buyer_email = '';
  }
}, { immediate: true });

const closeModal = () => {
  emit('update:show', false);
};

const submitForm = () => {
  emit('submit', form);
  closeModal();
};
</script>
