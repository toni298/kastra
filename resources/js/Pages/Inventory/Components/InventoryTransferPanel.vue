<script setup>
import { ArrowRightLeft, EllipsisVertical, Filter, Plus } from '@lucide/vue'
import { computed, nextTick, ref } from 'vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import InventoryTransferFilters from './InventoryTransferFilters.vue'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  pagination: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ warehouses: [], branches: [] }) },
  loading: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
  canCreate: { type: Boolean, default: false },
})
const emit = defineEmits(['action', 'request', 'load-more'])
const search = ref(props.filters.search ?? '')
const filterOpen = ref(false)
const filterButton = ref(null)
const filterPanelStyle = ref({})
const transferFilters = ref({
  source_gudang_id: props.filters.source_gudang_id ?? '',
  destination_type: props.filters.destination_type ?? '',
  destination_gudang_id: props.filters.destination_gudang_id ?? '',
  destination_branch_id: props.filters.destination_branch_id ?? '',
  status: props.filters.status ?? '',
  date_from: props.filters.date_from ?? '',
  date_to: props.filters.date_to ?? '',
})
const rows = computed(() => props.items?.data ?? [])
const requestData = (overrides = {}) => ({
  search: search.value || undefined,
  source_gudang_id: transferFilters.value.source_gudang_id || undefined,
  destination_type: transferFilters.value.destination_type || undefined,
  destination_gudang_id: transferFilters.value.destination_gudang_id || undefined,
  destination_branch_id: transferFilters.value.destination_branch_id || undefined,
  status: transferFilters.value.status || undefined,
  date_from: transferFilters.value.date_from || undefined,
  date_to: transferFilters.value.date_to || undefined,
  per_page: Number(props.pagination?.meta?.per_page ?? 10),
  ...overrides,
})

const filter = ({ search: value }) => {
  search.value = value
  emit('request', {
    tab: 'transfer',
    url: route('inventory.transfers'),
    data: requestData({ search: value || undefined }),
    replace: true,
  })
}
const loadMore = (url) => emit('load-more', url)
const changePerPage = (perPage) =>
  emit('request', {
    tab: 'transfer',
    url: route('inventory.transfers'),
    data: requestData({ per_page: perPage }),
    replace: true,
  })
const applyFilters = (filters) => {
  transferFilters.value = filters
  filterOpen.value = false
  emit('request', {
    tab: 'transfer',
    url: route('inventory.transfers'),
    data: requestData(),
    replace: true,
  })
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
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Transfer Gudang</h2>
        <p class="mt-1 text-sm text-slate-500">Pindahkan satu atau beberapa produk antar gudang.</p>
      </div>
      <Button
        v-if="canCreate"
        size="sm"
        @click="emit('action', { action: 'create', type: 'transfer' })"
        ><Plus :size="16" class="mr-2" />Tambah Transfer</Button
      >
    </div>
    <DataPanel>
      <DataTable
        :items="rows"
        :pagination="pagination"
        :per-page="Number(pagination?.meta?.per_page ?? 10)"
        :loading="loading"
        :loading-more="loadingMore"
        infinite
        require-scroll-for-load-more
        sticky-toolbar
        searchable
        :search="search"
        search-placeholder="Cari nomor atau gudang..."
        empty-icon="arrow-right-left"
        empty-title="Tidak ada data"
        empty-message="Transfer gudang tidak ditemukan."
        @filter="filter"
        @per-page-change="changePerPage"
        @load-more="loadMore"
      >
        <template #filters
          ><div ref="filterButton">
            <Button variant="secondary" size="sm" @click="toggleFilter"
              ><Filter :size="16" class="mr-2" />Filter</Button
            >
          </div>
          <InventoryTransferFilters
            v-if="filterOpen"
            :filters="transferFilters"
            :options="options"
            :panel-style="filterPanelStyle"
            :trigger-element="filterButton"
            @close="filterOpen = false"
            @apply="applyFilters"
        /></template>
        <template #thead
          ><tr>
            <th>
              <span class="flex items-center gap-2"
                ><ArrowRightLeft :size="14" />Nomor Transfer</span
              >
            </th>
            <th>Rute Transfer</th>
            <th>Produk</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th class="text-right">Aksi</th>
          </tr></template
        >
        <tr
          v-for="row in rows"
          :key="row.id"
          class="cursor-pointer text-sm text-slate-700 dark:text-slate-200"
          tabindex="0"
          @click="emit('action', { action: 'detail', type: 'transfer', item: row })"
          @keydown.enter="emit('action', { action: 'detail', type: 'transfer', item: row })"
        >
          <td class="px-5 py-4 font-medium">{{ row.number }}</td>
          <td class="px-5 py-4">{{ row.source }} → {{ row.destination }}</td>
          <td class="px-5 py-4">{{ row.details_count }} produk</td>
          <td class="px-5 py-4">
            <Badge :variant="row.variant">{{ row.status }}</Badge>
          </td>
          <td class="px-5 py-4">{{ row.date }}</td>
          <td class="px-5 py-4">
            <div class="flex justify-end">
              <IconButton
                label="Buka detail transfer"
                @click.stop="emit('action', { action: 'detail', type: 'transfer', item: row })"
              >
                <EllipsisVertical :size="18" />
              </IconButton>
            </div>
          </td>
        </tr>
      </DataTable>
    </DataPanel>
  </div>
</template>
