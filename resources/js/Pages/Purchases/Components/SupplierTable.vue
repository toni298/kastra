<script setup>
import { computed, ref } from 'vue'
import { Building2, MoreVertical, Pencil, Plus, Trash2 } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import IconButton from '@/Components/UI/IconButton.vue'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
  capabilities: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['action', 'request'])
const search = ref(props.filters.search ?? '')
const openMenu = ref(null)
const menuStyle = ref({})
const rows = computed(() => props.items?.data ?? [])
const pagination = computed(() => ({
  ...props.items,
  per_page: props.items?.meta?.per_page ?? 10,
}))
const request = (data = {}) =>
  emit('request', {
    url: route('purchases.suppliers.index'),
    data: { search: search.value || undefined, per_page: pagination.value.per_page, ...data },
  })
const navigate = ({ cursor, direction }) => {
  if (!cursor) return

  const url = new URL(route('purchases.suppliers.index'), window.location.origin)
  url.searchParams.set('cursor', cursor)

  emit('request', { url: url.toString(), data: { search: search.value || undefined, per_page: pagination.value.per_page } })
}
const applySearch = ({ search: value }) => {
  search.value = value
  request({ search: value || undefined })
}
const deleteSupplier = (row) => {
  if (window.confirm(`Hapus supplier ${row.name}?`))
    router.delete(route('purchases.suppliers.destroy', row.id), { preserveScroll: true })
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
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Daftar Supplier</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola data supplier dan riwayat pembelian.</p>
      </div>
      <Button v-if="capabilities.create" size="sm" @click="emit('action', { action: 'create' })"
        ><Plus :size="16" class="mr-2" />Tambah Supplier</Button
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
        search-placeholder="Cari nama, telepon, atau email..."
        empty-icon="building-2"
        empty-title="Belum ada supplier"
        empty-message="Data supplier belum tersedia."
        @navigate="navigate"
        @filter="applySearch"
        @per-page-change="(perPage) => request({ per_page: perPage })"
      >
        <template #filters
          ><span class="text-sm text-slate-500">{{ rows.length }} supplier</span></template
        >
        <template #thead
          ><tr>
            <th>
              <span class="inline-flex items-center gap-2"><Building2 :size="14" />Supplier</span>
            </th>
            <th>Kontak</th>
            <th>Riwayat</th>
            <th class="text-right">Aksi</th>
          </tr></template
        >
        <tr
          v-for="row in rows"
          :key="row.id"
          class="cursor-pointer text-sm"
          tabindex="0"
          @click="emit('action', { action: 'supplier-detail', item: row })"
          @keydown.enter="emit('action', { action: 'supplier-detail', item: row })"
        >
          <td class="px-5 py-4">
            <p class="font-semibold dark:text-white">{{ row.name }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              {{ row.address || 'Alamat belum diisi' }}
            </p>
          </td>
          <td class="px-5 py-4">
            <p class="font-medium dark:text-white">{{ row.phone || '-' }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.email || '-' }}</p>
          </td>
          <td class="px-5 py-4">
            <p class="font-medium dark:text-white">
              {{ row.purchase_transactions_count }} transaksi
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Riwayat pembelian</p>
          </td>
          <td class="px-5 py-4" @click.stop>
            <div class="flex justify-end">
              <IconButton label="Aksi supplier" @click="toggleMenu(row, $event)"
                ><MoreVertical :size="18"
              /></IconButton>
            </div>
          </td>
        </tr>
      </DataTable>
    </DataPanel>
    <Teleport to="body"
      ><div
        v-if="openMenu"
        :style="menuStyle"
        class="fixed z-[100] w-52 overflow-hidden rounded-xl border bg-white py-1 shadow-xl dark:border-[#29476b] dark:bg-[#102542]"
      >
        <button
          v-if="capabilities.edit"
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
          v-if="capabilities.delete"
          type="button"
          class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30"
          @click="deleteSupplier(rows.find((row) => row.id === openMenu))"
        >
          <Trash2 :size="16" />Hapus
        </button>
      </div></Teleport
    >
  </div>
</template>
