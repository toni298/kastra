<script setup>
import * as icons from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import { reports } from '../Data/accountingData'
const emit = defineEmits(['preview'])
</script>
<template>
  <div>
    <div class="mb-5">
      <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Laporan Keuangan</h2>
      <p class="mt-1 text-sm text-slate-500">
        Laporan terbentuk otomatis dari aktivitas bisnis Anda.
      </p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <article
        v-for="report in reports"
        :key="report.name"
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <span
          class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"
          ><component :is="icons[report.icon]" :size="22"
        /></span>
        <h3 class="mt-4 font-semibold text-slate-950 dark:text-white">{{ report.name }}</h3>
        <p class="mt-2 min-h-[44px] text-sm leading-6 text-slate-500">{{ report.description }}</p>
        <Button class="mt-5" variant="secondary" size="sm" @click="emit('preview', report)"
          >Lihat Laporan</Button
        >
      </article>
    </div>
  </div>
</template>
