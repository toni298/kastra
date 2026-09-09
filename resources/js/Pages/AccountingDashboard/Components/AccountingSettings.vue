<script setup>
import {
  Bot,
  ChevronRight,
  Coins,
  FileDigit,
  Landmark,
  BadgePercent,
  CalendarClock,
} from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
const emit = defineEmits(['open'])
const summary = [
  { label: 'Konfigurasi Lengkap', value: '4 dari 6 Modul', color: 'emerald' },
  { label: 'Perlu Ditinjau', value: '1 Modul', color: 'amber' },
  { label: 'Belum Dikonfigurasi', value: '0 Modul', color: 'slate' },
  { label: 'Terakhir Diperbarui', value: 'Hari Ini', color: 'blue' },
]
const cards = [
  {
    id: 'coa',
    title: 'Chart of Accounts',
    description: '124 akun aktif untuk pembukuan perusahaan.',
    detail: 'Struktur akun siap digunakan.',
    status: 'Siap Digunakan',
    variant: 'success',
    icon: Landmark,
  },
  {
    id: 'tax',
    title: 'Pajak',
    description: 'PPN aktif. Coretax belum dikonfigurasi.',
    detail: 'Lengkapi Coretax agar pelaporan lebih mudah.',
    status: 'Perlu Ditinjau',
    variant: 'warning',
    icon: BadgePercent,
  },
  {
    id: 'fiscal',
    title: 'Tahun Buku',
    description: 'Tahun aktif 2026.',
    detail: 'Periode Juli masih berjalan.',
    status: 'Siap Digunakan',
    variant: 'success',
    icon: CalendarClock,
  },
  {
    id: 'numbers',
    title: 'Nomor Dokumen',
    description: 'Format penomoran otomatis aktif.',
    detail: 'Semua dokumen memiliki format.',
    status: 'Siap Digunakan',
    variant: 'success',
    icon: FileDigit,
  },
  {
    id: 'currency',
    title: 'Mata Uang',
    description: 'IDR sebagai mata uang utama.',
    detail: '2 mata uang aktif.',
    status: 'Siap Digunakan',
    variant: 'success',
    icon: Coins,
  },
  {
    id: 'automation',
    title: 'Otomatisasi Pembukuan',
    description: 'Kastra membuat jurnal dari aktivitas bisnis.',
    detail: 'Penjualan, pembelian, stok, dan kas terhubung.',
    status: 'Aktif',
    variant: 'success',
    icon: Bot,
  },
]
</script>
<template>
  <div>
    <div class="mb-5">
      <h2 class="text-xl font-semibold text-slate-950 dark:text-white">
        Accounting Configuration Center
      </h2>
      <p class="mt-1 text-sm text-slate-500">
        Pantau kesiapan pembukuan perusahaan dan selesaikan konfigurasi yang membutuhkan perhatian.
      </p>
    </div>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <article
        v-for="item in summary"
        :key="item.label"
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ item.label }}</p>
        <p class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">{{ item.value }}</p>
      </article>
    </section>
    <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <button
        v-for="card in cards"
        :key="card.id"
        type="button"
        class="group relative rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-400 hover:shadow-lg dark:border-[#29476b] dark:bg-[#102542] dark:hover:border-emerald-400"
        @click="emit('open', card.id)"
      >
        <div class="flex items-start gap-4">
          <span
            class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"
            ><component :is="card.icon" :size="22"
          /></span>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
              <h3 class="font-semibold text-slate-950 dark:text-white">{{ card.title }}</h3>
              <ChevronRight
                :size="19"
                class="shrink-0 text-slate-400 transition group-hover:translate-x-1 group-hover:text-emerald-600"
              />
            </div>
            <Badge class="mt-2" :variant="card.variant">{{ card.status }}</Badge>
            <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
              {{ card.description }}
            </p>
            <p class="mt-1 text-xs leading-5 text-slate-500">{{ card.detail }}</p>
          </div>
        </div>
      </button>
    </section>
  </div>
</template>
