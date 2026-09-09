<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
})
</script>

<template>
  <header
    class="flex items-start gap-3 border-b border-slate-100 px-5 py-4 dark:border-[#29476b] sm:px-6"
  >
    <span
      v-if="$slots.icon"
      class="grid size-9 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
    >
      <slot name="icon"></slot>
    </span>
    <div>
      <h3 class="font-semibold text-slate-900 dark:text-white">{{ title }}</h3>
      <p v-if="description" class="mt-0.5 text-sm text-slate-500 dark:text-slate-300">
        {{ description }}
      </p>
    </div>
  </header>
</template>
