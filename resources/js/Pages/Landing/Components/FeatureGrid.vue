<script setup>
import { BarChart3, Boxes, Calculator, CreditCard, ShoppingBag, ShoppingCart } from '@lucide/vue'

const features = [
  {
    icon: Boxes,
    title: 'Persediaan',
    description: 'Pantau stok lintas gudang, transfer barang, opname, dan mutasi secara akurat.',
    items: ['Stok real-time', 'Multi-gudang', 'Transfer & opname'],
    tone: 'bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-300',
  },
  {
    icon: ShoppingBag,
    title: 'Penjualan',
    description: 'Kelola POS, sales order, invoice, pembayaran, dan retur dalam satu alur.',
    items: ['Kasir & invoice', 'Pelanggan', 'Retur penjualan'],
    tone: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300',
  },
  {
    icon: ShoppingCart,
    title: 'Pembelian',
    description: 'Kontrol pemesanan, penerimaan barang, tagihan supplier, dan pembayaran.',
    items: ['Purchase order', 'Penerimaan', 'Utang supplier'],
    tone: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300',
  },
  {
    icon: CreditCard,
    title: 'Kas & Bank',
    description: 'Catat penerimaan, pengeluaran, transfer, dan rekonsiliasi saldo dengan rapi.',
    items: ['Arus kas', 'Transfer dana', 'Rekonsiliasi'],
    tone: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300',
  },
  {
    icon: Calculator,
    title: 'Akuntansi',
    description: 'Setiap transaksi membentuk jurnal dan memperbarui pembukuan secara otomatis.',
    items: ['Jurnal otomatis', 'Buku besar', 'Tutup buku'],
    tone: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
  },
  {
    icon: BarChart3,
    title: 'Laporan',
    description: 'Lihat KPI penjualan, persediaan, laba rugi, dan arus kas kapan saja.',
    items: ['Dashboard KPI', 'Laba rugi', 'Analisis real-time'],
    tone: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300',
  },
]
</script>

<template>
  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    <article
      v-for="feature in features"
      :key="feature.title"
      class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-950/5 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-blue-500/40"
    >
      <span
        class="pointer-events-none absolute -right-8 -top-8 size-24 rounded-full bg-blue-100/70 opacity-0 blur-2xl transition-opacity duration-300 group-hover:opacity-100 dark:bg-blue-500/10"
        aria-hidden="true"
      ></span>
      <div
        :class="[
          'relative grid size-11 place-items-center rounded-xl transition-transform duration-300 group-hover:-rotate-3 group-hover:scale-105',
          feature.tone,
        ]"
      >
        <component :is="feature.icon" :size="20" aria-hidden="true" />
      </div>
      <h3 class="relative mt-5 text-lg font-semibold text-slate-950 dark:text-white">
        {{ feature.title }}
      </h3>
      <p class="relative mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
        {{ feature.description }}
      </p>
      <ul class="relative mt-5 space-y-2 border-t border-slate-100 pt-4 dark:border-slate-800">
        <li
          v-for="item in feature.items"
          :key="item"
          class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400"
        >
          <span class="size-1.5 rounded-full bg-blue-500" aria-hidden="true"></span>
          {{ item }}
        </li>
      </ul>
    </article>
  </div>
</template>
