<script setup>
import { computed, nextTick, ref } from 'vue'
import { Eye, Filter } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import SalesReportFilters from './SalesReportFilters.vue'
import { formatCurrency, formatQty } from '@/Utils/helpers'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  loading: { type: Boolean, default: false },
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ branches: [] }) },
  exporting: { type: Boolean, default: false },
})
const emit = defineEmits(['detail', 'filter', 'apply', 'load-options', 'export', 'navigate'])
const filterOpen = ref(false)
const filterButton = ref(null)
const filterPanelStyle = ref({})
const rows = computed(() => props.items?.data ?? [])
const pagination = computed(() => ({
  ...props.items,
  per_page: props.items?.meta?.per_page ?? 10,
}))
const paymentLabel = (value) => ({
  cash: 'Tunai',
  transfer: 'Transfer',
  qris: 'QRIS',
  ewallet: 'E-Wallet',
}[value] ?? value ?? '-')
const statusLabel = (value) => ({
  completed: 'Selesai',
  draft: 'Draft',
  partial_return: 'Retur Sebagian',
  full_return: 'Retur Penuh',
  cancelled: 'Dibatalkan',
}[value] ?? value ?? '-')
const statusVariant = (value) =>
  value === 'completed'
    ? 'success'
    : ['cancelled', 'full_return'].includes(value)
      ? 'error'
      : 'warning'
const toggleFilter = async () => {
  filterOpen.value = !filterOpen.value
  if (!filterOpen.value) return

  emit('load-options')
  await nextTick()
  const rect = filterButton.value?.getBoundingClientRect()
  if (!rect) return
  const width = Math.min(560, window.innerWidth - 24)
  filterPanelStyle.value = {
    left: `${Math.max(12, Math.min(rect.left, window.innerWidth - width - 12))}px`,
    top: `${Math.min(rect.bottom + 8, window.innerHeight - 24)}px`,
  }
}
</script>

<template>
  <DataPanel>
    <DataTable
      :items="rows"
      :pagination="pagination"
      :loading="loading"
      sticky-toolbar
      empty-icon="bar-chart-3"
      empty-title="Tidak ada penjualan"
      empty-message="Belum ada transaksi pada filter yang dipilih."
      searchable
      :search="filters.search ?? ''"
      search-placeholder="Cari nomor faktur, SKU, produk, atau pelanggan..."
      @navigate="emit('navigate', $event)"
      @filter="emit('filter', $event.search)"
    >
      <template #filters>
        <div ref="filterButton">
          <Button variant="secondary" size="sm" @click="toggleFilter">
            <Filter :size="16" class="mr-2" />Filter
          </Button>
        </div>
        <SalesReportFilters
          v-if="filterOpen"
          :filters="filters"
          :options="options"
          :panel-style="filterPanelStyle"
          :trigger-element="filterButton"
          :exporting="exporting"
          @close="filterOpen = false"
          @apply="(value) => { emit('apply', value); filterOpen = false }"
          @export="emit('export', $event)"
        />
      </template>
      <template #thead>
        <tr>
          <th>Tanggal Transaksi</th>
          <th>Nomor Faktur</th>
          <th>Pelanggan</th>
          <th class="text-right">Total Qty</th>
          <th class="text-right">Total Nominal</th>
          <th>Metode Pembayaran</th>
          <th>Status</th>
          <th class="text-right">Aksi</th>
        </tr>
      </template>
      <tr v-for="row in rows" :key="row.id" class="text-sm">
        <td class="px-5 py-4 whitespace-nowrap dark:text-slate-200">{{ row.date ?? '-' }}</td>
        <td class="px-5 py-4 font-mono font-semibold dark:text-white">{{ row.number }}</td>
        <td class="px-5 py-4 dark:text-slate-200">{{ row.customer ?? 'Penjualan Umum' }}</td>
        <td class="px-5 py-4 text-right tabular-nums dark:text-slate-200">{{ formatQty(row.total_qty) }}</td>
        <td class="px-5 py-4 text-right font-semibold tabular-nums dark:text-white">{{ formatCurrency(row.total) }}</td>
        <td class="px-5 py-4 dark:text-slate-200">{{ paymentLabel(row.payment_method) }}</td>
        <td class="px-5 py-4"><Badge :variant="statusVariant(row.status)">{{ statusLabel(row.status) }}</Badge></td>
        <td class="px-5 py-4 text-right">
          <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-400/10"
            @click="emit('detail', row)"
          >
            <Eye :size="15" />Detail
          </button>
        </td>
      </tr>
    </DataTable>
  </DataPanel>
</template>