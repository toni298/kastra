<script setup>
import { Search, X } from '@lucide/vue'

defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Cari data...' },
  ariaLabel: { type: String, default: 'Cari data' },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <label
    class="flex w-full max-w-sm items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 transition focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33]"
  >
    <Search :size="16" class="shrink-0 text-slate-400" />
    <input
      :value="modelValue"
      type="search"
      class="w-full border-0 bg-transparent py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white"
      :placeholder="placeholder"
      :aria-label="ariaLabel"
      @input="emit('update:modelValue', $event.target.value)"
    />
    <button
      v-if="modelValue"
      type="button"
      class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-[#163354] dark:hover:text-white"
      aria-label="Hapus pencarian"
      @click="emit('update:modelValue', '')"
    >
      <X :size="14" />
    </button>
  </label>
</template>
