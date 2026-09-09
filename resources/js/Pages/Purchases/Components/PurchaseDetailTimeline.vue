<script setup>
import { Clock3 } from 'lucide-vue-next'
import DataPanel from '@/Components/UI/DataPanel.vue'

defineProps({
  events: { type: Array, default: () => [] },
})
</script>

<template>
  <DataPanel>
    <header class="border-b border-slate-100 px-5 py-4 dark:border-[#29476b]">
      <div class="flex items-center gap-3">
        <span
          class="grid size-9 place-items-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300"
        >
          <Clock3 :size="18" />
        </span>
        <div>
          <h3 class="font-semibold text-slate-950 dark:text-white">Timeline</h3>
          <p class="mt-0.5 text-xs text-slate-500">Riwayat aktivitas transaksi.</p>
        </div>
      </div>
    </header>
    <ol v-if="events.length" class="p-5">
      <li
        v-for="(event, index) in events"
        :key="`${event.label}-${index}`"
        class="relative flex gap-4 pb-6 last:pb-0"
      >
        <span
          v-if="index < events.length - 1"
          class="absolute left-[7px] top-4 h-full w-px bg-emerald-200 dark:bg-emerald-400/25"
        ></span>
        <span
          class="relative mt-1.5 size-4 shrink-0 rounded-full border-4 border-emerald-100 bg-emerald-600 dark:border-emerald-400/20 dark:bg-emerald-400"
        ></span>
        <div>
          <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ event.label }}</p>
          <p class="mt-1 text-xs text-slate-500">{{ event.time }}</p>
        </div>
      </li>
    </ol>
    <p v-else class="p-5 text-sm text-slate-500">Tidak ada data</p>
  </DataPanel>
</template>
