<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Eye, Filter, MoreVertical, Pencil, Plus, ShoppingCart, Trash2 } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import SalesTransactionFilters from './SalesTransactionFilters.vue'
import { useAccessControl } from '@/Composables/useAccessControl'
import { useRoute } from 'ziggy-js'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ branches: [] }) },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['action', 'request'])
const page = usePage()
const route = useRoute(page.props.ziggy)
const transactionIndexUrl = computed(() =>
  page.url.startsWith('/cashier/sales')
    ? route('cashier.sales.tab', { salesTab: 'transactions' })
    : route('sales.transactions.index')
)
const isCashierTransactions = computed(() => page.url.startsWith('/cashier/sales/transactions'))
const { can } = useAccessControl()
const search = ref(props.filters.search ?? '')
const transactionFilters = ref({
  branch_id: props.filters.branch_id ?? '',
  status: props.filters.status ?? '',
  payment_status: props.filters.payment_status ?? '',
  date_from: props.filters.date_from ?? '',
  date_to: props.filters.date_to ?? '',
})
const tabs = [
  { id: 'all', label: 'Semua' },
  { id: 'completed', label: 'Selesai' },
  { id: 'unpaid', label: 'Belum Lunas' },
  { id: 'draft', label: 'Draft' },
  { id: 'overdue', label: 'Jatuh Tempo' },
]
const activeTab = ref(props.filters.tab ?? 'all')
const filterOpen = ref(false)
const filterButton = ref(null)
const filterPanelStyle = ref({})
const openMenu = ref(null)
const menuStyle = ref({})
const rows = computed(() => props.items?.data ?? [])
const pagination = computed(() => ({
  ...props.items,
  per_page: props.items?.meta?.per_page ?? 10,
  next_page_url: props.items?.links?.next ?? null,
}))
const statusLabel = (row) =>
  row.status === 'completed'
    ? 'Selesai'
    : row.status === 'return' || row.status === 'partial_return'
      ? 'Retur Sebagian'
      : row.status === 'full_return'
        ? 'Retur Penuh'
        : row.status === 'cancelled'
          ? 'Dibatalkan'
          : row.status === 'draft'
            ? 'Draft'
            : row.status === 'pending'
              ? 'Dalam Proses'
              : row.status
