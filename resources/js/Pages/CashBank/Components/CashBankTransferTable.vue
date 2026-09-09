<script setup>
import { computed } from 'vue'
import { ArrowRight } from 'lucide-vue-next'
import DataTable from '@/Components/UI/DataTable.vue'
import DataTableSortHeader from '@/Components/UI/DataTableSortHeader.vue'

const props = defineProps({
  items: { type: Object, required: true },
  search: { type: String, default: '' },
  sortDirection: { type: String, default: 'desc' },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['filter', 'per-page-change', 'sort', 'navigate'])
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
const pagination = computed(() => ({
  ...props.items,
  per_page: props.items?.meta?.per_page ?? 10,
}))
const navigate = ({ cursor, direction }) => {
  if (!cursor) return

  const url = new URL(route('cash-bank.transfers.index'), window.location.origin)
  url.searchParams.set('cursor', cursor)

  emit('navigate', { url: url.toString() })
}
</script>
<template>
  <DataTable
    :items="items.data ?? []"
    :pagination="pagination"
    :loading="loading"
    sticky-toolbar
    searchable
    :search="search"
    :sort-direction="sortDirection"
    :per-page-options="[10, 20, 50, 100]"
    search-placeholder="Cari nomor atau referensi transfer..."
    empty-icon="arrow-right-left"
    empty-title="Belum ada transfer"
    empty-message="Transfer antar rekening akan tampil di sini."
    @filter="emit('filter', $event)"
    @per-page-change="emit('per-page-change', $event)"
    @sort="emit('sort', $event)"
    @navigate="navigate"
    ><template #thead="{ sort: sortTable, sortKey: activeSort, sortDirection: direction }"
      ><tr>
        <th>
          <DataTableSortHeader
            label="Nomor Transfer"
            :active="activeSort === 'transfer_number'"
            :direction="direction"
            @sort="sortTable({ key: 'transfer_number', sortable: true })"
          />
        </th>
        <th>Alur Dana</th>
        <th>
          <DataTableSortHeader
            label="Tanggal"
            :active="activeSort === 'transfer_date'"
            :direction="direction"
            @sort="sortTable({ key: 'transfer_date', sortable: true })"
          />
        </th>
        <th class="text-right">
          <DataTableSortHeader
            label="Nominal"
            align="right"
            :active="activeSort === 'amount'"
            :direction="direction"
            @sort="sortTable({ key: 'amount', sortable: true })"
          />
        </th></tr
    ></template>
    <tr v-for="item in items.data ?? []" :key="item.id" class="text-sm">
      <td class="px-5 py-4">
        <p class="font-mono font-semibold dark:text-white">{{ item.number }}</p>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ item.reference || '-' }}</p>
      </td>
      <td class="px-5 py-4">
        <p class="font-medium dark:text-white">{{ item.source }}</p>
        <p class="mt-1 inline-flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
          <ArrowRight :size="14" />{{ item.destination }}
        </p>
      </td>
      <td class="px-5 py-4 dark:text-white">{{ item.date }}</td>
      <td class="px-5 py-4 text-right font-semibold text-slate-900 dark:text-white">
        {{ money(item.amount) }}
      </td>
    </tr></DataTable
  >
</template>
