<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import {
  ChartNoAxesCombined,
  ChartColumnBig,
  ShoppingCart,
  Warehouse,
  Landmark,
  ChevronRight,
} from 'lucide-vue-next'
import { useRoute } from 'ziggy-js'

const page = usePage()
const route = useRoute(page.props.ziggy)

const reports = [
  {
    title: 'Laporan Keuangan',
    description: 'Laporan laba rugi, neraca, arus kas, dan perubahan modal.',
    icon: ChartNoAxesCombined,
    color: 'text-emerald-600',
    background: 'bg-emerald-50 dark:bg-emerald-900/30',
    href: route('dashboard'),
  },
  {
    title: 'Laporan Penjualan',
    description: 'Analisis penjualan, produk terlaris, dan penjualan per pelanggan.',
    icon: ChartColumnBig,
    color: 'text-emerald-600',
    background: 'bg-emerald-50 dark:bg-emerald-900/30',
    href: route('sales.index'),
  },
  {
    title: 'Laporan Pembelian',
    description: 'Analisis pembelian, supplier terbaik, dan histori pembelian.',
    icon: ShoppingCart,
    color: 'text-orange-600',
    background: 'bg-orange-50 dark:bg-orange-900/30',
    href: route('purchases.index'),
  },
  {
    title: 'Laporan Persediaan',
    description: 'Laporan stok, kartu stok, pergerakan stok, dan valuasi persediaan.',
    icon: Warehouse,
    color: 'text-violet-600',
    background: 'bg-violet-50 dark:bg-violet-900/30',
    href: route('inventory.stock'),
  },
  {
    title: 'Laporan Kas & Bank',
    description: 'Mutasi kas, mutasi bank, dan rekonsiliasi kas & bank.',
    icon: Landmark,
    color: 'text-blue-600',
    background: 'bg-blue-50 dark:bg-blue-900/30',
    href: route('dashboard'),
  },
]
</script>

<template>
  <section
    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800"
  >
    <h2 class="text-lg font-semibold leading-6 tracking-tight text-slate-950 dark:text-white">
      Laporan
    </h2>
    <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
      <Link
        v-for="report in reports"
        :key="report.title"
        :href="report.href"
        class="group flex min-h-24 items-start gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-slate-300 hover:shadow-sm dark:border-slate-700 dark:hover:border-slate-600"
      >
        <div :class="['rounded-lg p-2', report.background]">
          <component :is="report.icon" :class="['h-5 w-5', report.color]" />
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-xs font-semibold leading-4 text-slate-900 dark:text-white">
            {{ report.title }}
          </h3>
          <p
            class="mt-1 line-clamp-3 text-[12px] font-medium leading-4 text-slate-500 dark:text-slate-400"
          >
            {{ report.description }}
          </p>
        </div>
        <ChevronRight
          class="mt-auto h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5"
        />
      </Link>
    </div>
  </section>
</template>
