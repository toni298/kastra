<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { Eye, MoreVertical } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import { useAccessControl } from '@/Composables/useAccessControl'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['action', 'request'])
const { can } = useAccessControl()
const page = usePage()
const returnIndexUrl = computed(() =>
  page.url.startsWith('/cashier/sales')
    ? route('cashier.sales.tab', { salesTab: 'returns' })
    : route('sales.returns.index')
)
const search = ref(props.filters.search ?? '')
const openMenu = ref(null)
const menuStyle = ref({})
const rows = computed(() => props.items?.data ?? [])
const pagination = computed(() => ({
  ...props.items,
  per_page: props.items?.meta?.per_page ?? 10,
  next_page_url: props.items?.links?.next ?? null,
}))
const statusLabel = (value) =>
  value === 'draft' ? 'Draft' : value === 'completed' ? 'Selesai' : 'Dibatalkan'
const statusVariant = (value) =>
  value === 'completed' ? 'success' : value === 'cancelled' ? 'error' : 'neutral'
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
const navigate = ({ cursor, direction }) => {
  if (!cursor) return

  const url = new URL(returnIndexUrl.value, window.location.origin)
  url.searchParams.set('cursor', cursor)

  emit('request', { url: url.toString(), data: { search: search.value || undefined, per_page: pagination.value.per_page } })
}
const applySearch = ({ search: value }) => {
  search.value = value
  request({ search: value || undefined })
}
const openDetail = (row) => emit('action', { action: 'return-detail', item: row })
const toggleMenu = async (row, event) => {
  openMenu.value = openMenu.value === row.id ? null : row.id
  if (!openMenu.value) return

  await nextTick()
  const rect = event.currentTarget.getBoundingClientRect()
  menuStyle.value = { top: `${rect.bottom + 4}px`, right: `${window.innerWidth - rect.right}px` }
}
const choose = (action, row) => {
  openMenu.value = null
  if (action === 'print-return') window.print()
  else emit('action', { action, item: row })
}
const closeMenuOnEscape = (event) => {
  if (event.key === 'Escape') openMenu.value = null
}
onMounted(() => document.addEventListener('keydown', closeMenuOnEscape))
onBeforeUnmount(() => document.removeEventListener('keydown', closeMenuOnEscape))
</script>

<template>
  <div class="space-y-5">
    <div>
      <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Retur Penjualan</h2>
      <p class="mt-1 text-sm text-slate-500">Kelola retur dan proses pengembalian stok.</p>
    </div>

    <DataPanel>
      <DataTable
        :items="rows"
        :pagination="pagination"
        :loading="loading"
        sticky-toolbar
        searchable
        :search="search"
        search-placeholder="Cari nomor retur, transaksi, atau pelanggan..."
        empty-icon="rotate-ccw"
        empty-title="Tidak ada retur"
        empty-message="Data retur penjualan belum tersedia."
        @navigate="navigate"
        @filter="applySearch"
        @per-page-change="(perPage) => request({ per_page: perPage })"
      >
        <template #thead>
          <tr>
            <th>Nomor Retur</th>
            <th>Transaksi</th>
            <th>Pelanggan</th>
            <th>Status</th>
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
            <p class="font-mono font-semibold dark:text-white">{{ row.return_number }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.date }}</p>
          </td>
          <td class="px-5 py-4">
            <p class="font-mono font-medium dark:text-white">{{ row.reference_number || '-' }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.reason_label }}</p>
          </td>
          <td class="px-5 py-4">{{ row.customer }}</td>
          <td class="px-5 py-4">
            <Badge :variant="statusVariant(row.status)">{{ statusLabel(row.status) }}</Badge>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              {{ row.details_count }} item
            </p>
          </td>
          <td class="px-5 py-4 text-right font-semibold dark:text-white">{{ money(row.total) }}</td>
          <td class="px-5 py-4" @click.stop>
            <div class="flex justify-end">
              <IconButton label="Aksi retur" @click="toggleMenu(row, $event)">
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
          v-if="can('penjualan.returns.view')"
          type="button"
          class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm hover:bg-slate-50 dark:text-white dark:hover:bg-[#163354]"
          @click="
            choose(
              'return-detail',
              rows.find((row) => row.id === openMenu)
            )
          "
        >
          <Eye :size="16" />Detail
        </button>
      </div>
    </Teleport>
  </div>
</template>
