<script setup>
import { FilePenLine, Package, Trash2 } from '@lucide/vue'
import Badge from '@/Components/UI/Badge.vue'
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
    search-placeholder="Cari nama, SKU, atau barcode..."
    empty-icon="package"
    empty-title="Tidak ada produk"
    empty-message="Belum ada produk sesuai pencarian."
    @navigate="emit('navigate', $event)"
    @filter="emit('filter', $event)"
    @per-page-change="emit('per-page-change', $event)"
    @sort="emit('sort', $event)"
    ><template #thead="{ sort, sortKey: activeSort, sortDirection: direction }"
      ><tr class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-300">
        <th>
          <DataTableSortHeader
            label="Produk"
            :active="activeSort === 'name'"
            :direction="direction"
            @sort="sort({ key: 'name', sortable: true })"
          >
            <template #icon><Package :size="14" /></template>
          </DataTableSortHeader>
        </th>
        <th>Kategori</th>
        <th>Satuan</th>
        <th>
          <DataTableSortHeader
            label="Harga Jual"
            :active="activeSort === 'selling_price'"
            :direction="direction"
            @sort="sort({ key: 'selling_price', sortable: true })"
          />
        </th>
        <th class="text-right">
          <DataTableSortHeader
            label="Status"
            align="right"
            :active="activeSort === 'is_active'"
            :direction="direction"
            @sort="sort({ key: 'is_active', sortable: true })"
          />
        </th>
        <th class="text-right">Aksi</th>
      </tr></template
    >
    <tr
      v-for="product in items.data ?? []"
      :key="product.id"
      class="cursor-pointer"
      @click="emit('detail', product)"
    >
      <td class="px-5 py-4">
        <div class="flex items-center gap-3">
          <span
            class="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
            ><Package :size="16"
          /></span>
          <div>
            <p class="font-medium text-slate-900 dark:text-white">{{ product.name }}</p>
            <p class="text-sm text-slate-500 dark:text-slate-300">
              {{ product.sku }} <span v-if="product.barcode">&bull; {{ product.barcode }}</span>
            </p>
          </div>
        </div>
      </td>
      <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
        {{ product.category?.name || '-' }}
      </td>
      <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
        {{ product.unit?.name || '-' }}
      </td>
      <td class="px-5 py-4 text-sm font-medium text-slate-900 dark:text-white">
        Rp {{ new Intl.NumberFormat('id-ID').format(product.selling_price) }}
      </td>
      <td class="px-5 py-4 text-right">
        <Badge :variant="product.is_active ? 'success' : 'error'">{{
          product.is_active ? 'Aktif' : 'Nonaktif'
        }}</Badge>
      </td>
      <td class="px-5 py-4">
        <div class="flex justify-end gap-1">
          <IconButton
            v-if="canEdit"
            label="Edit produk"
            variant="info"
            @click.stop="emit('edit', product)"
            ><FilePenLine :size="17" /></IconButton
          ><IconButton
            v-if="canDelete"
            label="Hapus produk"
            variant="danger"
            @click.stop="emit('delete', product)"
            ><Trash2 :size="17"
          /></IconButton>
        </div>
      </td></tr
  ></DataTable>
</template>
