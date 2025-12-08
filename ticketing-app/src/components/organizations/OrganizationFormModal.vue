<template>
  <Modal
    :model-value="modelValue"
    :title="organisateur ? 'Edit Organization' : 'Create Organization'"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Organization Name *</label>
        <input
          type="text"
          id="name"
          v-model="form.name"
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          required
        />
      </div>
      
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
        <input
          type="email"
          id="email"
          v-model="form.email"
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          required
        />
      </div>

      <div>
        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
        <input
          type="tel"
          id="phone"
          v-model="form.phone"
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
        />
      </div>

      <div v-if="!organisateur">
        <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
        <input
          type="password"
          id="password"
          v-model="form.password"
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          :required="!organisateur"
          minlength="8"
        />
      </div>

      <div class="flex justify-end gap-3 pt-4">
        <button
          type="button"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
          @click="$emit('update:modelValue', false)"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
        >
          {{ organisateur ? 'Save Changes' : 'Create' }}
        </button>
      </div>
    </form>
  </Modal>
</template>

<script setup lang="ts">
import { watch, reactive } from 'vue'
import Modal from '@/components/common/Modal.vue'
import type { Organization } from '@/types/api'

const props = defineProps<{
  modelValue: boolean;
  organisateur?: Organization | null;
}>();

const emit = defineEmits(['update:modelValue', 'submit']);

const form = reactive<any>({
  name: '',
  email: '',
  phone: '',
  password: '',
});

watch(() => props.organisateur, (newOrg) => {
  if (newOrg) {
    form.name = newOrg.name || '';
    form.email = newOrg.email || '';
    form.phone = newOrg.phone || '';
  } else {
    // Reset form for new organisateur
    form.name = '';
    form.email = '';
    form.phone = '';
    form.password = '';
  }
}, { immediate: true });

const submitForm = () => {
  const data = { ...form };
  
  // Remove password if it's empty (for updates)
  if (!data.password || data.password.trim() === '') {
    delete data.password;
  }
  
  emit('submit', data);
  emit('update:modelValue', false);
};
</script>
