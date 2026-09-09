<script setup>
import { Head, Link } from '@inertiajs/vue3'
import CashierLayout from '@/Components/Cashier/CashierLayout.vue'
import CashierDashboardSummary from './Dashboard/CashierDashboardSummary.vue'
import CashierSalesChart from './Dashboard/CashierSalesChart.vue'
import CashierRecentTransactions from './Dashboard/CashierRecentTransactions.vue'
import CashierDashboardActions from './Dashboard/CashierDashboardActions.vue'
import CashierDashboardLists from './Dashboard/CashierDashboardLists.vue'

const props = defineProps({
  today: { type: String, default: '' },
  summary: { type: Object, default: () => ({}) },
  salesChart: { type: Array, default: () => [] },
  chartPeriod: { type: String, default: 'today' },
  recentTransactions: { type: Array, default: () => [] },
  topProducts: { type: Array, default: () => [] },
  paymentMethods: { type: Array, default: () => [] },
  lowStock: { type: Array, default: () => [] },
})
const transactionTabUrl = (tab) =>
  `${route('cashier.sales.tab', { salesTab: 'transactions' })}?branch_id=&date_from=&date_to=&payment_status=&per_page=10&status=&tab=${tab}`
</script>

<template>
  <Head title="Dashboard Kasir" />
  <CashierLayout full-width>
    <div class="space-y-6">
      <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
          <p class="text-sm font-semibold text-blue-600">Operasional hari ini</p>
          <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 dark:text-white">
            Selamat datang, {{ $page.props.auth?.user?.name ?? 'Kasir' }}
          </h1>
          <p class="mt-1 text-sm text-slate-500">Ringkasan aktivitas toko untuk {{ today }}.</p>
        </div>
        <Link
          :href="route('cashier')"
          class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700"
          >+ Transaksi Baru</Link
        >
      </div>
      <CashierDashboardSummary :summary="summary" />
      <div class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(360px,.9fr)]">
        <CashierSalesChart :data="salesChart" :period="chartPeriod" />
        <CashierRecentTransactions :items="recentTransactions" />
      </div>
      <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)]">
        <CashierDashboardActions />
        <CashierDashboardLists title="Produk Terlaris Hari Ini" :items="topProducts" type="top" />
        <CashierDashboardLists title="Stok Menipis" :items="lowStock" type="stock" />
      </div>
      <div class="grid gap-5 lg:grid-cols-2">
        <CashierDashboardLists
          title="Pembayaran Perlu Perhatian"
          :items="[
            {
              label: 'Belum Lunas',
              value: summary.unpaid_count,
              amount: summary.unpaid,
              tone: 'amber',
              href: transactionTabUrl('unpaid'),
            },
            {
              label: 'Jatuh Tempo Hari Ini',
              value: summary.unpaid_count,
              amount: summary.unpaid,
              tone: 'rose',
              href: transactionTabUrl('overdue'),
            },
          ]"
          type="attention"
        />
        <CashierDashboardLists
          title="Ringkasan Metode Pembayaran"
          :items="paymentMethods"
          type="payment"
        />
      </div>
    </div>
  </CashierLayout>
</template>
