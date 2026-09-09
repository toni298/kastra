<script setup>
import { computed, nextTick, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import {
  Boxes,
  Filter,
  AlertTriangle,
  ArrowDownCircle,
  ArrowUpCircle,
  PackagePlus,
} from 'lucide-vue-next'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import InventoryProductDrawer from './InventoryProductDrawer.vue'
import InventoryDiscountModal from './InventoryDiscountModal.vue'
import InventoryAdjustmentModal from './InventoryAdjustmentModal.vue'
import InventoryStockFilters from './InventoryStockFilters.vue'

const props = defineProps({
  summary: { type: Object, default: () => ({}) },
  items: { type: Object, default: () => ({ data: [] }) },
  pagination: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ categories: [], warehouses: [] }) },
  loading: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
  canDiscount: { type: Boolean, default: false },
  canAdjustment: { type: Boolean, default: false },
})

const emit = defineEmits(['navigate', 'request', 'load-more'])

const search = ref(props.filters.search ?? '')
const filterOpen = ref(false)
const filterButton = ref(null)
const filterPanelStyle = ref({})
const transactionFilters = ref({
  category_id: props.filters.category_id ?? '',
  status: props.filters.status ?? '',
})

const activeFilterCount = computed(
  () => Object.values(transactionFilters.value).filter(Boolean).length
)
const rows = computed(() => props.items?.data ?? [])
const pagination = computed(() => ({
  ...props.items,
  per_page: props.items?.meta?.per_page ?? 10,
  next_page_url: props.items?.links?.next ?? null,
}))

const groupedRows = computed(() => {
  const groups = new Map()
  rows.value.forEach((row) => {
    const key = row.branch?.id ?? row.branch_id ?? 'unknown'
    const label = row.branch?.name ?? '—'
    if (!groups.has(key)) groups.set(key, { key, label, items: [] })
    groups.get(key).items.push(row)
  })
  return [...groups.values()]
})

const cards = computed(() => [
  {
    label: 'Total Produk',
    value: props.summary?.total_products ?? 0,
    subtitle: 'Jumlah produk terdaftar',
    icon: Boxes,
    bgClass: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300',
  },
  {
    label: 'Perlu Direstok',
    value: props.summary?.to_restock ?? 0,
    subtitle: 'Perlu dipantau',
    icon: AlertTriangle,
    bgClass: 'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300',
  },
  {
    label: 'Barang Masuk Hari Ini',
    value: props.summary?.in_today ?? 0,
    subtitle: 'Masuk hari ini',
    icon: ArrowDownCircle,
    bgClass: 'bg-blue-50 text-blue-700 dark:bg-blue-400/10 dark:text-blue-300',
  },
  {
    label: 'Barang Keluar Hari Ini',
    value: props.summary?.out_today ?? 0,
    subtitle: 'Keluar hari ini',
    icon: ArrowUpCircle,
    bgClass: 'bg-red-50 text-red-700 dark:bg-red-400/10 dark:text-red-300',
  },
])

const requestData = (overrides = {}) => ({
  search: search.value || undefined,
  per_page: pagination.value.per_page,
  category_id: transactionFilters.value.category_id || undefined,
  status: transactionFilters.value.status || undefined,
  ...overrides,
})
const request = (overrides = {}) =>
  emit('request', {
    tab: 'overview',
    url: route('inventory.index'),
    data: requestData(overrides),
    replace: true,
  })

