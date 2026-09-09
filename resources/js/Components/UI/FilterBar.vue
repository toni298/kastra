<template>
  <div
    class="flex flex-col gap-3 border-b border-slate-100 p-4 dark:border-[#29476b] sm:flex-row sm:items-center"
  >
    <div class="min-w-0 flex-1"><slot></slot></div>
    <div v-if="$slots.actions" class="flex flex-wrap items-center gap-3">
      <slot name="actions"></slot>
    </div>
  </div>
</template>
