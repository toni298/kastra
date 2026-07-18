<template>
  <div class="w-full">
    <label v-if="label" :for="id" class="block text-sm font-medium text-secondary-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      :class="[
        'w-full px-3 py-2 border rounded-lg transition duration-150',
        'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500',
        error ? 'border-red-500' : 'border-secondary-300',
        disabled ? 'bg-secondary-100 cursor-not-allowed' : 'bg-white',
      ]"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  id: { type: String, default: () => 'input-' + Math.random().toString(36).substr(2, 9) },
  modelValue: { type: [String, Number], default: '' },
  type: { type: String, default: 'text' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>
