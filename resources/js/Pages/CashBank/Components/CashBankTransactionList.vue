<script setup>
import { computed, nextTick, ref } from 'vue'
import {
  ArrowDownLeft,
  ArrowDownToLine,
  ArrowRightLeft,
  ArrowUpFromLine,
  ArrowUpRight,
  Eye,
  Filter,
  MoreVertical,
  Pencil,
  Trash2,
} from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import { useAccessControl } from '@/Composables/useAccessControl'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import CashBankTransactionFilterPopover from './CashBankTransactionFilterPopover.vue'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['action', 'request', 'transfer'])
const { can } = useAccessControl()
const search = ref(props.filters.search ?? '')
const openMenu = ref(null)
const filterOpen = ref(false)
const filterButton = ref(null)
const filterPanelStyle = ref({})
const transactionFilters = ref({
  type: props.filters.type ?? '',
  category: props.filters.category ?? '',
  date_from: props.filters.date_from ?? '',
  date_to: props.filters.date_to ?? '',
})

const activeFilterCount = computed(
  () => Object.values(transactionFilters.value).filter(Boolean).length
)
const rows = computed(() => props.items?.data ?? [])
const pagination = computed(() => ({
  ...props.items,
  per_page: props.items?.meta?.per_page ?? 10,
}))
const groupedRows = computed(() => {
  const groups = new Map()
  rows.value.forEach((row) => {
    const date = row.isoDate || row.date
    if (!groups.has(date)) groups.set(date, [])
    groups.get(date).push(row)
  })
  return [...groups.entries()].map(([date, items]) => ({ date, items }))
})
const requestData = (overrides = {}) => ({
  search: search.value || undefined,
  per_page: pagination.value.per_page,
  ...transactionFilters.value,
  ...overrides,
})
const request = (overrides = {}) =>
  emit('request', { url: route('cash-bank.transactions.index'), data: requestData(overrides) })
