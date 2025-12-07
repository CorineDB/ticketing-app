<template>
  <div class="step-content">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">📝 Description de l'événement</h2>
    
    <!-- Description -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Description
      </label>
      <textarea
        v-model="localData.description"
        rows="8"
        placeholder="Décrivez votre événement en détail..."
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
      ></textarea>
      <p class="mt-1 text-sm text-gray-500">
        Présentez votre événement de manière attractive pour attirer les participants
      </p>
    </div>

    <!-- Dates -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Date et heure de début <span class="text-red-500">*</span>
        </label>
        <input
          v-model="localData.start_datetime"
          type="datetime-local"
          required
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Date et heure de fin
        </label>
        <input
          v-model="localData.end_datetime"
          type="datetime-local"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>
    </div>

    <!-- Validation Message -->
    <div v-if="!isValid" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
      <p class="text-sm text-yellow-800">
        ⚠️ Veuillez remplir tous les champs obligatoires pour continuer
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'

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

const isValid = computed(() => {
  return localData.value.start_datetime && localData.value.start_datetime.length > 0
})

// Watch for changes and emit
watch(localData, (newValue) => {
  emit('update:modelValue', newValue)
}, { deep: true })
</script>
