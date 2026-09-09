<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
})
</script>

<template>
  <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
    <div class="flex items-start gap-3">
      <span
        v-if="$slots.icon"
        class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
      >
        <slot name="icon"></slot>
      </span>
      <div>
        <h2 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ title }}</h2>
        <p v-if="description" class="mt-1 text-sm text-slate-500 dark:text-slate-300">
          {{ description }}
        </p>
      </div>
    </div>
    <div v-if="$slots.actions" class="flex shrink-0 items-center gap-2">
      <slot name="actions"></slot>
    </div>
  </header>
</template>
