<script setup>
import { ArrowDown, ArrowUp, ArrowUpDown } from '@lucide/vue'

defineProps({
  label: { type: String, required: true },
  active: { type: Boolean, default: false },
  direction: {
    type: String,
    default: 'asc',
    validator: (value) => ['asc', 'desc'].includes(value),
  },
  align: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'center', 'right'].includes(value),
  },
})

const emit = defineEmits(['sort'])
</script>

<template>
  <button
    type="button"
    :class="[
      'group -mx-5 inline-flex h-12 w-[calc(100%+2.5rem)] items-center gap-1.5 px-5 text-xs font-semibold uppercase tracking-wide transition-colors hover:bg-emerald-100/70 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500/30 [&_svg]:shrink-0 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300',
      active ? 'text-emerald-700 dark:text-emerald-300' : 'text-inherit',
      align === 'center' ? 'justify-center' : align === 'right' ? 'justify-end' : 'justify-start',
    ]"
    @click="emit('sort')"
  >
    <slot name="icon"></slot>
    {{ label }}
    <ArrowUp v-if="active && direction === 'asc'" :size="14" />
    <ArrowDown v-else-if="active && direction === 'desc'" :size="14" />
    <ArrowUpDown v-else :size="14" />
  </button>
</template>
