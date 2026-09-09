<script setup>
import { computed, ref, toRef } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { ChartNoAxesColumn, CircleDollarSign, Package, Receipt } from 'lucide-vue-next'
import { formatCurrency, formatQty } from '@/Utils/helpers'
import { exportToExcel, exportToPdf } from '@/Utils/reportExport'
import ReportsLayout from '@/Layouts/ReportsLayout.vue'
import { useToastify } from '@/Composables/useToastify'
import SalesReportTable from './Components/SalesReportTable.vue'
import SalesReportDetailDrawer from './Components/SalesReportDetailDrawer.vue'
import { useSalesReport } from '../Composables/useSalesReport'

const props = defineProps({
  activeTab: { type: String, default: 'sales' },
  cashierLayout: { type: Boolean, default: false },
  reportSalesItems: { type: Object, default: () => ({ data: [] }) },
  reportSalesSummary: { type: Object, default: () => ({}) },
  reportSalesFilters: { type: Object, default: () => ({}) },
  reportSalesOptions: { type: Object, default: () => ({ branches: [] }) },
})
const selected = ref(null)
const exporting = ref(false)
const toast = useToastify()
const detailLoading = ref(false)
const detailError = ref(null)
let detailRequestId = 0
const { items, tableLoading, request, navigate } = useSalesReport(toRef(props, 'reportSalesItems'))
const reportRoute = computed(() =>
  props.cashierLayout ? route('cashier.reports') : route('reports.sales')
)
const comparisonText = (value) => {
  if (value === null || value === undefined) return 'Belum ada data pembanding'
  return `${value >= 0 ? '+' : ''}${value.toLocaleString('id-ID')}% dari periode sebelumnya`
}
const cards = computed(() => [
  {
    label: 'Total Penjualan',
    value: formatCurrency(props.reportSalesSummary.revenue),
    comparison: props.reportSalesSummary.comparison?.revenue,
    icon: CircleDollarSign,
    iconClass: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300',
  },
  {
    label: 'Total Transaksi',
    value: Number(props.reportSalesSummary.transaction_count ?? 0).toLocaleString('id-ID'),
    comparison: props.reportSalesSummary.comparison?.transaction_count,
    icon: Receipt,
    iconClass: 'bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300',
  },
  {
    label: 'Rata-rata Nilai Transaksi',
    value: formatCurrency(props.reportSalesSummary.aov),
    comparison: props.reportSalesSummary.comparison?.aov,
    icon: ChartNoAxesColumn,
    iconClass: 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300',
  },
  {
    label: 'Total Item Terjual',
    value: formatQty(props.reportSalesSummary.total_qty),
    comparison: props.reportSalesSummary.comparison?.total_qty,
    icon: Package,
    iconClass: 'bg-violet-50 text-violet-600 dark:bg-violet-400/10 dark:text-violet-300',
  },
])
const comparisonClass = (value) => {
  if (value === null || value === undefined || value === 0) return 'text-slate-500'
  return value > 0 ? 'text-emerald-600 dark:text-emerald-300' : 'text-red-600 dark:text-red-300'
}
const applyFilters = (filters) => request(reportRoute.value, { ...filters, per_page: 10 })
const loadReportOptions = () => {
  if (props.reportSalesOptions?.branches?.length) return

  router.reload({
    only: ['reportSalesOptions'],
  })
}
const paymentLabel = (value) =>
  ({ cash: 'Tunai', transfer: 'Transfer', qris: 'QRIS', ewallet: 'E-Wallet' })[value] ??
  value ??
  '-'
const statusLabel = (value) =>
  ({
    completed: 'Selesai',
    draft: 'Draft',
    partial_return: 'Retur Sebagian',
    full_return: 'Retur Penuh',
    cancelled: 'Dibatalkan',
  })[value] ??
  value ??
  '-'
const shortDate = (value) => {
  if (!value || value === '-') return '-'
  const [year, month, day] = String(value).split('-')
  return year && month && day ? `${day}/${month}/${year.slice(-2)}` : value
}
const truncateForPdf = (value, maxLength) => {
  const text = String(value ?? '-')
  return text.length > maxLength ? `${text.slice(0, maxLength)}...` : text
}
const exportSales = async (format) => {
  exporting.value = true

  try {
    const response = await axios.get(route('reports.sales.export'), {
      params: { ...props.reportSalesFilters },
    })
    const report = response.data
    const exportReport = {
      ...report,
      rows: report.rows.map((row) => [
        shortDate(row[0]),
        truncateForPdf(row[1], 10),
        truncateForPdf(row[2], 15),
        formatQty(row[3]),
        Number(row[4] ?? 0),
        paymentLabel(row[5]),
        statusLabel(row[6]),
      ]),
      summary: report.summary.map(([label, value]) => [label, Number(value ?? 0)]),
      pdfTable: {
        columnStyles: {
          0: { cellWidth: 20 },
          1: { cellWidth: 25 },
          2: { cellWidth: 30 },
          3: { cellWidth: 16, halign: 'right' },
          4: { cellWidth: 32, halign: 'right' },
          5: { cellWidth: 31 },
          6: { cellWidth: 28 },
        },
      },
    }

    if (format === 'pdf') exportToPdf(exportReport)
    else exportToExcel(exportReport)
  } catch (error) {
    toast.error(error?.response?.data?.message ?? 'Export laporan gagal. Silakan coba lagi.')
  } finally {
    exporting.value = false
  }
}
const openDetail = async (row) => {
  const requestId = ++detailRequestId
  selected.value = row
  detailLoading.value = true
  detailError.value = null

  try {
    const response = await axios.get(route('reports.sales.detail', { transaction: row.id }), {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
    if (typeof response.data === 'string') {
      throw new Error('Respons detail transaksi bukan JSON.')
    }
    if (requestId === detailRequestId) selected.value = response.data?.data ?? response.data
  } catch (error) {
    if (requestId === detailRequestId) {
      detailError.value =
        error?.response?.data?.message ?? error.message ?? 'Detail transaksi gagal dimuat.'
    }
  } finally {
    if (requestId === detailRequestId) detailLoading.value = false
  }
}
const closeDetail = () => {
  detailRequestId += 1
  selected.value = null
  detailLoading.value = false
  detailError.value = null
}
</script>

<template>
  <ReportsLayout :active-tab="activeTab" :cashier-layout="cashierLayout">
    <div class="space-y-5">
      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article
          v-for="card in cards"
          :key="card.label"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
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
            <span
              :class="['grid h-10 w-10 shrink-0 place-items-center rounded-xl', card.iconClass]"
            >
              <component :is="card.icon" :size="20" />
            </span>
          </div>
          <p :class="['mt-2 text-xs font-medium', comparisonClass(card.comparison)]">
            {{ comparisonText(card.comparison) }}
          </p>
        </article>
      </section>
      <SalesReportTable
        :items="items"
        :filters="reportSalesFilters"
        :options="reportSalesOptions"
        :exporting="exporting"
        :loading="tableLoading"
        @navigate="navigate"
        @filter="(search) => applyFilters({ ...reportSalesFilters, search })"
        @apply="applyFilters"
        @load-options="loadReportOptions"
        @export="exportSales"
        @detail="openDetail"
      />
    </div>
    <SalesReportDetailDrawer
      v-if="selected"
      :transaction="selected"
      :loading="detailLoading"
      :error="detailError"
      @close="closeDetail"
    />
  </ReportsLayout>
</template>
