<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { formatQty } from '@/Utils/helpers'
import { ArrowDownToLine, ArrowUpFromLine, ClipboardList, Filter, SlidersHorizontal } from 'lucide-vue-next'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import Button from '@/Components/UI/Button.vue'
import InventoryMovementFilters from './InventoryMovementFilters.vue'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  pagination: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
  summary: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ branches: [], warehouses: [], users: [] }) },
})

const emit = defineEmits(['request', 'load-more'])
const rows = computed(() => props.items?.data ?? [])
const search = computed(() => props.filters.search ?? '')
const formatDate = (value) => (value ? new Date(value).toLocaleString('id-ID') : '-')
const filterOpen = ref(false)
const filterButton = ref(null)
const filterPanelStyle = ref({})
const request = (data = {}) =>
  emit('request', {
    tab: 'movements',
    url: route('inventory.movements'),
    data: { ...props.filters, ...data, per_page: data.per_page ?? Number(props.pagination?.meta?.per_page ?? 25) },
    replace: true,
  })
const filter = ({ search: value }) => request({ search: value || undefined })
const changePerPage = (perPage) => request({ per_page: perPage })
const loadMore = (url) => emit('load-more', url)
const applyFilters = (filters) => {
  filterOpen.value = false
  request({ ...filters })
}
const toggleFilter = async () => {
  filterOpen.value = !filterOpen.value
  if (!filterOpen.value) return

  await nextTick()
  const rect = filterButton.value?.getBoundingClientRect()
  if (!rect) return
  const width = Math.min(520, window.innerWidth - 24)
  filterPanelStyle.value = {
    left: `${Math.max(12, Math.min(rect.left, window.innerWidth - width - 12))}px`,
    top: `${Math.min(rect.bottom + 8, window.innerHeight - 24)}px`,
  }
}
const closeMenuOnEscape = (event) => {
  if (event.key === 'Escape') filterOpen.value = false
}
onMounted(() => document.addEventListener('keydown', closeMenuOnEscape))
onBeforeUnmount(() => document.removeEventListener('keydown', closeMenuOnEscape))
</script>

<template>
  <div class="space-y-5">
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan mutasi stok">
      <article v-for="card in [
        { label: 'Total Transaksi Mutasi (30 Hari)', value: Number(summary.total_events ?? 0).toLocaleString('id-ID'), icon: ClipboardList, tone: 'text-blue-600 bg-blue-50 dark:bg-blue-400/10' },
        { label: 'Total Barang Masuk (30 Hari)', value: `+${formatQty(summary.total_qty_in ?? 0)} Qty`, icon: ArrowDownToLine, tone: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-400/10' },
        { label: 'Total Barang Keluar (30 Hari)', value: `-${formatQty(summary.total_qty_out ?? 0)} Qty`, icon: ArrowUpFromLine, tone: 'text-rose-600 bg-rose-50 dark:bg-rose-400/10' },
        { label: 'Total Penyesuaian (30 Hari)', value: `${Number(summary.total_adjustment ?? 0) >= 0 ? '+' : ''}${formatQty(summary.total_adjustment ?? 0)} Qty`, icon: SlidersHorizontal, tone: 'text-amber-600 bg-amber-50 dark:bg-amber-400/10' },
      ]" :key="card.label" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-[#29476b] dark:bg-[#102542]">
        <div class="flex items-start justify-between gap-3"><div><p class="text-xs text-slate-500 dark:text-slate-400">{{ card.label }}</p><p class="mt-2 text-lg font-semibold tabular-nums text-slate-950 dark:text-white">{{ card.value }}</p></div><span :class="['grid size-9 place-items-center rounded-lg', card.tone]"><component :is="card.icon" :size="18" /></span></div>
      </article>
    </section>

    <DataPanel>
      <div class="border-b border-slate-200 px-5 py-4 dark:border-[#29476b]">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Riwayat Mutasi Stok</h2>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          Audit trail pergerakan stok masuk dan keluar.
        </p>
      </div>
      <DataTable
      :items="rows"
      :pagination="pagination"
      :per-page="Number(pagination?.meta?.per_page ?? 25)"
      :loading="loading"
      :loading-more="loadingMore"
      infinite
      require-scroll-for-load-more
      searchable
      :search="search"
      search-placeholder="Cari produk, SKU, atau referensi..."
      empty-icon="clipboard-list"
      empty-title="Tidak ada data"
      empty-message="Belum ada mutasi stok yang tercatat."
      @filter="filter"
      @per-page-change="changePerPage"
      @load-more="loadMore"
    >
      <template #filters>
        <div ref="filterButton">
          <Button variant="secondary" size="sm" @click="toggleFilter">
            <Filter :size="16" class="mr-2" />Filter
          </Button>
        </div>
        <InventoryMovementFilters
          v-if="filterOpen"
          :filters="props.filters"
          :options="props.options"
          :panel-style="filterPanelStyle"
          :trigger-element="filterButton"
          @close="filterOpen = false"
          @apply="applyFilters"
        />
      </template>
      <template #thead>
        <tr>
          <th>Tanggal</th>
          <th>Produk</th>
          <th>Tipe</th>
          <th class="text-right">Qty</th>
          <th>Saldo</th>
          <th>Lokasi</th>
          <th>Referensi</th>
          <th>User</th>
        </tr>
      </template>

      <tr v-for="row in rows" :key="row.id">
        <td class="whitespace-nowrap px-5 py-4 text-xs">{{ formatDate(row.created_at) }}</td>
        <td class="px-5 py-4">
          <p class="font-medium text-slate-900 dark:text-white">{{ row.product_name ?? '-' }}</p>
          <p class="text-xs text-slate-500 dark:text-slate-400">{{ row.product_sku ?? '-' }}</p>
        </td>
        <td class="px-5 py-4">
          <span :class="row.type === 'IN' ? 'text-emerald-600' : 'text-red-600'">
            {{ row.movement_type }}
          </span>
        </td>
        <td class="px-5 py-4 text-right font-semibold tabular-nums">
          {{ row.type === 'OUT' ? '-' : '+' }}{{ formatQty(row.qty) }}
          <span class="ml-1 text-xs font-normal text-slate-500 dark:text-slate-400">
            {{ row.unit_name ?? '' }}
          </span>
        </td>
        <td class="whitespace-nowrap px-5 py-4 text-xs">
          {{ formatQty(row.stock_before) }} -> {{ formatQty(row.stock_after) }}
        </td>
        <td class="px-5 py-4">{{ row.location_name ?? '-' }}</td>
        <td class="px-5 py-4 text-xs">{{ row.reference_number ?? '-' }}</td>
        <td class="px-5 py-4 text-xs">{{ row.user_name ?? '-' }}</td>
      </tr>
    </DataTable>
    </DataPanel>
  </div>
</template>
