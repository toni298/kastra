<script setup>
import { computed, ref, toRef } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { ArrowDownLeft, ArrowUpRight, CircleDollarSign, WalletCards } from 'lucide-vue-next'
import { formatCurrency } from '@/Utils/helpers'
import { exportToExcel, exportToPdf } from '@/Utils/reportExport'
import ReportsLayout from '@/Layouts/ReportsLayout.vue'
import { useToastify } from '@/Composables/useToastify'
import CashBankTransactionDrawer from '@/Pages/CashBank/Components/CashBankTransactionDrawer.vue'
import FinanceReportTable from './Components/FinanceReportTable.vue'
import FinanceCategoryBreakdown from './Components/FinanceCategoryBreakdown.vue'
import { useFinanceReport } from '../Composables/useFinanceReport'

const props = defineProps({
  activeTab: { type: String, default: 'finance' }, cashierLayout: { type: Boolean, default: false },
  reportFinanceItems: { type: Object, default: () => ({ data: [] }) }, reportFinanceSummary: { type: Object, default: () => ({}) },
  reportFinanceFilters: { type: Object, default: () => ({}) }, reportFinanceOptions: { type: Object, default: () => ({ accounts: [] }) },
})
const exporting = ref(false); const selected = ref(null); const toast = useToastify()
const { items, tableLoading, request, navigate } = useFinanceReport(toRef(props, 'reportFinanceItems'))
const reportRoute = computed(() => props.cashierLayout ? route('cashier.reports.finance') : route('reports.finance'))
const netClass = computed(() => Number(props.reportFinanceSummary.net ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-red-600 dark:text-red-300')
const cards = computed(() => [
  { label: 'Total Pemasukan', value: formatCurrency(props.reportFinanceSummary.income), icon: ArrowDownLeft, iconClass: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300', valueClass: 'text-emerald-600 dark:text-emerald-300' },
  { label: 'Total Pengeluaran', value: formatCurrency(props.reportFinanceSummary.expense), icon: ArrowUpRight, iconClass: 'bg-red-50 text-red-600 dark:bg-red-400/10 dark:text-red-300', valueClass: 'text-red-600 dark:text-red-300' },
  { label: 'Arus Kas Bersih', value: formatCurrency(props.reportFinanceSummary.net), icon: CircleDollarSign, iconClass: 'bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300', valueClass: netClass.value },
  { label: 'Total Saldo Kas & Bank', value: formatCurrency(props.reportFinanceSummary.current_balance), icon: WalletCards, iconClass: 'bg-violet-50 text-violet-600 dark:bg-violet-400/10 dark:text-violet-300', valueClass: 'text-slate-950 dark:text-white' },
])
const applyFilters = (filters) => request(reportRoute.value, { ...filters, per_page: 10 })
const loadReportOptions = () => { if (props.reportFinanceOptions?.accounts?.length) return; router.reload({ only: ['reportFinanceOptions'] }) }
const shortDate = (value) => { if (!value || value === '-') return '-'; const [year, month, day] = String(value).split('-'); return year && month && day ? `${day}/${month}/${year.slice(-2)}` : value }
const truncate = (value, max) => { const text = String(value ?? '-'); return text.length > max ? `${text.slice(0, max)}...` : text }
const exportFinance = async (format) => {
  exporting.value = true
  try {
    const report = (await axios.get(route('reports.finance.export'), { params: { ...props.reportFinanceFilters } })).data
    const rows = report.rows.map((row) => [
      shortDate(row[0]),
      truncate(row[1], 18),
      truncate(row[2], 20),
      truncate(row[3], 18),
      row[4],
      Number(row[5] ?? 0),
      truncate(row[6], 28),
    ])
    const summary = report.summary.map(([label, value]) => [label, Number(value ?? 0)])
    const excelReport = { ...report, rows, summary }
    const pdfReport = {
      ...report,
      columns: ['Tanggal', 'No. Referensi', 'Kategori & Akun', 'Tipe', 'Nominal', 'Keterangan'],
      rows: rows.map((row) => [
        row[0],
        row[1],
        truncate(`${row[2]} - ${row[3]}`, 34),
        row[4],
        row[5],
        truncate(row[6], 42),
      ]),
      summary,
      pdfTable: {
        columnStyles: {
          0: { cellWidth: 20 },
          1: { cellWidth: 27 },
          2: { cellWidth: 38 },
          3: { cellWidth: 22 },
          4: { cellWidth: 29, halign: 'right' },
          5: { cellWidth: 46 },
        },
      },
    }
    if (format === 'pdf') exportToPdf(pdfReport)
    else exportToExcel(excelReport)
  } catch (error) { toast.error(error?.response?.data?.message ?? 'Export laporan keuangan gagal. Silakan coba lagi.') } finally { exporting.value = false }
}
const openDetail = async (row) => {
  try {
    const response = await axios.get(route('reports.finance.detail', { transaction: row.id }))
    selected.value = response.data?.data ?? response.data
  } catch {
    selected.value = row
  }
}
const closeDetail = () => { selected.value = null }
</script>

<template>
  <ReportsLayout :active-tab="activeTab" :cashier-layout="cashierLayout"><div class="space-y-5"><section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><article v-for="card in cards" :key="card.label" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p><p :class="['mt-2 truncate text-lg font-semibold tracking-tight sm:text-xl', card.valueClass]">{{ card.value }}</p></div><span :class="['grid h-10 w-10 shrink-0 place-items-center rounded-xl', card.iconClass]"><component :is="card.icon" :size="20" /></span></div><p class="mt-2 text-xs text-slate-500">{{ card.label === 'Arus Kas Bersih' ? 'Pemasukan dikurangi pengeluaran' : 'Berdasarkan periode terpilih' }}</p></article></section><section class="grid gap-5 lg:grid-cols-2"><FinanceCategoryBreakdown title="Top Kategori Pemasukan" :items="reportFinanceSummary.income_categories" :icon="ArrowDownLeft" /><FinanceCategoryBreakdown title="Top Kategori Pengeluaran" :items="reportFinanceSummary.expense_categories" tone="red" :icon="ArrowUpRight" /></section><FinanceReportTable :items="items" :filters="reportFinanceFilters" :options="reportFinanceOptions" :exporting="exporting" :loading="tableLoading" @navigate="navigate" @filter="(search) => applyFilters({ ...reportFinanceFilters, search })" @apply="applyFilters" @load-options="loadReportOptions" @export="exportFinance" @detail="openDetail" /></div><CashBankTransactionDrawer v-if="selected" :transaction="selected" @close="closeDetail" /></ReportsLayout>
</template>
