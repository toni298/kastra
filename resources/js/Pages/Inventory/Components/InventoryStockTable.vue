<script setup>
import { Boxes, FilePenLine, Plus, Trash2 } from '@lucide/vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import DataTableSortHeader from '@/Components/UI/DataTableSortHeader.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import InventoryStockFilters from './InventoryStockFilters.vue'
import { Filter } from '@lucide/vue'
import { ref, computed } from 'vue'

const props = defineProps({
  items: { type: Object, required: true },
  pagination: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ warehouses: [], categories: [] }) },
  hasMultipleWarehouses: { type: Boolean, default: false },
  canCreate: { type: Boolean, default: false },
  canEdit: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['request', 'navigate', 'create', 'edit', 'delete'])
const search = ref(props.filters.search ?? '')
const filters = ref({
  gudang_id: props.filters.gudang_id ?? '',
  category_id: props.filters.category_id ?? '',
  status: props.filters.status ?? '',
})
const filterOpen = ref(false)
const filterButton = ref(null)
const sortKey = ref(typeof props.filters.sort === 'string' ? props.filters.sort : 'created_at')
const sortDirection = ref(
  ['asc', 'desc'].includes(props.filters.sort_direction) ? props.filters.sort_direction : 'desc'
)
const rows = computed(() => props.items?.data ?? [])
const request = (perPage = Number(props.pagination?.meta?.per_page ?? 10)) =>
  emit('request', {
    tab: 'stock',
    url: route('inventory.stock'),
    data: {
      search: search.value || undefined,
      gudang_id: filters.value.gudang_id || undefined,
      category_id: filters.value.category_id || undefined,
      status: filters.value.status || undefined,
      per_page: perPage,
      sort: sortKey.value,
      sort_direction: sortDirection.value,
    },
    replace: true,
  })
const handleSearch = ({ search: value }) => {
  search.value = value
  request()
}
const applyFilters = (value) => {
  filters.value = value
  filterOpen.value = false
  request()
}
const toggleFilter = () => {
  filterOpen.value = !filterOpen.value
}
const handleSort = ({ key, direction }) => {
  sortKey.value = key
  sortDirection.value = direction
  request()
}
const navigate = ({ cursor }) => {
  if (!cursor) return
  const url = new URL(route('inventory.stock'), window.location.origin)
  url.searchParams.set('cursor', cursor)
  emit('request', { tab: 'stock', url: url.toString(), replace: true })
}
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Daftar Stok Gudang</h2>
        <p class="mt-1 text-sm text-slate-500">Pantau stok dan nilai persediaan per Gudang.</p>
      </div>
      <Button v-if="canCreate" size="sm" @click="emit('create')">
        <Plus :size="16" class="mr-2" />Tambah Produk
      </Button>
    </div>

    <DataPanel>
      <DataTable
        class="dark:[&>div:first-child]:border-[#29476b] dark:[&>div:first-child]:bg-[#163354] dark:[&_input]:!border-[#3b5d84] dark:[&_input]:!bg-[#163354] dark:[&_input]:!text-white dark:[&_select]:!border-[#3b5d84] dark:[&_select]:!bg-[#163354] dark:[&_select]:!text-white dark:[&_thead]:bg-[#163354] dark:[&_tbody>tr]:text-slate-100 dark:[&_tbody>tr:nth-child(even)]:bg-[#163354] dark:[&_tbody>tr:hover]:!bg-emerald-400/15"
        :items="rows"
        :pagination="pagination"
        :per-page="Number(pagination?.meta?.per_page ?? 10)"
        :loading="loading"
        sticky-toolbar
        searchable
        :search="search"
        :sort-key="sortKey"
        :sort-direction="sortDirection"
        search-placeholder="Cari nama/SKU/barcode..."
        search-class="w-full sm:max-w-[180px] lg:max-w-[210px] xl:min-w-0 xl:w-[210px]"
        empty-icon="package"
        empty-title="Tidak ada data"
        empty-message="Stok produk tidak ditemukan."
        @navigate="navigate"
        @filter="handleSearch"
        @per-page-change="request"
        @sort="handleSort"
      >
        <template #filters>
          <div ref="filterButton" class="relative inline-block">
            <Button variant="secondary" size="sm" @click="toggleFilter"
              ><Filter :size="16" class="mr-2" />Filter</Button
            >
            <InventoryStockFilters
              v-if="filterOpen"
              :filters="filters"
              :options="options"
              @close="filterOpen = false"
              @apply="applyFilters"
            />
          </div>
        </template>

        <template #thead="{ sort, sortKey: activeSort, sortDirection: direction }">
          <tr>
            <th>
              <DataTableSortHeader
                label="Produk"
                :active="activeSort === 'product_name'"
                :direction="direction"
                @sort="sort({ key: 'product_name', sortable: true })"
                ><template #icon><Boxes :size="14" /></template
              ></DataTableSortHeader>
            </th>
            <th v-if="hasMultipleWarehouses">Inventory</th>
            <th>
              <DataTableSortHeader
                label="Stok"
                :active="activeSort === 'quantity'"
                :direction="direction"
                @sort="sort({ key: 'quantity', sortable: true })"
              />
            </th>
            <th>Nilai Persediaan</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
          </tr>
        </template>

        <tr v-for="item in rows" :key="item.id">
          <td class="min-w-[250px] px-5 py-4">
            <p class="font-medium text-slate-900 dark:text-white">{{ item.product.name }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              {{ item.product.sku }} · {{ item.product.category?.name || 'Tanpa kategori' }}
            </p>
          </td>
          <td v-if="hasMultipleWarehouses" class="px-5 py-4 text-slate-700 dark:text-slate-100">
            {{ item.gudang.nama }}
          </td>
          <td class="min-w-[170px] px-5 py-4">
            <p class="font-semibold text-slate-900 dark:text-white">{{ item.quantity_display }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Minimum: {{ item.minimum_display }}
            </p>
          </td>
          <td class="px-5 py-4 font-medium text-slate-900 dark:text-white">
            {{ item.inventory_value_display }}
          </td>
          <td class="px-5 py-4">
            <Badge :variant="item.status_variant">{{ item.status_label }}</Badge>
          </td>
          <td class="px-5 py-4">
            <div class="flex justify-end gap-1">
              <IconButton
                v-if="canEdit"
                label="Edit stok produk"
                variant="info"
                @click="emit('edit', item)"
                ><FilePenLine :size="17" /></IconButton
              ><IconButton
                v-if="canDelete"
                label="Hapus dari Inventory"
                variant="danger"
                @click="emit('delete', item)"
                ><Trash2 :size="17"
              /></IconButton>
            </div>
          </td>
        </tr>
      </DataTable>
    </DataPanel>
  </div>
</template>
