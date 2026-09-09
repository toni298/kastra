<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center rounded-xl font-medium transition duration-150 focus:outline-none focus:ring-4',
      sizeClasses,
      variantClasses,
      { 'cursor-not-allowed opacity-60': disabled || loading },
    ]"
  >
    <Icon v-if="loading" name="loader-2" class="mr-2 size-4 animate-spin" />
    <slot></slot>
  </button>
</template>

<script setup>
import { computed } from 'vue'
import Icon from './Icon.vue'

const props = defineProps({
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' },
  size: { type: String, default: 'md' },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-2.5 text-sm',
    lg: 'px-6 py-3 text-base',
  }

  return sizes[props.size] ?? sizes.md
})

const variantClasses = computed(() => {
  const variants = {
    primary:
      'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 focus:ring-emerald-500/20 dark:bg-emerald-500 dark:text-[#071426] dark:hover:bg-emerald-400',
    secondary:
      'border border-slate-300 bg-white text-slate-800 hover:bg-slate-50 focus:ring-slate-300/30 dark:border-[#29476b] dark:bg-[#102542] dark:text-white dark:hover:bg-[#163354]',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500/20',
    ghost:
      'bg-transparent text-slate-700 hover:bg-slate-100 focus:ring-slate-300/30 dark:text-slate-200 dark:hover:bg-[#102542]',
  }

  return variants[props.variant] ?? variants.primary
})
</script>
