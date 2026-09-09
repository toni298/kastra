<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ShoppingCart, Package, Plus, Receipt, ArrowRightLeft, BarChart3 } from '@lucide/vue'
import { useRoute } from 'ziggy-js'

const page = usePage()
const route = useRoute(page.props.ziggy)

const quickActions = [
  { label: 'Buat Penjualan', icon: ShoppingCart, route: 'dashboard' },
  { label: 'Buat Pembelian', icon: Package, route: 'dashboard' },
  { label: 'Tambah Produk', icon: Plus, route: 'dashboard' },
  { label: 'Catat Pengeluaran', icon: Receipt, route: 'dashboard' },
  { label: 'Transfer Bank', icon: ArrowRightLeft, route: 'dashboard' },
  { label: 'Lihat Laporan', icon: BarChart3, route: 'dashboard' },
]
</script>

<template>
  <div
    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"
  >
    <div class="mb-5 flex items-center justify-between gap-4">
      <h2 class="text-lg font-semibold leading-6 tracking-tight text-slate-900 dark:text-white">
        Aksi Cepat
      </h2>
      <Link
        href="#"
        class="text-sm font-semibold leading-5 text-emerald-600 hover:underline dark:text-emerald-400"
        >Edit</Link
      >
    </div>

    <div class="grid grid-cols-2 gap-3">
      <Link
        v-for="action in quickActions"
        :key="action.label"
        :href="route(action.route)"
        class="flex flex-col items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3 text-center transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-700 dark:hover:bg-slate-600"
      >
        <component :is="action.icon" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
        <span class="text-xs font-semibold leading-4 text-slate-700 dark:text-slate-300">{{
          action.label
        }}</span>
      </Link>
    </div>
  </div>
</template>
