<script setup>
import { CircleDollarSign, Clock3, PackageCheck, ShoppingCart } from 'lucide-vue-next'

const props = defineProps({
  summary: {
    type: Object,
    default: () => ({
      active_orders: 0,
      waiting_goods: 0,
      unpaid_count: 0,
      unpaid_total: 0,
      month_total: 0,
      month_count: 0,
    }),
  },
})

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value || 0))

const cards = [
  {
    label: 'Purchase Order Aktif',
    value: String(props.summary.active_orders ?? 0),
    caption: 'Masih dalam proses',
    icon: ShoppingCart,
    tone: 'bg-blue-50 text-blue-700 dark:bg-blue-400/10 dark:text-blue-300',
  },
  {
    label: 'Menunggu Barang',
    value: String(props.summary.waiting_goods ?? 0),
    caption: 'Perlu dipantau',
    icon: PackageCheck,
    tone: 'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300',
  },
  {
    label: 'Belum Dibayar',
    value: String(props.summary.unpaid_count ?? 0),
    caption: formatCurrency(props.summary.unpaid_total),
    icon: Clock3,
    tone: 'bg-red-50 text-red-700 dark:bg-red-400/10 dark:text-red-300',
  },
  {
    label: 'Total Pembelian Bulan Ini',
    value: formatCurrency(props.summary.month_total),
    caption: `${props.summary.month_count ?? 0} transaksi`,
    icon: CircleDollarSign,
    tone: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300',
  },
]
</script>

<template>
  <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Ringkasan transaksi pembelian">
    <article
      v-for="card in cards"
      :key="card.label"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-[#29476b] dark:bg-[#102542] sm:p-5"
    >
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
          <p
            class="mt-2 truncate text-lg font-semibold tracking-tight text-slate-950 dark:text-white sm:text-xl"
          >
            {{ card.value }}
          </p>
        </div>
        <span :class="['grid size-9 shrink-0 place-items-center rounded-xl', card.tone]">
          <component :is="card.icon" :size="18" />
        </span>
      </div>
      <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ card.caption }}</p>
    </article>
  </section>
</template>
