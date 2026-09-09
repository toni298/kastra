<script setup>
import { computed, ref, toRef } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { CircleDollarSign, ClipboardList, Package } from 'lucide-vue-next'
import { formatCurrency, formatQty } from '@/Utils/helpers'
import { exportToExcel, exportToPdf } from '@/Utils/reportExport'
import ReportsLayout from '@/Layouts/ReportsLayout.vue'
import { useToastify } from '@/Composables/useToastify'
import PurchaseReportTable from './Components/PurchaseReportTable.vue'
import PurchaseReportDetailDrawer from './Components/PurchaseReportDetailDrawer.vue'
import { usePurchaseReport } from '../Composables/usePurchaseReport'

const props = defineProps({
  activeTab: { type: String, default: 'purchases' },
  cashierLayout: { type: Boolean, default: false },
  reportPurchaseItems: { type: Object, default: () => ({ data: [] }) },
  reportPurchaseSummary: { type: Object, default: () => ({}) },
  reportPurchaseFilters: { type: Object, default: () => ({}) },
  reportPurchaseOptions: { type: Object, default: () => ({ suppliers: [], warehouses: [] }) },
})
const selected = ref(null)
const exporting = ref(false)
const toast = useToastify()
const detailLoading = ref(false)
const detailError = ref(null)
let detailRequestId = 0
const { items, tableLoading, request, navigate } = usePurchaseReport(toRef(props, 'reportPurchaseItems'))
const reportRoute = computed(() =>
  props.cashierLayout ? route('cashier.reports.purchases') : route('reports.purchases')
)
const cards = computed(() => [
  {
    label: 'Total Pembelian',
    value: formatCurrency(props.reportPurchaseSummary.total),
    icon: CircleDollarSign,
    iconClass: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300',
  },
  {
    label: 'Total Transaksi Pembelian',
    value: Number(props.reportPurchaseSummary.transaction_count ?? 0).toLocaleString('id-ID'),
    icon: ClipboardList,
    iconClass: 'bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300',
  },
  {
    label: 'Total Item Dibeli',
    value: formatQty(props.reportPurchaseSummary.total_qty),
    icon: Package,
    iconClass: 'bg-violet-50 text-violet-600 dark:bg-violet-400/10 dark:text-violet-300',
  },
])
const applyFilters = (filters) => request(reportRoute.value, { ...filters, per_page: 10 })
const loadReportOptions = () => {
  if (
    props.reportPurchaseOptions?.suppliers?.length &&
    props.reportPurchaseOptions?.warehouses?.length
  )
    return
  router.reload({ only: ['reportPurchaseOptions'] })
}
const shortDate = (value) => {
  if (!value || value === '-') return '-'
  const [year, month, day] = String(value).split('-')
  return year && month && day ? `${day}/${month}/${year.slice(-2)}` : value
}
const truncateForPdf = (value, maxLength) => {
  const text = String(value ?? '-')
  return text.length > maxLength ? `${text.slice(0, maxLength)}...` : text
}
const paymentStatusLabel = (value) =>
  ({
    paid: 'Lunas',
    partial: 'Utang / Pending',
    unpaid: 'Utang / Pending',
    cancelled: 'Dibatalkan',
  })[value] ??
  value ??
  '-'
const exportPurchases = async (format) => {
  exporting.value = true
  try {
    const response = await axios.get(route('reports.purchases.export'), {
      params: { ...props.reportPurchaseFilters },
    })
    const report = response.data
    const exportReport = {
      ...report,
      rows: report.rows.map((row) => [
        shortDate(row[0]),
        truncateForPdf(row[1], 18),
        truncateForPdf(row[2], 18),
        formatQty(row[3]),
        Number(row[4] ?? 0),
        paymentStatusLabel(row[5]),
      ]),
      summary: report.summary.map(([label, value]) => [label, Number(value ?? 0)]),
      pdfTable: {
        columnStyles: {
          0: { cellWidth: 24 },
          1: { cellWidth: 42 },
          2: { cellWidth: 36 },
          3: { cellWidth: 20, halign: 'right' },
          4: { cellWidth: 40, halign: 'right' },
          5: { cellWidth: 20 },
        },
      },
    }
    if (format === 'pdf') exportToPdf(exportReport)
    else exportToExcel(exportReport)
  } catch (error) {
    toast.error(
      error?.response?.data?.message ?? 'Export laporan pembelian gagal. Silakan coba lagi.'
    )
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
    const response = await axios.get(route('reports.purchases.detail', { transaction: row.id }), {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    if (typeof response.data === 'string') throw new Error('Respons detail transaksi bukan JSON.')
    if (requestId === detailRequestId) selected.value = response.data?.data ?? response.data
  } catch (error) {
    if (requestId === detailRequestId)
      detailError.value =
        error?.response?.data?.message ?? error.message ?? 'Detail pembelian gagal dimuat.'
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
      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
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
            <span :class="['grid h-10 w-10 shrink-0 place-items-center rounded-xl', card.iconClass]"
              ><component :is="card.icon" :size="20"
            /></span>
          </div>
        </article>
      </section>
      <PurchaseReportTable
        :items="items"
        :filters="reportPurchaseFilters"
        :options="reportPurchaseOptions"
        :exporting="exporting"
        :loading="tableLoading"
        @navigate="navigate"
        @filter="(search) => applyFilters({ ...reportPurchaseFilters, search })"
        @apply="applyFilters"
        @load-options="loadReportOptions"
        @export="exportPurchases"
        @detail="openDetail"
      />
    </div>
    <PurchaseReportDetailDrawer
      v-if="selected"
      :transaction="selected"
      :loading="detailLoading"
      :error="detailError"
      @close="closeDetail"
    />
  </ReportsLayout>
</template>
