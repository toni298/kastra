<script setup>
import { Hash, Landmark, ReceiptText, Settings2, Tags } from '@lucide/vue'
import Badge from '@/Components/UI/Badge.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import DataTableSortHeader from '@/Components/UI/DataTableSortHeader.vue'

defineProps({
  type: { type: String, required: true },
  items: { type: Object, required: true },
  search: { type: String, default: '' },
  sortKey: { type: String, required: true },
  sortDirection: { type: String, default: 'asc' },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['navigate', 'filter', 'per-page-change', 'sort'])
const icons = { coa: Landmark, taxes: ReceiptText, numbers: Settings2 }
const labels = { coa: 'Akun', taxes: 'Pajak', numbers: 'Generator' }
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
    search-placeholder="Cari kode atau nama..."
    empty-icon="inbox"
    empty-title="Tidak ada data"
    :empty-message="`Belum ada data ${labels[type].toLowerCase()} yang sesuai pencarian.`"
    @navigate="emit('navigate', $event)"
    @filter="emit('filter', $event)"
    @per-page-change="emit('per-page-change', $event)"
    @sort="emit('sort', $event)"
  >
    <template #thead="{ sort, sortKey: activeSort, sortDirection: direction }">
      <tr class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-300">
        <th>
          <DataTableSortHeader
            :label="labels[type]"
            :active="activeSort === (type === 'numbers' ? 'document_type' : 'nama')"
            :direction="direction"
            @sort="sort({ key: type === 'numbers' ? 'document_type' : 'nama', sortable: true })"
          >
            <template #icon><component :is="icons[type]" :size="14" /></template>
          </DataTableSortHeader>
        </th>
        <th>
          <DataTableSortHeader
            :label="type === 'numbers' ? 'Prefix' : 'Kode'"
            :active="activeSort === (type === 'numbers' ? 'prefix' : 'kode')"
            :direction="direction"
            @sort="sort({ key: type === 'numbers' ? 'prefix' : 'kode', sortable: true })"
          >
            <template #icon><Hash :size="14" /></template>
          </DataTableSortHeader>
        </th>
        <th>
          <span class="flex items-center gap-2"><Tags :size="14" />Detail</span>
        </th>
        <th class="text-right">
          <DataTableSortHeader
            label="Status"
            align="right"
            :active="activeSort === 'aktif'"
            :direction="direction"
            @sort="sort({ key: 'aktif', sortable: true })"
          />
        </th>
      </tr>
    </template>
    <tr v-for="item in items.data ?? []" :key="item.id">
      <td class="px-5 py-4">
        <div class="flex items-center gap-3">
          <span
            class="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
            ><component :is="icons[type]" :size="16"
          /></span>
          <div class="min-w-0">
            <p class="truncate font-medium text-slate-900 dark:text-white">
              {{ item.nama || item.document_type }}
            </p>
            <p class="truncate text-sm text-slate-500 dark:text-slate-300">
              {{ item.kode || item.prefix }}
            </p>
          </div>
        </div>
      </td>
      <td class="px-5 py-4 text-sm font-medium text-slate-600 dark:text-slate-300">
        {{ item.kode || item.prefix }}
      </td>
      <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
        <template v-if="type === 'coa'"
          >{{ item.kategori }} · {{ item.parent?.nama || 'Root' }}</template
        >
        <template v-else-if="type === 'taxes'"
          >{{ item.jenis }} · {{ item.persentase }}% · {{ item.mode }}</template
        >
        <template v-else>{{ item.format }} · reset {{ item.reset_period }}</template>
      </td>
      <td class="px-5 py-4 text-right">
        <Badge :variant="item.aktif === false ? 'error' : 'success'">{{
          item.aktif === false ? 'Nonaktif' : 'Aktif'
        }}</Badge>
      </td>
    </tr>
  </DataTable>
</template>
