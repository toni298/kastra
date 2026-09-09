<script setup>
import { computed, nextTick, ref } from 'vue'
import { Eye, Filter } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import StockReportFilters from './StockReportFilters.vue'
import { formatCurrency, formatQty } from '@/Utils/helpers'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  loading: { type: Boolean, default: false },
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({}) },
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
const statusVariant = (row) => (row.is_low_stock ? 'warning' : 'success')
const toggleFilter = async () => {
  filterOpen.value = !filterOpen.value
  if (!filterOpen.value) return
  emit('load-options')
  await nextTick()
  const rect = filterButton.value?.getBoundingClientRect()
  if (!rect) return
  const width = Math.min(600, window.innerWidth - 24)
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
      empty-icon="package"
      empty-title="Tidak ada data stok"
      empty-message="Belum ada data stok pada filter yang dipilih."
      searchable
      :search="filters.search ?? ''"
      search-placeholder="Cari SKU, nama produk, atau barcode..."
      @navigate="emit('navigate', $event)"
      @filter="emit('filter', $event.search)"
    >
      <template #filters
        ><div ref="filterButton">
          <Button variant="secondary" size="sm" @click="toggleFilter"
            ><Filter :size="16" class="mr-2" />Filter</Button
          >
        </div>
        <StockReportFilters
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
      /></template>
      <template #thead
        ><tr>
          <th>SKU</th>
          <th>Nama Produk</th>
          <th>Kategori</th>
          <th class="text-right">Stok Awal</th>
          <th class="text-right">Total Masuk</th>
          <th class="text-right">Total Keluar</th>
          <th class="text-right">Adjustment</th>
          <th class="text-right">Stok Akhir</th>
          <th class="text-right">Nilai Valuasi</th>
          <th class="text-right">Aksi</th>
        </tr></template
      >
      <tr v-for="row in rows" :key="row.id" class="text-sm">
        <td class="px-5 py-4 font-mono dark:text-white">{{ row.sku }}</td>
        <td class="px-5 py-4 font-medium dark:text-white">
          <p>{{ row.name }}</p>
          <p class="mt-1 text-xs font-normal text-slate-400">• {{ row.unit ?? '-' }}</p>
        </td>
        <td class="px-5 py-4 dark:text-slate-200">{{ row.category }}</td>
        <td class="px-5 py-4 text-right tabular-nums dark:text-slate-200">
          {{ formatQty(row.stock_initial) }}
        </td>
        <td class="px-5 py-4 text-right tabular-nums text-emerald-600">
          {{ formatQty(row.total_in) }}
        </td>
        <td class="px-5 py-4 text-right tabular-nums text-red-600">
          {{ formatQty(row.total_out) }}
        </td>
        <td class="px-5 py-4 text-right tabular-nums dark:text-slate-200">
          {{ formatQty(row.adjustment) }}
        </td>
        <td class="px-5 py-4 text-right font-semibold tabular-nums dark:text-white">
          <span class="inline-flex items-center gap-2"
            >{{ formatQty(row.stock_final)
            }}<Badge :variant="statusVariant(row)">{{
              row.is_low_stock ? 'Menipis' : 'Aman'
            }}</Badge></span
          >
        </td>
        <td class="px-5 py-4 text-right font-semibold tabular-nums dark:text-white">
          {{ formatCurrency(row.valuation) }}
        </td>
        <td class="px-5 py-4 text-right">
          <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 dark:text-emerald-300"
            @click="emit('detail', row)"
          >
            <Eye :size="15" />Riwayat
          </button>
        </td>
      </tr>
    </DataTable>
  </DataPanel>
</template>
