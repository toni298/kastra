<script setup>
import * as icons from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps({ summary: { type: Object, default: () => ({}) } })

const tones = {
  emerald: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300',
  blue: 'bg-blue-50 text-blue-700 dark:bg-blue-400/10 dark:text-blue-300',
  red: 'bg-red-50 text-red-700 dark:bg-red-400/10 dark:text-red-300',
  violet: 'bg-violet-50 text-violet-700 dark:bg-violet-400/10 dark:text-violet-300',
}
const currency = (value) => `Rp ${Number(value ?? 0).toLocaleString('id-ID')}`
const summaryItems = computed(() => [
  { label: 'Saldo Saat Ini', value: currency(props.summary.current_balance), caption: `${props.summary.active_accounts_count ?? 0} rekening aktif`, icon: 'WalletCards', color: 'emerald' },
  { label: 'Kas Masuk Hari Ini', value: currency(props.summary.today_income), caption: `${props.summary.today_income_count ?? 0} transaksi`, icon: 'ArrowDownToLine', color: 'blue' },
  { label: 'Kas Keluar Hari Ini', value: currency(props.summary.today_expense), caption: `${props.summary.today_expense_count ?? 0} transaksi`, icon: 'ArrowUpFromLine', color: 'red' },
  { label: 'Jumlah Transaksi Hari Ini', value: String(props.summary.today_transaction_count ?? 0), caption: `${props.summary.today_income_count ?? 0} masuk · ${props.summary.today_expense_count ?? 0} keluar`, icon: 'ReceiptText', color: 'violet' },
])
</script>

<template>
  <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Ringkasan kas dan bank">
    <article
      v-for="item in summaryItems"
      :key="item.label"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-[#29476b] dark:bg-[#102542] sm:p-5"
    >
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ item.label }}</p>
          <p
            class="mt-2 truncate text-lg font-semibold tracking-tight text-slate-950 dark:text-white sm:text-xl"
          >
            {{ item.value }}
          </p>
        </div>
        <span :class="['grid size-9 shrink-0 place-items-center rounded-xl', tones[item.color]]">
          <component :is="icons[item.icon]" :size="18" />
        </span>
      </div>
      <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ item.caption }}</p>
    </article>
  </section>
</template>
