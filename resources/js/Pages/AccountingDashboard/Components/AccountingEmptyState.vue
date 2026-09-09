<script setup>
import { Sparkles } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
defineEmits(['start'])
</script>
<template>
  <section
    class="grid min-h-[520px] place-items-center rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
  >
    <div class="max-w-xl">
      <span
        class="mx-auto grid h-20 w-20 place-items-center rounded-3xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
        ><Sparkles :size="36"
      /></span>
      <h1 class="mt-6 text-2xl font-semibold text-slate-950 dark:text-white">
        Selamat Datang di Modul Akuntansi
      </h1>
      <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">
        Kastra akan membuat pembukuan secara otomatis dari setiap transaksi yang Anda lakukan. Anda
        tidak perlu memahami debit, kredit, maupun jurnal untuk mulai menggunakan sistem.
      </p>
      <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
        Semua laporan keuangan akan terbentuk otomatis berdasarkan aktivitas bisnis Anda.
      </p>
      <Button class="mt-7" @click="$emit('start')">Mulai Menggunakan Akuntansi</Button>
    </div>
  </section>
</template>
