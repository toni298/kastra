<script setup>
import { WalletCards, RotateCcw, Receipt, ShoppingBag } from 'lucide-vue-next'
defineProps({ summary: { type: Object, default: () => ({}) } })
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
const cards = [
  {
    key: 'sales',
    label: 'Penjualan Hari Ini',
    icon: ShoppingBag,
    tone: 'emerald',
    format: 'money',
  },
  {
    key: 'transactions',
    label: 'Transaksi Hari Ini',
    icon: Receipt,
    tone: 'blue',
    format: 'count',
  },
  { key: 'unpaid', label: 'Belum Lunas', icon: WalletCards, tone: 'amber', format: 'money' },
  { key: 'returns', label: 'Retur Hari Ini', icon: RotateCcw, tone: 'violet', format: 'count' },
]
const toneClass = {
  emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300',
  blue: 'bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300',
  amber: 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300',
  violet: 'bg-violet-50 text-violet-600 dark:bg-violet-400/10 dark:text-violet-300',
}
</script>
<template>
  <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article
      v-for="card in cards"
      :key="card.key"
      class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
    >
      <div class="flex items-start justify-between">
        <span :class="['grid size-10 place-items-center rounded-xl', toneClass[card.tone]]"
          ><component :is="card.icon" :size="19" /></span
        ><span class="text-xs font-semibold text-slate-400">Hari ini</span>
      </div>
      <p class="mt-5 text-sm font-medium text-slate-500">{{ card.label }}</p>
      <p class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">
        {{
          card.format === 'money' ? money(summary[card.key]) : `${summary[card.key] ?? 0} transaksi`
        }}
      </p>
      <p v-if="card.key === 'unpaid'" class="mt-2 text-xs font-semibold text-amber-600">
        {{ summary.unpaid_count ?? 0 }} transaksi perlu ditagih
      </p>
      <p v-else-if="card.key === 'returns'" class="mt-2 text-xs font-semibold text-violet-600">
        {{ money(summary.returns_total) }} nilai retur
      </p>
      <p v-else class="mt-2 text-xs font-semibold text-emerald-600">Aktivitas tercatat</p>
    </article>
  </section>
</template>