const navigate = ({ cursor, direction }) => {
  if (!cursor) return

  const url = new URL(route('cash-bank.transactions.index'), window.location.origin)
  url.searchParams.set('cursor', cursor)

  emit('request', { url: url.toString(), data: requestData() })
}
const money = (row) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Math.abs(row.amount || 0))
const labelDate = (date) => {
  const value = new Date(`${date}T00:00:00`)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const yesterday = new Date(today)
  yesterday.setDate(today.getDate() - 1)
  if (value.getTime() === today.getTime()) return 'Hari Ini'
  if (value.getTime() === yesterday.getTime()) return 'Kemarin'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(value)
}
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
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Transaksi Kas & Bank</h2>
        <p class="mt-1 text-sm text-slate-500">
          Pantau seluruh pemasukan dan pengeluaran perusahaan.
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <Button
          v-if="can('cash_bank.transactions.create')"
          variant="secondary"
          size="sm"
          @click="emit('action', { action: 'in' })"
        >
          <ArrowDownToLine :size="16" class="mr-2" />Kas Masuk
        </Button>
        <Button
          v-if="can('cash_bank.transactions.create')"
          variant="secondary"
          size="sm"
          @click="emit('action', { action: 'out' })"
        >
          <ArrowUpFromLine :size="16" class="mr-2" />Kas Keluar
        </Button>
        <Button
          v-if="can('cash_bank.transfers.create')"
          variant="secondary"
          size="sm"
          @click="emit('transfer')"
        >
          <ArrowRightLeft :size="16" class="mr-2" />Transfer Dana
        </Button>
      </div>
    </div>
    <DataPanel>
      <DataTable
        :items="rows"
        :pagination="pagination"
        :loading="loading"
        sticky-toolbar
        searchable
        :search="search"
        search-placeholder="Cari transaksi atau referensi..."
        empty-icon="wallet-cards"
        empty-title="Belum ada transaksi"
        empty-message="Transaksi kas dan bank akan tampil di sini."
        @navigate="navigate"
        @filter="applySearch"
        @per-page-change="(perPage) => request({ per_page: perPage })"
      >
        <template #filters
          ><div ref="filterButton" class="relative">
            <Button variant="secondary" size="sm" @click="toggleFilter"
              ><Filter :size="16" class="mr-2" />Filter</Button
            ><span
              v-if="activeFilterCount"
              class="absolute -right-1 -top-1 grid size-4 place-items-center rounded-full bg-emerald-600 text-[10px] text-white"
              >{{ activeFilterCount }}</span
            >
          </div>
          <CashBankTransactionFilterPopover
            v-if="filterOpen"
            :filters="transactionFilters"
            :panel-style="filterPanelStyle"
            :trigger-element="filterButton"
            @close="filterOpen = false"
            @apply="applyFilters"
        /></template>
        <template #thead
          ><tr>
            <th>Transaksi</th>
            <th>Rekening</th>
            <th class="text-right">Nominal</th>
            <th class="w-14 text-right">Aksi</th>
          </tr></template
        >
        <template v-for="group in groupedRows" :key="group.date"
          ><tr class="bg-slate-50/80 dark:bg-[#0a1b33]">
            <td
              colspan="4"
              class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500"
            >
              {{ labelDate(group.date) }}
              <span class="ml-2 font-normal normal-case">{{ group.items.length }} transaksi</span>
            </td>
          </tr>
          <tr
            v-for="row in group.items"
            :key="row.id"
            class="cursor-pointer hover:bg-slate-50 dark:hover:bg-[#163354]"
            @click="emit('action', { action: 'detail', item: row })"
          >
            <td class="px-5 py-4">
              <div class="flex items-center gap-3">
                <span
                  :class="[
                    'inline-flex rounded-lg p-2',
                    row.amount > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600',
                  ]"
                  ><ArrowDownLeft v-if="row.amount > 0" :size="16" /><ArrowUpRight
                    v-else
                    :size="16"
                /></span>
                <div>
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium dark:text-white">{{ row.description }}</span
                    ><Badge :variant="row.amount > 0 ? 'success' : 'error'">{{
                      row.amount > 0 ? 'Kas Masuk (IN)' : 'Kas Keluar (OUT)'
                    }}</Badge>
                  </div>
                  <p class="mt-1 font-mono text-xs text-slate-400">{{ row.reference }}</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-4 dark:text-white">{{ row.account || '-' }}</td>
            <td
              :class="[
                'px-5 py-4 text-right font-semibold',
                row.amount > 0 ? 'text-emerald-600' : 'text-red-600',
              ]"
            >
              {{ row.amount > 0 ? '+' : '-' }}{{ money(row) }}
            </td>
            <td class="relative px-5 py-4 text-right" @click.stop>
              <button
                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100"
                aria-label="Aksi transaksi"
                @click="openMenu = openMenu === row.id ? null : row.id"
              >
                <MoreVertical :size="18" />
              </button>
              <div
                v-if="openMenu === row.id"
                class="absolute right-5 top-11 z-20 w-36 rounded-xl border bg-white py-1 shadow-xl dark:bg-[#102542]"
              >
                <button
                  class="flex w-full items-center gap-2 px-3 py-2 text-sm"
                  @click="(emit('action', { action: 'detail', item: row }), (openMenu = null))"
                >
                  <Eye :size="15" />Detail</button
                ><button
                  v-if="can('cash_bank.transactions.edit')"
                  class="flex w-full items-center gap-2 px-3 py-2 text-sm"
                  @click="(emit('action', { action: 'edit', item: row }), (openMenu = null))"
                >
                  <Pencil :size="15" />Edit</button
                ><button
                  v-if="can('cash_bank.transactions.delete')"
                  class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600"
                  @click="(emit('action', { action: 'delete', item: row }), (openMenu = null))"
                >
                  <Trash2 :size="15" />Hapus
                </button>
              </div>
            </td>
          </tr></template
        >
      </DataTable>
    </DataPanel>
  </div>
</template>
