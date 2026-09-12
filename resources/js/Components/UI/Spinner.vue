<script setup>
import { LoaderCircle } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps({
  size: { type: String, default: 'md' },
  color: { type: String, default: 'emerald' },
  label: { type: String, default: 'Memuat data...' },
  showLabel: { type: Boolean, default: true },
})

const sizeClasses = computed(() => ({
  sm: 'size-4',
  md: 'size-7',
  lg: 'size-10',
})[props.size] ?? 'size-7')

const colorClasses = computed(() => ({
  slate: 'text-slate-500 dark:text-slate-300',
  emerald: 'text-emerald-600 dark:text-emerald-300',
  blue: 'text-blue-600 dark:text-blue-300',
  white: 'text-white',
})[props.color] ?? 'text-emerald-600 dark:text-emerald-300')
</script>

<template>
  <span class="inline-flex items-center justify-center gap-3" role="status" :aria-label="label">
    <LoaderCircle :class="[sizeClasses, colorClasses, 'animate-spin']" aria-hidden="true" />
    <span v-if="showLabel" class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ label }}</span>
  </span>
</template>
