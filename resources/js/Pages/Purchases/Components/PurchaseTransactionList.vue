<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { EllipsisVertical, Eye, Filter, Pencil, Plus, Trash2 } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import { useAccessControl } from '@/Composables/useAccessControl'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import PurchaseTransactionSummaryCards from './PurchaseTransactionSummaryCards.vue'
import PurchaseTransactionFilterPopover from './PurchaseTransactionFilterPopover.vue'

const props = defineProps({
  transactions: { type: Array, default: () => [] },
  pagination: { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ suppliers: [] }) },
  summary: { type: Object, default: () => ({}) },
})
const { can } = useAccessControl()
const emit = defineEmits(['action', 'create', 'request'])
const search = ref(props.filters.search ?? '')
const transactionFilters = ref({
  supplier_id: props.filters.supplier_id ?? '',
  document_type: props.filters.document_type ?? '',
  status: props.filters.status ?? '',
  payment_status: props.filters.payment_status ?? '',
  date_from: props.filters.date_from ?? '',
  date_to: props.filters.date_to ?? '',
})
const filterOpen = ref(false)
const filterButton = ref(null)
const filterPanelStyle = ref({})
const openMenu = ref(null)
const pagination = computed(() => ({
  ...props.pagination,
  per_page: props.pagination?.meta?.per_page ?? 10,
}))
const status = (value) =>
  value === 'completed'
    ? 'Selesai'
    : value === 'cancelled'
      ? 'Dibatalkan'
      : value === 'draft'
        ? 'Draft'
        : value === 'received'
          ? 'Barang Diterima'
          : value === 'ordered'
            ? 'Menunggu Barang'
            : value
const statusVariant = (value) =>
  value === 'completed'
    ? 'success'
    : value === 'cancelled'
      ? 'error'
      : value === 'draft'
        ? 'neutral'
        : 'info'
const payment = (value) =>
  value === 'paid' ? 'Lunas' : value === 'partial' ? 'Sebagian Dibayar' : 'Belum Dibayar'
const paymentVariant = (value) =>
  value === 'paid' ? 'success' : value === 'partial' ? 'warning' : 'error'
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
const requestData = (overrides = {}) => ({
  search: search.value || undefined,
  ...transactionFilters.value,
  per_page: pagination.value.per_page,
  ...overrides,
})
const request = (url = route('purchases.transactions.index'), data = requestData()) =>
  router.get(url, data, { preserveScroll: true, preserveState: true, replace: true })
const navigate = ({ cursor, direction }) => {
  if (!cursor) return

  const url = new URL(route('purchases.transactions.index'), window.location.origin)
  url.searchParams.set('cursor', cursor)

  router.get(url.toString(), requestData(), { preserveScroll: true, preserveState: true, replace: true })
}
const filter = ({ search: value }) => {
  search.value = value
  request(route('purchases.transactions.index'), requestData({ search: value || undefined }))
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
const toggleMenu = (row) => {
  openMenu.value = openMenu.value === row.id ? null : row.id
}
const chooseAction = (action, row) => {
  openMenu.value = null
  emit('action', { action, item: row })
}
</script>
<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Transaksi Pembelian</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola purchase order dan invoice pembelian.</p>
      </div>
      <Button v-if="can('pembelian.create')" size="sm" @click="emit('create')"
        ><Plus :size="16" class="mr-2" />Pembelian Baru</Button
      >
    </div>
    <PurchaseTransactionSummaryCards :summary="summary" />
    <DataPanel>
      <DataTable
        :items="transactions"
        :pagination="pagination"
        sticky-toolbar
        searchable
        :search="search"
        search-placeholder="Cari nomor atau supplier..."
        empty-icon="file-text"
        empty-title="Tidak ada transaksi"
        empty-message="Transaksi pembelian belum tersedia."
        @navigate="navigate"
        @filter="filter"
        @per-page-change="
          (perPage) =>
            request(route('purchases.transactions.index'), requestData({ per_page: perPage }))
        "
        ><template #filters
          ><div ref="filterButton">
            <Button variant="secondary" size="sm" @click="toggleFilter"
              ><Filter :size="16" class="mr-2" />Filter</Button
            >
          </div>
          <PurchaseTransactionFilterPopover
            v-if="filterOpen"
            :filters="transactionFilters"
            :options="options"
            :panel-style="filterPanelStyle"
            :trigger-element="filterButton"
            @close="filterOpen = false"
            @apply="applyFilters" /></template
        ><template #thead
          ><tr>
            <th>Nomor Dokumen</th>
            <th>Supplier</th>
            <th>Status</th>
            <th>Pembayaran</th>
            <th class="text-right">Total</th>
            <th class="text-right">Aksi</th>
          </tr></template
        >
        <tr
          v-for="row in transactions"
          :key="row.id"
          class="cursor-pointer text-sm transition hover:bg-emerald-50/50 dark:hover:bg-[#163354]"
          @click="emit('action', { action: 'transaction-detail', item: row })"
        >
          <td class="px-5 py-4">
            <p class="font-mono font-semibold dark:text-white">{{ row.number }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.date }}</p>
          </td>
          <td class="px-5 py-4">{{ row.supplier }}</td>
          <td class="px-5 py-4">
            <Badge :variant="statusVariant(row.status)">{{ status(row.status) }}</Badge>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.typeShort }}</p>
          </td>
          <td class="px-5 py-4">
            <Badge :variant="paymentVariant(row.payment)">{{ payment(row.payment) }}</Badge>
            <Badge v-if="row.due_status" :variant="row.due_status.variant" class="mt-1">{{
              row.due_status.label
            }}</Badge>
          </td>
          <td class="px-5 py-4 text-right font-semibold">{{ money(row.total) }}</td>
          <td class="px-5 py-4" @click.stop>
            <div class="relative flex justify-end">
              <IconButton label="Kelola transaksi" @click="toggleMenu(row)"
                ><EllipsisVertical :size="18"
              /></IconButton>
              <div
                v-if="openMenu === row.id"
                class="absolute right-0 top-10 z-20 w-40 rounded-xl border border-slate-200 bg-white py-1 shadow-xl dark:border-[#29476b] dark:bg-[#102542]"
              >
                <button
                  type="button"
                  class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm dark:text-white"
                  @click="chooseAction('transaction-detail', row)"
                >
                  <Eye :size="15" />Detail
                </button>
                <button
                  v-if="row.status === 'draft' && can('pembelian.edit')"
                  type="button"
                  class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm dark:text-white"
                  @click="chooseAction('edit-purchase', row)"
                >
                  <Pencil :size="15" />Edit
                </button>
                <button
                  v-if="row.status === 'draft' && can('pembelian.delete')"
                  type="button"
                  class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600"
                  @click="chooseAction('delete-purchase', row)"
                >
                  <Trash2 :size="15" />Hapus
                </button>
              </div>
            </div>
          </td>
        </tr></DataTable
      >
    </DataPanel>
  </div>
</template>
