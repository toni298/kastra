<script setup>
import {
  ClipboardCheck,
  Eye,
  Filter,
  MoreVertical,
  Pencil,
  Play,
  Plus,
  Trash2,
} from 'lucide-vue-next'
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import InventoryStockOpnameFilters from './InventoryStockOpnameFilters.vue'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  pagination: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ warehouses: [], branches: [] }) },
  loading: { type: Boolean, default: false },
  canCreate: { type: Boolean, default: false },
  canEdit: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
  canProcess: { type: Boolean, default: false },
})
const emit = defineEmits(['action', 'request', 'navigate'])
const search = ref(props.filters.search ?? '')
const filterOpen = ref(false)
const filterButton = ref(null)
const filterPanelStyle = ref({})
const opnameFilters = ref({
  gudang_id: props.filters.gudang_id ?? '',
  branch_id: props.filters.branch_id ?? '',
  status: props.filters.status ?? '',
  date_from: props.filters.date_from ?? '',
  date_to: props.filters.date_to ?? '',
})
const rows = () => props.items?.data ?? []
const requestData = (perPage = Number(props.pagination?.meta?.per_page ?? 10)) => ({
  search: search.value || undefined,
  per_page: perPage,
  ...opnameFilters.value,
})
const request = (perPage = Number(props.pagination?.meta?.per_page ?? 10)) =>
  emit('request', {
    tab: 'opname',
    url: route('inventory.opnames'),
    data: requestData(perPage),
    replace: true,
  })
const filter = ({ search: value }) => {
  search.value = value
  request()
}
const navigate = ({ cursor }) => {
  if (!cursor) return
  const url = new URL(route('inventory.opnames'), window.location.origin)
  url.searchParams.set('cursor', cursor)
  emit('request', { tab: 'opname', url: url.toString(), replace: true })
}
const changePerPage = (perPage) => request(perPage)
const applyFilters = (filters) => {
  opnameFilters.value = filters
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
const openMenu = ref(null)
const menuStyle = ref({})
const menuButton = ref(null)
const menuItems = (row) => [
  { id: 'detail', label: 'Lihat Detail', icon: Eye },
  ...(props.canProcess && (row.status_code === 'draft' || row.status_code === 'in_progress')
    ? [{ id: 'process', label: 'Lanjutkan', icon: Play }]
    : []),
  ...(props.canEdit && (row.status_code === 'draft' || row.status_code === 'in_progress')
    ? [{ id: 'edit', label: 'Edit', icon: Pencil }]
    : []),
  ...(props.canDelete && row.status_code === 'draft'
    ? [{ id: 'delete', label: 'Hapus', icon: Trash2, danger: true }]
    : []),
]
const choose = (action, row) => {
  openMenu.value = null
  if (action === 'delete') {
    if (window.confirm(`Hapus stock opname ${row.number}?`))
      router.delete(route('inventory.opnames.destroy', row.id), { preserveScroll: true })
    return
  }
  emit('action', { action, type: 'opname', item: row })
}
const toggleMenu = async (row, event) => {
  openMenu.value = openMenu.value === row.id ? null : row.id
  if (openMenu.value) {
    menuButton.value = event.currentTarget
    await nextTick()
    const rect = menuButton.value.getBoundingClientRect()
    menuStyle.value = {
      position: 'fixed',
      top: `${rect.bottom + 4}px`,
      right: `${window.innerWidth - rect.right}px`,
    }
  }
}
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
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Stock Opname</h2>
        <p class="mt-1 text-sm text-slate-500">
          Pantau aktivitas pemeriksaan stok fisik setiap gudang.
        </p>
      </div>
      <Button
        v-if="canCreate"
        size="sm"
        @click="emit('action', { action: 'create', type: 'opname' })"
        ><Plus :size="16" class="mr-2" />Buat Stock Opname</Button
      >
    </div>
    <DataPanel>
      <DataTable
        :items="rows()"
        :pagination="pagination"
        :per-page="Number(pagination?.meta?.per_page ?? 10)"
        :loading="loading"
        sticky-toolbar
        searchable
        :search="search"
        search-placeholder="Cari gudang atau nomor opname..."
        empty-icon="clipboard-check"
        empty-title="Tidak ada data"
        empty-message="Stock opname tidak ditemukan."
        @filter="filter"
        @per-page-change="changePerPage"
        @navigate="navigate"
      >
        <template #filters
          ><div ref="filterButton">
            <Button variant="secondary" size="sm" @click="toggleFilter"
              ><Filter :size="16" class="mr-2" />Filter</Button
            >
          </div>
          <InventoryStockOpnameFilters
            v-if="filterOpen"
            :filters="opnameFilters"
            :options="options"
            :panel-style="filterPanelStyle"
            :trigger-element="filterButton"
            @close="filterOpen = false"
            @apply="applyFilters"
        /></template>
        <template #thead
          ><tr>
            <th>
              <span class="inline-flex items-center gap-2"
                ><ClipboardCheck :size="14" />Opname</span
              >
            </th>
            <th>Progress</th>
            <th>Selisih</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th class="text-right">Aksi</th>
          </tr></template
        >
        <tr v-for="row in rows()" :key="row.id">
          <td class="px-5 py-4">
            <div class="font-medium text-gray-900 dark:text-white">
              {{ row.number }}
            </div>

            <div class="mt-1 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
              📦
              <span>{{ row.warehouse }}</span>
            </div>
          </td>
          <td class="min-w-[170px] px-5 py-4 dark:text-white">
            <div class="flex justify-between text-xs">
              <span>{{ row.checked_products }}/{{ row.total_products }} produk</span
              ><span>{{ row.progress }}%</span>
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-[#163354]">
              <div
                class="h-full rounded-full bg-emerald-500"
                :style="{ width: `${row.progress}%` }"
              ></div>
            </div>
          </td>
          <td
            class="px-5 py-4 font-semibold"
            :class="
              row.difference < 0
                ? 'text-red-600 dark:text-red-300'
                : row.difference > 0
                  ? 'text-emerald-600 dark:text-emerald-300'
                  : 'text-slate-500'
            "
          >
            {{ row.difference_label }}
          </td>
          <td class="px-5 py-4">
            <Badge :variant="row.variant">{{ row.status }}</Badge>
          </td>
          <td class="px-5 py-4 dark:text-white">{{ row.date }}</td>
          <td class="px-5 py-4 flex justify-end" @click.stop>
            <IconButton label="Aksi stock opname" @click="toggleMenu(row, $event)"
              ><MoreVertical :size="18"
            /></IconButton>
          </td>
        </tr>
      </DataTable>
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
          class="z-[100] w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl dark:border-[#29476b] dark:bg-[#102542]"
        >
          <button
            v-for="item in menuItems(rows().find((row) => row.id === openMenu))"
            :key="item.id"
            type="button"
            :class="[
              'flex w-full items-center gap-2.5 px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-[#163354]',
              item.danger ? 'text-red-600 dark:text-red-300' : 'text-slate-600 dark:text-slate-300',
            ]"
            @click="
              choose(
                item.id,
                rows().find((row) => row.id === openMenu)
              )
            "
          >
            <component :is="item.icon" :size="15" />{{ item.label }}
          </button>
        </div>
      </Teleport>
    </DataPanel>
  </div>
</template>
