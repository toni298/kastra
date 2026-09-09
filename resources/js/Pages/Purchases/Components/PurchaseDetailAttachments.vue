<script setup>
import { FileText, Paperclip } from 'lucide-vue-next'
import DataPanel from '@/Components/UI/DataPanel.vue'

defineProps({
  attachments: { type: Array, default: () => [] },
})
</script>

<template>
  <DataPanel>
    <header class="border-b border-slate-100 px-5 py-4 dark:border-[#29476b]">
      <div class="flex items-center gap-3">
        <span
          class="grid size-9 place-items-center rounded-xl bg-cyan-50 text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-300"
        >
          <Paperclip :size="18" />
        </span>
        <div>
          <h3 class="font-semibold text-slate-950 dark:text-white">Lampiran</h3>
          <p class="mt-0.5 text-xs text-slate-500">Dokumen pendukung pembelian.</p>
        </div>
      </div>
    </header>
    <div v-if="attachments.length" class="divide-y divide-slate-100 dark:divide-[#29476b]">
      <div v-for="file in attachments" :key="file.name" class="flex items-center gap-3 px-5 py-4">
        <span
          class="grid size-9 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500 dark:bg-[#0a1b33]"
        >
          <FileText :size="17" />
        </span>
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
            {{ file.name }}
          </p>
          <p class="mt-1 text-xs text-slate-500">{{ file.type }} · {{ file.size }}</p>
        </div>
      </div>
    </div>
    <div v-else class="p-5 text-center">
      <Paperclip class="mx-auto size-8 text-slate-300" />
      <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">Tidak ada lampiran</p>
      <p class="mt-1 text-xs text-slate-500">Dokumen pendukung belum tersedia.</p>
    </div>
  </DataPanel>
</template>
