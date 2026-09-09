<script setup>
import { BookOpenCheck } from 'lucide-vue-next'
import DataPanel from '@/Components/UI/DataPanel.vue'
import { formatCurrency } from '@/Utils/helpers'

defineProps({
  entries: { type: Array, default: () => [] },
})
</script>

<template>
  <DataPanel>
    <header class="border-b border-slate-100 px-5 py-4 dark:border-[#29476b]">
      <div class="flex items-center gap-3">
        <span
          class="grid size-9 place-items-center rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300"
        >
          <BookOpenCheck :size="18" />
        </span>
        <div>
          <h3 class="font-semibold text-slate-950 dark:text-white">Jurnal</h3>
          <p class="mt-0.5 text-xs text-slate-500">Pencatatan akuntansi transaksi.</p>
        </div>
      </div>
    </header>
    <div v-if="entries.length" class="divide-y divide-slate-100 dark:divide-[#29476b]">
      <div
        v-for="entry in entries"
        :key="`${entry.position}-${entry.account}`"
        class="grid grid-cols-[36px_minmax(0,1fr)_auto] gap-3 px-5 py-4"
      >
        <span class="font-semibold text-emerald-700 dark:text-emerald-300">
          {{ entry.position }}
        </span>
        <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
          {{ entry.account }}
        </span>
        <span class="text-sm font-semibold text-slate-900 dark:text-white">
          {{ formatCurrency(entry.amount) }}
        </span>
      </div>
    </div>
    <div v-else class="p-5">
      <div
        class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center dark:border-[#29476b] dark:bg-[#0a1b33]"
      >
        <BookOpenCheck class="mx-auto size-8 text-slate-300" />
        <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">
          Jurnal belum tersedia
        </p>
        <p class="mt-1 text-xs leading-5 text-slate-500">
          Jurnal transaksi akan tampil di sini setelah pencatatan akuntansi tersedia.
        </p>
      </div>
    </div>
  </DataPanel>
</template>
