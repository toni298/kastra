<script setup>
import { computed, nextTick, ref } from 'vue'
import { Eye, Filter } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import PurchaseReportFilters from './PurchaseReportFilters.vue'
import { formatCurrency, formatQty } from '@/Utils/helpers'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  loading: { type: Boolean, default: false },
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ suppliers: [], warehouses: [] }) },
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
const paymentLabel = (row) => {
  if (row.status === 'cancelled') return 'Dibatalkan'
  return (
    { paid: 'Lunas', partial: 'Utang / Pending', unpaid: 'Utang / Pending' }[row.payment] ??
    row.payment ??
    '-'
  )
}
const paymentVariant = (row) =>
  row.status === 'cancelled' ? 'error' : row.payment === 'paid' ? 'success' : 'warning'
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
      empty-icon="shopping-cart"
      empty-title="Tidak ada pembelian"
      empty-message="Belum ada transaksi pembelian pada filter yang dipilih."
      searchable
      :search="filters.search ?? ''"
      search-placeholder="Cari nomor faktur, supplier, SKU, atau produk..."
      @navigate="emit('navigate', $event)"
      @filter="emit('filter', $event.search)"
    >
      <template #filters>
        <div ref="filterButton">
          <Button variant="secondary" size="sm" @click="toggleFilter"
            ><Filter :size="16" class="mr-2" />Filter</Button
          >
        </div>
        <PurchaseReportFilters
          v-if="filterOpen"
          :filters="filters"
          :options="options"
          :panel-style="filterPanelStyle"
          :trigger-element="filterButton"
          :exporting="exporting"
          @close="filterOpen = false"
          @apply="
            (value) => {
              emit('apply', value)
              filterOpen = false
            }
          "
          @export="emit('export', $event)"
        />
      </template>
      <template #thead>
        <tr>
          <th>Tanggal Transaksi</th>
          <th>No. Ref / Faktur Supplier</th>
          <th>Nama Supplier</th>
          <th class="text-right">Total Qty</th>
          <th class="text-right">Total Nominal</th>
          <th>Status Pembayaran</th>
          <th class="text-right">Aksi</th>
        </tr>
      </template>
      <tr v-for="row in rows" :key="row.id" class="text-sm">
        <td class="whitespace-nowrap px-5 py-4 dark:text-slate-200">{{ row.date ?? '-' }}</td>
        <td class="px-5 py-4 font-mono font-semibold dark:text-white">{{ row.number ?? '-' }}</td>
        <td class="px-5 py-4 dark:text-slate-200">{{ row.supplier ?? 'Pembelian Umum' }}</td>
        <td class="px-5 py-4 text-right tabular-nums dark:text-slate-200">
          {{ formatQty(row.total_qty) }}
        </td>
        <td class="px-5 py-4 text-right font-semibold tabular-nums dark:text-white">
          {{ formatCurrency(row.total) }}
        </td>
        <td class="px-5 py-4">
          <Badge :variant="paymentVariant(row)">{{ paymentLabel(row) }}</Badge>
        </td>
        <td class="px-5 py-4 text-right">
          <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 dark:text-emerald-300"
            @click="emit('detail', row)"
          >
            <Eye :size="15" />Detail
          </button>
        </td>
      </tr>
    </DataTable>
  </DataPanel>
</template>
