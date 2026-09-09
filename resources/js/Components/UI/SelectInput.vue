<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  ariaLabel: { type: String, default: 'Pilih filter' },
  label: { type: String, default: '' },
  required: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="w-full">
    <label v-if="label" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <div class="flex h-[46px] items-center rounded-xl border bg-white transition focus-within:ring-4 dark:bg-[#0a1b33] border-slate-300 focus-within:border-emerald-500 focus-within:ring-emerald-500/10 dark:border-[#29476b]">
      <select
        :value="modelValue"
        class="h-full w-full rounded-xl border-0 bg-transparent py-2.5 pl-3 pr-9 text-sm font-medium text-slate-700 focus:border-transparent focus:outline-none focus:ring-0 dark:bg-transparent dark:text-white"
        :aria-label="ariaLabel"
        :required="required"
        @change="emit('update:modelValue', $event.target.value)"
      >
        <slot></slot>
      </select>
    </div>
  </div>
</template>