const paymentLabel = (row) => (row.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas')
const documentLabel = (row) => (row.document_type === 'invoice' ? 'Invoice' : 'Penjualan')
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
const editable = (row) => row.status === 'draft'
const variant = (value) =>
  value === 'Lunas' || value === 'Selesai'
    ? 'success'
    : value === 'Dibatalkan'
      ? 'error'
      : value === 'Retur Penuh'
        ? 'error'
        : 'warning'
const actions = (row) => {
  return [
    { id: 'transaction-detail', label: 'Detail', icon: Eye },
    ...(editable(row) && can('penjualan.transactions.edit')
      ? [{ id: 'transaction-edit', label: 'Edit', icon: Pencil }]
      : []),
    ...(editable(row) && can('penjualan.transactions.delete')
      ? [{ id: 'transaction-delete', label: 'Hapus', icon: Trash2 }]
      : []),
  ]
}
const requestData = (overrides = {}) => ({
  tab: activeTab.value === 'all' ? undefined : activeTab.value,
  search: search.value || undefined,
  ...transactionFilters.value,
  per_page: pagination.value.per_page,
  ...overrides,
})
const request = (data = requestData()) => emit('request', { url: transactionIndexUrl.value, data })

const navigate = ({ cursor }) => {
  if (!cursor) return

  const url = new URL(transactionIndexUrl.value, window.location.origin)
  url.searchParams.set('cursor', cursor)

  emit('request', { url: url.toString(), data: requestData() })
}
const selectTab = (tab) => {
  activeTab.value = tab
  request(requestData({ tab: tab === 'all' ? undefined : tab }))
}
const applySearch = ({ search: value }) => {
  search.value = value
  request(requestData({ search: value || undefined }))
}
const applyFilters = (filters) => {
  transactionFilters.value = filters
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
const toggleMenu = async (row, event) => {
  openMenu.value = openMenu.value === row.id ? null : row.id
  if (!openMenu.value) return

  await nextTick()
  const rect = event.currentTarget.getBoundingClientRect()
  menuStyle.value = { top: `${rect.bottom + 4}px`, right: `${window.innerWidth - rect.right}px` }
}
const choose = (action, row) => {
  openMenu.value = null
  emit('action', { action, item: row })
}
const openDetail = (row) => emit('action', { action: 'transaction-detail', item: row })
const closeMenuOnEscape = (event) => {
  if (event.key === 'Escape') openMenu.value = null
}
onMounted(() => document.addEventListener('keydown', closeMenuOnEscape))
onBeforeUnmount(() => document.removeEventListener('keydown', closeMenuOnEscape))
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold dark:text-white">Transaksi Penjualan</h2>
        <p class="mt-1 text-sm text-slate-500">
          Kelola transaksi berdasarkan nomor atau pelanggan.
        </p>
      </div>
      <div v-if="can('penjualan.transactions.create')" class="flex flex-wrap gap-2">
        <Link
          v-if="can('cashier.access')"
          :href="route('cashier')"
          class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
        >
          <ShoppingCart :size="16" class="mr-2" />Kasir
        </Link>
        <Button
          v-if="!isCashierTransactions"
          size="sm"
          @click="emit('action', { action: 'transaction-create' })"
        >
          <Plus :size="16" class="mr-2" />Tambah Transaksi
        </Button>
      </div>
    </div>

    <DataPanel>
      <div class="border-b border-slate-200 px-4 pt-4 dark:border-[#29476b]">
        <div
          class="flex min-w-max gap-5 overflow-x-auto"
          role="tablist"
          aria-label="Status transaksi"
        >
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            role="tab"
            :aria-selected="activeTab === tab.id"
            :class="[
              'whitespace-nowrap border-b-2 px-1 py-2.5 text-sm font-medium transition-colors focus:outline-none focus-visible:border-blue-600 focus-visible:text-blue-700',
              activeTab === tab.id
                ? 'border-blue-600 text-blue-700'
                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-900 dark:text-slate-300 dark:hover:border-[#476789] dark:hover:text-white',
            ]"
            @click="selectTab(tab.id)"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>
      <DataTable
        :items="rows"
        :pagination="pagination"
        :loading="loading"
        sticky-toolbar
        searchable
        :search="search"
        search-placeholder="Cari nomor transaksi atau pelanggan..."
        empty-icon="shopping-cart"
        empty-title="Tidak ada transaksi"
        empty-message="Transaksi penjualan belum tersedia."
        @navigate="navigate"
        @filter="applySearch"
        @per-page-change="(perPage) => request(requestData({ per_page: perPage }))"
      >
        <template #filters>
          <div ref="filterButton">
            <Button variant="secondary" size="sm" @click="toggleFilter">
              <Filter :size="16" class="mr-2" />Filter
            </Button>
          </div>
          <SalesTransactionFilters
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
            <th>Nomor Dokumen</th>
            <th>Pelanggan</th>
            <th>Status</th>
            <th>Pembayaran</th>
            <th class="text-right">Total</th>
            <th class="text-right">Aksi</th>
          </tr>
        </template>
        <tr
          v-for="row in rows"
          :key="row.id"
          class="cursor-pointer text-sm"
          tabindex="0"
          @click="openDetail(row)"
          @keydown.enter="openDetail(row)"
        >
          <td class="px-5 py-4">
            <p class="font-mono font-semibold dark:text-white">{{ row.number }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.date }}</p>
          </td>
          <td class="px-5 py-4">{{ row.customer }}</td>
          <td class="px-5 py-4">
            <Badge :variant="variant(statusLabel(row))">{{ statusLabel(row) }}</Badge>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ documentLabel(row) }}</p>
          </td>
          <td class="px-5 py-4">
            <Badge :variant="variant(paymentLabel(row))">{{ paymentLabel(row) }}</Badge>
            <Badge v-if="row.due_status" :variant="row.due_status.variant" class="mt-1">{{
              row.due_status.label
            }}</Badge>
          </td>
          <td class="px-5 py-4 text-right font-semibold dark:text-white">{{ money(row.total) }}</td>
          <td class="px-5 py-4" @click.stop>
            <div class="flex justify-end">
              <IconButton label="Aksi transaksi" @click="toggleMenu(row, $event)">
                <MoreVertical :size="18" />
              </IconButton>
            </div>
          </td>
        </tr>
      </DataTable>
    </DataPanel>

    <Teleport to="body">
      <div
        v-if="openMenu"
        class="fixed inset-0 z-[99]"
        aria-hidden="true"
        @click="openMenu = null"
      ></div>
      <div
        v-if="openMenu"
        :style="menuStyle"
        class="fixed z-[100] w-52 overflow-hidden rounded-xl border bg-white py-1 shadow-xl dark:border-[#29476b] dark:bg-[#102542]"
      >
        <button
          v-for="item in actions(rows.find((row) => row.id === openMenu))"
          :key="item.id"
          type="button"
          class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm hover:bg-slate-50 dark:text-white dark:hover:bg-[#163354]"
          @click="
            choose(
              item.id,
              rows.find((row) => row.id === openMenu)
            )
          "
        >
          <component :is="item.icon" :size="16" />{{ item.label }}
        </button>
      </div>
    </Teleport>
  </div>
</template>
