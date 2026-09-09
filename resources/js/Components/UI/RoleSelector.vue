<script setup>
const props = defineProps({
  modelValue: { type: String, default: '' },
  roles: { type: Array, required: true }, // array of role objects with id & name
  label: { type: String, default: 'Role' },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="space-y-2">
    <label v-if="label" class="block text-sm font-medium text-slate-900 dark:text-white">
      {{ label }}
      <span class="text-red-500">*</span>
    </label>

    <select
      :value="modelValue"
      :disabled="disabled"
      class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:bg-slate-100 disabled:text-slate-500 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white dark:placeholder-slate-500 dark:focus:border-emerald-400"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <option value="">Pilih Role</option>
      <option v-for="role in roles" :key="role.id" :value="role.name">
        {{ role.name }}
      </option>
    </select>

    <div v-if="error" class="text-sm text-red-500">
      {{ error }}
    </div>
  </div>
</template>
