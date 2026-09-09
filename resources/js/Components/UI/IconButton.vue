<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  variant: { type: String, default: 'default' },
  disabled: { type: Boolean, default: false },
})

const variantClasses = computed(() => {
  const variants = {
    default: 'text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#163354]',
    info: 'text-blue-600 hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-400/10',
    danger: 'text-red-600 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-400/10',
  }

  return variants[props.variant] ?? variants.default
})
</script>

<template>
  <button
    type="button"
    :disabled="disabled"
    :aria-label="label"
    :title="label"
    :class="[
      'grid size-9 place-items-center rounded-lg transition focus:outline-none focus:ring-4 focus:ring-slate-300/30 disabled:cursor-not-allowed disabled:opacity-50',
      variantClasses,
    ]"
  >
    <slot></slot>
  </button>
</template>
