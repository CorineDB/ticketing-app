<template>
  <Modal :show="show" @close="closeModal">
    <template #title>
      <h3 class="text-lg font-medium text-gray-900">{{ organization ? 'Edit Organization' : 'Create Organization' }}</h3>
    </template>
    <template #body>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Organization Name</label>
          <input
            type="text"
            id="name"
            v-model="form.name"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            required
          />
        </div>
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          ></textarea>
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
        {{ organization ? 'Save Changes' : 'Create' }}
      </button>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, watch, reactive } from 'vue'
import Modal from '@/components/common/Modal.vue' // Assuming a common Modal component exists

interface Organization {
  id?: string;
  name: string;
  description?: string;
  // Add other organization properties here
}

const props = defineProps<{
  show: boolean;
  organization?: Organization | null;
}>();

const emit = defineEmits(['update:show', 'submit']);

const form = reactive<Organization>({
  name: '',
  description: '',
});

watch(() => props.organization, (newOrg) => {
  if (newOrg) {
    Object.assign(form, newOrg);
  } else {
    // Reset form for new organization
    form.id = undefined;
    form.name = '';
    form.description = '';
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