const applySearch = ({ search: value }) => {
  search.value = value
  request()
}
const applyFilters = (value) => {
  transactionFilters.value = value
  filterOpen.value = false
  request()
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

const handleSort = ({ key, direction }) => {
  request({ sort: key, sort_direction: direction })
}

const loadMore = (url) => emit('load-more', url)

const drawerOpen = ref(false)
const drawerLoading = ref(false)
const drawerError = ref(null)
const drawerItem = ref(null)
const discountModalOpen = ref(false)

const openProductDetail = async (item) => {
  drawerOpen.value = true
  drawerLoading.value = true
  drawerError.value = null
  drawerItem.value = null

  try {
    const { data } = await axios.get(route('inventory.products.detail', item.id))
    drawerItem.value = data
  } catch {
    drawerError.value = 'Gagal memuat detail produk.'
  } finally {
    drawerLoading.value = false
  }
}

const closeDrawer = () => {
  drawerOpen.value = false
  discountModalOpen.value = false
  drawerItem.value = null
  drawerError.value = null
}

const openDiscountModal = () => {
  discountModalOpen.value = true
}

const closeDiscountModal = () => {
  discountModalOpen.value = false
}

const handleDiscountSaved = (discount) => {
  if (drawerItem.value?.stock) drawerItem.value.stock.discount = discount
  closeDiscountModal()
}

// ----- Penyesuaian Stok -----
const adjustmentOpen = ref(false)
const openAdjustment = () => {
  adjustmentOpen.value = true
}
const closeAdjustment = () => {
  adjustmentOpen.value = false
}
const handleAdjustmentSaved = () => {
  closeAdjustment()
  router.reload({ preserveScroll: true })
}
</script>

<template>
  <div class="space-y-5">
    <Head title="Ringkasan Persediaan" />

    <section
      class="grid grid-cols-2 gap-3 xl:grid-cols-4"
      aria-label="Ringkasan transaksi pembelian"
    >
      <article
        v-for="card in cards"
        :key="card.label"
        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-[#29476b] dark:bg-[#102542] sm:p-5"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
            <p
              class="mt-2 truncate text-lg font-semibold tracking-tight text-slate-950 dark:text-white sm:text-xl"
            >
              {{ card.value ?? 0 }}
            </p>
          </div>
          <span :class="['grid size-9 shrink-0 place-items-center rounded-xl', card.bgClass]">
            <component :is="card.icon" width="18" height="18" />
          </span>
        </div>
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ card.subtitle }}</p>
      </article>
    </section>

    <DataPanel>
      <DataTable
        :items="rows"
        :pagination="pagination"
        :per-page="pagination.per_page"
        :loading="loading"
        :loading-more="loadingMore"
        infinite
        sticky-toolbar
        searchable
        :search="search"
        search-placeholder="Cari produk atau SKU..."
        @filter="applySearch"
        @load-more="loadMore"
        @per-page-change="(per) => request({ per_page: per })"
        @sort="handleSort"
      >
        <template #filters>
          <Button v-if="canAdjustment" variant="secondary" size="sm" @click="openAdjustment">
            <PackagePlus :size="16" class="mr-2" />Penyesuaian Stok
          </Button>
          <div ref="filterButton" class="relative">
            <Button variant="secondary" size="sm" @click="toggleFilter"
              ><Filter :size="16" class="mr-2" />Filter</Button
            >
            <span
              v-if="activeFilterCount"
              class="absolute -right-1 -top-1 grid h-4 w-4 place-items-center rounded-full bg-emerald-600 text-[10px] text-white"
              >{{ activeFilterCount }}</span
            >
          </div>
          <InventoryStockFilters
            v-if="filterOpen"
            :filters="transactionFilters"
            :options="options"
            :panel-style="filterPanelStyle"
            :trigger-element="filterButton"
            @close="filterOpen = false"
            @apply="applyFilters"
          />
        </template>

        <template #thead>
          <tr>
            <th>Produk</th>
            <th>Stok</th>
            <th>Status</th>
          </tr>
        </template>

        <template v-for="group in groupedRows" :key="group.key">
          <tr class="bg-slate-50/80 dark:bg-[#0a1b33]">
            <td
              colspan="3"
              class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500"
            >
              {{ group.label }}
              <span class="ml-2 font-normal normal-case">{{ group.items.length }} produk</span>
            </td>
          </tr>

          <tr
            v-for="item in group.items"
            :key="item.id"
            class="cursor-pointer hover:bg-slate-50 dark:hover:bg-[#163354]"
            @click="openProductDetail(item)"
          >
            <td class="min-w-[250px] px-5 py-4">
              <p class="font-medium text-slate-900 dark:text-white">
                {{ item.product?.name ?? '—' }}
              </p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ item.product?.sku ?? '—' }} · {{ item.product?.category?.name ?? '—' }}
              </p>
            </td>
            <td class="min-w-[170px] px-5 py-4">
              <p class="font-semibold text-slate-900 dark:text-white">
                {{ item.quantity ?? 0 }} {{ item.product?.unit?.name ?? '' }}
              </p>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Minimum: {{ item.product?.minimum_stock ?? 0 }} {{ item.product?.unit?.name ?? '' }}
              </p>
            </td>
            <td class="px-5 py-4">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  item.quantity === 0
                    ? 'bg-red-100 text-red-800'
                    : item.quantity <= (item.product?.minimum_stock ?? 0)
                      ? 'bg-amber-100 text-amber-800'
                      : 'bg-green-100 text-green-800',
                ]"
                >{{
                  item.quantity === 0
                    ? 'Habis'
                    : item.quantity <= (item.product?.minimum_stock ?? 0)
                      ? 'Perlu Direstok'
                      : 'Aman'
                }}</span
              >
            </td>
          </tr>
        </template>
      </DataTable>
    </DataPanel>

    <InventoryProductDrawer
      v-if="drawerOpen"
      :item="drawerItem"
      :loading="drawerLoading"
      :error="drawerError"
      :can-discount="canDiscount"
      @close="closeDrawer"
      @add-discount="openDiscountModal"
    />
    <InventoryDiscountModal
      v-if="discountModalOpen && drawerItem"
      :item="drawerItem"
      @close="closeDiscountModal"
      @saved="handleDiscountSaved"
    />

    <InventoryAdjustmentModal
      v-if="adjustmentOpen"
      :branches="options.branches ?? []"
      @close="closeAdjustment"
      @saved="handleAdjustmentSaved"
    />
  </div>
</template>
