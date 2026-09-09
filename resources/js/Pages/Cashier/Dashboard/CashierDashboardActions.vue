<script setup>
import { Link } from '@inertiajs/vue3'
import { ClipboardList, Plus, Receipt, RotateCcw, ShoppingCart, Users } from 'lucide-vue-next'
const actions = [
  {
    label: 'Kasir (POS)',
    caption: 'Buat transaksi baru',
    route: 'cashier',
    icon: ShoppingCart,
    tone: 'emerald',
  },
  {
    label: 'Transaksi',
    caption: 'Lihat semua transaksi',
    route: 'cashier.sales.tab',
    params: { salesTab: 'transactions' },
    icon: Receipt,
    tone: 'blue',
  },
  {
    label: 'Pelanggan',
    caption: 'Cari pelanggan',
    route: 'cashier.sales.tab',
    params: { salesTab: 'customers' },
    icon: Users,
    tone: 'violet',
  },
  {
    label: 'Retur',
    caption: 'Proses retur barang',
    route: 'cashier.sales.tab',
    params: { salesTab: 'returns' },
    icon: RotateCcw,
    tone: 'rose',
  },
]
const toneClass = {
  emerald: 'bg-emerald-50 text-emerald-700',
  blue: 'bg-blue-50 text-blue-700',
  violet: 'bg-violet-50 text-violet-700',
  rose: 'bg-rose-50 text-rose-700',
}
</script>
<template>
  <section
    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
  >
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-base font-bold text-slate-950 dark:text-white">Aksi Cepat</h2>
        <p class="mt-1 text-xs font-medium text-slate-500">Akses cepat ke pekerjaan kasir.</p>
      </div>
      <Plus :size="18" class="text-slate-400" />
    </div>
    <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
      <Link
        v-for="action in actions"
        :key="action.label"
        :href="route(action.route, action.params)"
        :class="[
          'flex items-center gap-3 rounded-xl border border-transparent p-3.5 transition hover:-translate-y-0.5 hover:border-current/10 hover:shadow-sm',
          toneClass[action.tone],
        ]"
        ><component :is="action.icon" :size="20" /><span
          ><strong class="block text-sm font-bold">{{ action.label }}</strong
          ><small class="mt-1 block text-[11px] font-medium opacity-70">{{
            action.caption
          }}</small></span
        ></Link
      >
    </div>
  </section>
</template>
