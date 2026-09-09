<script setup>
import { computed, ref, toRef } from 'vue'
import axios from 'axios'
import { AlertTriangle, Boxes, CircleDollarSign, Package } from 'lucide-vue-next'
import { formatCurrency, formatDate, formatQty } from '@/Utils/helpers'
import { exportToExcel, exportToPdf } from '@/Utils/reportExport'
import ReportsLayout from '@/Layouts/ReportsLayout.vue'
import { useToastify } from '@/Composables/useToastify'
import StockReportTable from './Components/StockReportTable.vue'
import StockReportDetailDrawer from './Components/StockReportDetailDrawer.vue'
import { useStockReport } from '../Composables/useStockReport'

const props = defineProps({
  activeTab: { type: String, default: 'stock' },
  cashierLayout: { type: Boolean, default: false },
  reportStockItems: { type: Object, default: () => ({ data: [] }) },
  reportStockSummary: { type: Object, default: () => ({}) },
  reportStockFilters: { type: Object, default: () => ({}) },
  reportStockOptions: {
    type: Object,
    default: () => ({ categories: [], brands: [], branches: [], warehouses: [] }),
  },
})
const exporting = ref(false)
const toast = useToastify()
const selected = ref(null)
const ledger = ref([])
const stockOptions = ref(props.reportStockOptions)
const optionsLoading = ref(false)
const detailLoading = ref(false)
const detailError = ref(null)
const { items, tableLoading, request, navigate } = useStockReport(toRef(props, 'reportStockItems'))
const reportRoute = computed(() =>
  props.cashierLayout ? route('cashier.reports.stock') : route('reports.stock')
)
const cards = computed(() => [
  {
    label: 'Total Jenis Produk',
    value: Number(props.reportStockSummary.product_count ?? 0).toLocaleString('id-ID'),
    icon: Boxes,
    iconClass: 'bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300',
  },
  {
    label: 'Total Kuantitas Stok',
    value: formatQty(props.reportStockSummary.total_qty),
    icon: Package,
    iconClass: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300',
  },
  {
    label: 'Total Valuasi Stok',
    value: formatCurrency(props.reportStockSummary.valuation),
    icon: CircleDollarSign,
    iconClass: 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300',
  },
  {
    label: 'Produk Stok Menipis',
    value: Number(props.reportStockSummary.low_stock_count ?? 0).toLocaleString('id-ID'),
    icon: AlertTriangle,
    iconClass: 'bg-orange-50 text-orange-600 dark:bg-orange-400/10 dark:text-orange-300',
  },
])
const applyFilters = (filters) => request(reportRoute.value, { ...filters, per_page: 10 })
const loadReportOptions = () => {
  if (optionsLoading.value || stockOptions.value?.categories?.length) return

  optionsLoading.value = true
  axios
    .get(route('reports.stock.options'))
    .then((response) => {
      stockOptions.value = response.data
    })
    .finally(() => {
      optionsLoading.value = false
    })
}
const truncateForPdf = (value, maxLength) => {
  const text = String(value ?? '-')
  return text.length > maxLength ? `${text.slice(0, maxLength)}...` : text
}
const formatAdjustment = (value) => {
  const quantity = Number(value ?? 0)
  return `${quantity >= 0 ? '+' : ''}${formatQty(quantity)}`
}
const exportStock = async (format) => {
  exporting.value = true
  try {
    const report = (
      await axios.get(route('reports.stock.export'), { params: { ...props.reportStockFilters } })
    ).data
    const excelReport = {
      ...report,
      rows: report.rows.map((row) => [
        row[0],
        truncateForPdf(row[1], 16),
        truncateForPdf(row[2], 24),
        truncateForPdf(row[3], 16),
        formatQty(row[4]),
        formatQty(row[5]),
        formatQty(row[6]),
        formatQty(row[7]),
        formatQty(row[8]),
        Number(row[9] ?? 0),
      ]),
      summary: report.summary.map(([label, value]) => [label, Number(value ?? 0)]),
    }
    const pdfReport = {
      ...report,
      filename: `Laporan_Stok_${props.reportStockFilters.date_from ?? report.period.from}.pdf`,
      columns: ['Tgl Mutasi', 'Produk & Identitas', 'Stok Awal', 'Rincian Mutasi', 'Stok Akhir', 'Nilai Valuasi'],
      rows: report.rows.map((row) => [
        formatDate(row[0]),
        `${row[2] ?? '-'}\n${row[1] ?? '-'} • ${row[3] ?? '-'}`,
        formatQty(row[4]),
        `Masuk: ${formatQty(row[5])}\nKeluar: ${formatQty(row[6])}\nPenyesuaian: ${formatAdjustment(row[7])}`,
        formatQty(row[8]),
        `Rp ${Number(row[9] ?? 0).toLocaleString('id-ID')}`,
      ]),
      pdfOptions: {
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4',
      },
      pdfTable: {
        styles: {
          fontSize: 8,
          cellPadding: 2,
          overflow: 'linebreak',
          valign: 'middle',
        },
        headStyles: {
          fillColor: [16, 185, 129],
          textColor: [255, 255, 255],
          fontStyle: 'bold',
        },
        columnStyles: {
          0: { cellWidth: 22, halign: 'center' },
          1: { cellWidth: 50, halign: 'left' },
          2: { cellWidth: 20, halign: 'right' },
          3: { cellWidth: 35, halign: 'left' },
          4: { cellWidth: 20, halign: 'right' },
          5: { cellWidth: 35, halign: 'right' },
        },
      },
    }
    if (format === 'pdf') exportToPdf(pdfReport)
    else exportToExcel(excelReport)
  } catch (error) {
    toast.error(error?.response?.data?.message ?? 'Export laporan stok gagal. Silakan coba lagi.')
  } finally {
    exporting.value = false
  }
}
const openDetail = async (row) => {
  selected.value = row
  ledger.value = []
  detailLoading.value = true
  detailError.value = null
  try {
    const response = await axios.get(route('reports.stock.detail', { product: row.product_id }), {
      params: {
        branch_id: props.reportStockFilters.branch_id,
        gudang_id: props.reportStockFilters.gudang_id,
        date_from: props.reportStockFilters.date_from,
        date_to: props.reportStockFilters.date_to,
      },
    })
    selected.value = response.data.product
    ledger.value = response.data.ledger?.data ?? response.data.ledger ?? []
  } catch (error) {
    detailError.value =
      error?.response?.data?.message ?? error.message ?? 'Riwayat ledger gagal dimuat.'
  } finally {
    detailLoading.value = false
  }
}
const closeDetail = () => {
  selected.value = null
  ledger.value = []
  detailError.value = null
}
</script>

<template>
  <ReportsLayout :active-tab="activeTab" :cashier-layout="cashierLayout"
    ><div class="space-y-5">
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
            <span :class="['grid h-10 w-10 shrink-0 place-items-center rounded-xl', card.iconClass]"
              ><component :is="card.icon" :size="20"
            /></span>
          </div>
        </article>
      </section>
      <StockReportTable
        :items="items"
        :filters="reportStockFilters"
        :options="stockOptions"
        :exporting="exporting"
        :loading="tableLoading"
        @navigate="navigate"
        @filter="(search) => applyFilters({ ...reportStockFilters, search })"
        @apply="applyFilters"
        @load-options="loadReportOptions"
        @export="exportStock"
        @detail="openDetail"
      />
    </div>
    <StockReportDetailDrawer
      v-if="selected"
      :product="selected"
      :ledger="ledger"
      :loading="detailLoading"
      :error="detailError"
      @close="closeDetail"
  /></ReportsLayout>
</template>
