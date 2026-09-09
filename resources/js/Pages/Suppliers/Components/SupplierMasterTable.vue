<script setup>
import { Building2, FilePenLine, Mail, MapPin, Trash2, UserRound } from '@lucide/vue'
import DataTable from '@/Components/UI/DataTable.vue'
import DataTableSortHeader from '@/Components/UI/DataTableSortHeader.vue'
import IconButton from '@/Components/UI/IconButton.vue'

defineProps({
  items: { type: Object, required: true },
  search: { type: String, default: '' },
  sortKey: { type: String, default: 'created_at' },
  sortDirection: { type: String, default: 'desc' },
  loading: { type: Boolean, default: false },
  canEdit: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
})

const emit = defineEmits([
  'navigate',
  'filter',
  'per-page-change',
  'sort',
  'detail',
  'edit',
  'delete',
])
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
    search-placeholder="Cari nama, kontak, email, atau alamat..."
    empty-icon="package"
    empty-title="Tidak ada supplier"
    empty-message="Belum ada supplier yang sesuai pencarian."
    @navigate="emit('navigate', $event)"
    @filter="emit('filter', $event)"
    @per-page-change="emit('per-page-change', $event)"
    @sort="emit('sort', $event)"
  >
    <template #thead="{ sort, sortKey: activeSort, sortDirection: direction }">
      <tr>
        <th>
          <DataTableSortHeader
            label="Supplier"
            :active="activeSort === 'name'"
            :direction="direction"
            @sort="sort({ key: 'name', sortable: true })"
          >
            <template #icon><Building2 :size="14" /></template>
          </DataTableSortHeader>
        </th>
        <th>
          <DataTableSortHeader
            label="Kontak Supplier"
            :active="activeSort === 'contact_supplier'"
            :direction="direction"
            @sort="sort({ key: 'contact_supplier', sortable: true })"
          />
        </th>
        <th>Email</th>
        <th>Alamat</th>
        <th class="text-right">Aksi</th>
      </tr>
    </template>

    <tr
      v-for="supplier in items.data ?? []"
      :key="supplier.id"
      class="cursor-pointer"
      @click="emit('detail', supplier)"
    >
      <td class="px-5 py-4">
        <div class="flex items-center gap-3">
          <span
            class="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
          >
            <Building2 :size="16" />
          </span>
          <div class="min-w-0">
            <p class="truncate font-medium text-slate-900 dark:text-white">{{ supplier.name }}</p>
            <p class="truncate text-sm text-slate-500">Data master supplier</p>
          </div>
        </div>
      </td>
      <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
        <p class="flex items-center gap-1.5">
          <UserRound :size="14" />{{ supplier.contact_supplier }}
        </p>
      </td>
      <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
        <p class="flex items-center gap-1.5"><Mail :size="14" />{{ supplier.email || '-' }}</p>
      </td>
      <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
        <p class="flex max-w-sm items-start gap-1.5">
          <MapPin :size="14" class="mt-0.5 shrink-0" />
          <span class="line-clamp-2">{{ supplier.address }}</span>
        </p>
      </td>
      <td class="px-5 py-4">
        <div class="flex justify-end gap-1">
          <IconButton
            v-if="canEdit"
            label="Edit supplier"
            variant="info"
            @click.stop="emit('edit', supplier)"
            ><FilePenLine :size="17"
          /></IconButton>
          <IconButton
            v-if="canDelete"
            label="Hapus supplier"
            variant="danger"
            @click.stop="emit('delete', supplier)"
            ><Trash2 :size="17"
          /></IconButton>
        </div>
      </td>
    </tr>
  </DataTable>
</template>
