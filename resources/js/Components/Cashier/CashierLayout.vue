<script setup>
import CashierHeader from './CashierHeader.vue'

defineProps({
  fullWidth: { type: Boolean, default: false },
})
</script>

<template>
  <div class="min-h-screen bg-[#f8fafc] text-slate-900">
    <CashierHeader />
    <main
      :class="[
        'mx-auto max-w-[1600px] p-4 xl:p-7 xl:grid-cols-[minmax(0,1.7fr)_minmax(390px,.9fr)]',
        fullWidth ? 'space-y-5' : 'grid gap-5',
      ]"
    >
      <slot />
    </main>
  </div>
</template>
