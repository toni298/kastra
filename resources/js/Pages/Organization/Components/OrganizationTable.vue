<script setup>
import { Building2, FilePenLine, MapPin, Tag, Trash2 } from '@lucide/vue'
import Badge from '@/Components/UI/Badge.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import DataTableSortHeader from '@/Components/UI/DataTableSortHeader.vue'
import IconButton from '@/Components/UI/IconButton.vue'

const props = defineProps({
  items: { type: Object, required: true },
  type: { type: String, required: true },
  search: { type: String, default: '' },
  sortKey: { type: String, default: 'id' },
  sortDirection: { type: String, default: 'desc' },
  loading: { type: Boolean, default: false },
  canEdit: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
})

const emit = defineEmits(['navigate', 'filter', 'per-page-change', 'sort', 'edit', 'delete'])

const isActive = (item) =>
  props.type === 'cabang' ? item.status === 'active' : Boolean(item.aktif)
</script>

<template>
  <DataTable
    :items="items.data ?? []"
    :pagination="items"
    :per-page="items.per_page"
    :loading="loading"
    searchable
    :search="search"
    :sort-key="sortKey"
    :sort-direction="sortDirection"
    search-placeholder="Cari nama atau kode..."
    :empty-icon="type === 'gudang' ? 'warehouse' : 'building-2'"
    empty-title="Tidak ada data"
    :empty-message="`Belum ada data ${type} yang sesuai pencarian.`"
    @navigate="emit('navigate', $event)"
    @filter="emit('filter', $event)"
    @per-page-change="emit('per-page-change', $event)"
    @sort="emit('sort', $event)"
  >
    <template #filters><slot name="filters"></slot></template>
    <template #thead="{ sort, sortKey: activeSort, sortDirection: direction }">
      <tr class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-300">
        <th>
          <DataTableSortHeader
            label="Nama"
            :active="activeSort === (type === 'cabang' ? 'name' : 'nama')"
            :direction="direction"
            @sort="sort({ key: type === 'cabang' ? 'name' : 'nama', sortable: true })"
          >
            <template #icon><Building2 :size="14" /></template>
          </DataTableSortHeader>
        </th>
        <th>
          <DataTableSortHeader
            label="Kode"
            :active="activeSort === (type === 'cabang' ? 'code' : 'kode')"
            :direction="direction"
            @sort="sort({ key: type === 'cabang' ? 'code' : 'kode', sortable: true })"
          >
            <template #icon><Tag :size="14" /></template>
          </DataTableSortHeader>
        </th>
        <th v-if="type === 'cabang'" class="text-center">Toko Online</th>
        <th v-if="type === 'outlet'">
          <span class="flex items-center gap-2"><MapPin :size="14" />Cabang</span>
        </th>
        <th class="text-center">
          <DataTableSortHeader
            label="Status"
            align="center"
            :active="activeSort === (type === 'cabang' ? 'status' : 'aktif')"
            :direction="direction"
            @sort="sort({ key: type === 'cabang' ? 'status' : 'aktif', sortable: true })"
          />
        </th>
        <th class="text-right">Aksi</th>
      </tr>
    </template>
    <tr v-for="item in items.data ?? []" :key="item.id">
      <td class="px-5 py-4">
        <div class="flex flex-wrap items-center gap-2">
          <p class="font-medium text-slate-900 dark:text-white">
            {{ type === 'cabang' ? item.name : item.nama }}
          </p>
          <Badge v-if="type === 'cabang' && item.is_default" variant="info"> Cabang utama </Badge>
        </div>
        <p
          v-if="type === 'cabang' ? item.city : item.kota"
          class="mt-0.5 text-sm text-slate-500 dark:text-slate-300"
        >
          {{ type === 'cabang' ? item.city : item.kota }}
          <template v-if="type === 'cabang' ? item.province : item.provinsi">
            , {{ type === 'cabang' ? item.province : item.provinsi }}
          </template>
        </p>
      </td>
      <td class="px-5 py-4 text-sm font-medium text-slate-600 dark:text-slate-300">
        {{ type === 'cabang' ? item.code : item.kode || '-' }}
      </td>
      <td v-if="type === 'cabang'" class="px-5 py-4 text-center">
        <Badge :variant="item.is_store_enabled ? 'success' : 'error'">
          {{ item.is_store_enabled ? 'Diaktifkan' : 'Tidak Aktif' }}
        </Badge>
      </td>
      <td v-if="type === 'outlet'" class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
        {{ item.cabang?.name ?? 'Tanpa cabang' }}
      </td>
      <td class="px-5 py-4 text-center">
        <Badge :variant="isActive(item) ? 'success' : 'error'">
          {{ isActive(item) ? 'Aktif' : 'Nonaktif' }}
        </Badge>
      </td>
      <td class="px-5 py-4">
        <div class="flex justify-end gap-1">
          <IconButton
            v-if="canEdit"
            :label="`Edit ${type}`"
            variant="info"
            @click="emit('edit', item)"
          >
            <FilePenLine :size="17" />
          </IconButton>
          <IconButton
            v-if="canDelete"
            :label="`Hapus ${type}`"
            variant="danger"
            @click="emit('delete', item)"
          >
            <Trash2 :size="17" />
          </IconButton>
        </div>
      </td>
    </tr>
  </DataTable>
</template>
