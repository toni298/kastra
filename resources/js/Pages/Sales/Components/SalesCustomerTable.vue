<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { MoreVertical, Pencil, Plus, Trash2, UserRound } from 'lucide-vue-next'
import { router, usePage } from '@inertiajs/vue3'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import { useAccessControl } from '@/Composables/useAccessControl'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['action', 'request'])
const { can } = useAccessControl()
const page = usePage()
const customerIndexUrl = computed(() =>
  page.url.startsWith('/cashier/sales')
    ? route('cashier.sales.tab', { salesTab: 'customers' })
    : route('sales.customers.index')
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
const request = (data = {}) =>
  emit('request', {
    url: customerIndexUrl.value,
    data: { search: search.value || undefined, per_page: pagination.value.per_page, ...data },
  })
const navigate = ({ cursor, direction }) => {
  if (!cursor) return

  const url = new URL(customerIndexUrl.value, window.location.origin)
  url.searchParams.set('cursor', cursor)

  emit('request', { url: url.toString(), data: { search: search.value || undefined, per_page: pagination.value.per_page } })
}
const applySearch = ({ search: value }) => {
  search.value = value
  request({ search: value || undefined })
}
const deleteCustomer = (row) => {
  openMenu.value = null
  if (window.confirm(`Hapus pelanggan ${row.name}?`))
    router.delete(route('sales.customers.destroy', row.id), { preserveScroll: true })
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
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Daftar Pelanggan</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola data pelanggan dan riwayat transaksi.</p>
      </div>
      <Button
        v-if="can('penjualan.customers.create')"
        size="sm"
        @click="emit('action', { action: 'create' })"
        ><Plus :size="16" class="mr-2" />Tambah Pelanggan</Button
      >
    </div>
    <DataPanel>
      <DataTable
        :items="rows"
        :pagination="pagination"
        :loading="loading"
        sticky-toolbar
        searchable
        :search="search"
        search-placeholder="Cari nama, telepon, atau alamat pelanggan..."
        empty-icon="users"
        empty-title="Tidak ada pelanggan"
        empty-message="Data pelanggan belum tersedia."
        @navigate="navigate"
        @filter="applySearch"
        @per-page-change="(perPage) => request({ per_page: perPage })"
      >
        <template #filters
          ><span class="text-sm text-slate-500">{{ rows.length }} pelanggan</span></template
        >
        <template #thead
          ><tr>
            <th>
              <span class="inline-flex items-center gap-2"><UserRound :size="14" />Pelanggan</span>
            </th>
            <th>Kontak</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
          </tr></template
        >
        <tr
          v-for="row in rows"
          :key="row.id"
          class="cursor-pointer text-sm"
          tabindex="0"
          @click="emit('action', { action: 'customer-detail', item: row })"
          @keydown.enter="emit('action', { action: 'customer-detail', item: row })"
        >
          <td class="px-5 py-4">
            <p class="font-semibold dark:text-white">{{ row.name }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              {{ row.branch || row.address || 'Alamat belum diisi' }}
            </p>
          </td>
          <td class="px-5 py-4">
            <p class="font-medium dark:text-white">{{ row.telp || '-' }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              {{ row.transactions_count }} transaksi
            </p>
          </td>
          <td class="px-5 py-4">
            <Badge :variant="row.status === 'active' ? 'success' : 'error'">{{
              row.status === 'active' ? 'Aktif' : 'Nonaktif'
            }}</Badge>
          </td>
          <td class="px-5 py-4" @click.stop>
            <div class="flex justify-end">
              <IconButton label="Aksi pelanggan" @click="toggleMenu(row, $event)"
                ><MoreVertical :size="18"
              /></IconButton>
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
          v-if="can('penjualan.customers.edit')"
          type="button"
          class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm hover:bg-slate-50 dark:text-white dark:hover:bg-[#163354]"
          @click="
            choose(
              'edit',
              rows.find((row) => row.id === openMenu)
            )
          "
        >
          <Pencil :size="16" />Edit</button
        ><button
          v-if="can('penjualan.customers.delete')"
          type="button"
          class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30"
          @click="deleteCustomer(rows.find((row) => row.id === openMenu))"
        >
          <Trash2 :size="16" />Hapus
        </button>
      </div>
    </Teleport>
  </div>
</template>
