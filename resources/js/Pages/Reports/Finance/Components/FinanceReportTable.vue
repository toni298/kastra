<script setup>
import { computed, nextTick, ref } from 'vue'
import { Eye, Filter } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import FinanceReportFilters from './FinanceReportFilters.vue'
import { formatCurrency } from '@/Utils/helpers'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ accounts: [] }) },
  loading: { type: Boolean, default: false },
  exporting: { type: Boolean, default: false },
})
const emit = defineEmits(['detail', 'filter', 'apply', 'load-options', 'export', 'navigate'])
const rows = computed(() => props.items?.data ?? [])
const filterOpen = ref(false)
const filterButton = ref(null)
const filterPanelStyle = ref({})
const pagination = computed(() => ({
  ...props.items,
  per_page: props.items?.meta?.per_page ?? 10,
}))
const typeLabel = (value) => ({ in: 'Pemasukan', out: 'Pengeluaran', none: 'Informasi' })[value] ?? value ?? '-'
const typeVariant = (value) => value === 'in' ? 'success' : value === 'out' ? 'error' : 'info'
const toggleFilter = async () => {
  filterOpen.value = !filterOpen.value
  if (!filterOpen.value) return
  emit('load-options')
  await nextTick()
  const rect = filterButton.value?.getBoundingClientRect()
  if (!rect) return
  const width = Math.min(600, window.innerWidth - 24)
  filterPanelStyle.value = {
    left: `${Math.max(12, Math.min(rect.left, window.innerWidth - width - 12))}px`,
    top: `${Math.min(rect.bottom + 8, window.innerHeight - 24)}px`,
  }
}
</script>

<template>
  <DataPanel>
    <DataTable
      :items="rows"
      :pagination="pagination"
      :loading="loading"
      sticky-toolbar
      searchable
      :search="filters.search ?? ''"
      search-placeholder="Cari referensi, deskripsi, atau catatan transaksi..."
      empty-icon="wallet-cards"
      empty-title="Belum ada transaksi"
      empty-message="Transaksi kas dan bank akan tampil di sini."
      @navigate="emit('navigate', $event)"
      @filter="emit('filter', $event.search)"
    >
      <template #filters>
        <div ref="filterButton">
          <Button variant="secondary" size="sm" @click="toggleFilter">
            <Filter :size="16" class="mr-2" />Filter
          </Button>
        </div>
        <FinanceReportFilters
          v-if="filterOpen"
          :filters="filters"
          :options="options"
          :panel-style="filterPanelStyle"
          :trigger-element="filterButton"
          :exporting="exporting"
          @close="filterOpen = false"
          @apply="(value) => { emit('apply', value); filterOpen = false }"
          @export="emit('export', $event)"
        />
      </template>
      <template #thead>
        <tr>
          <th>Tanggal Transaksi</th>
          <th>No. Referensi</th>
          <th>Akun Kas / Bank</th>
          <th>Kategori</th>
          <th>Tipe</th>
          <th class="text-right">Nominal</th>
          <th>Keterangan</th>
          <th class="text-right">Aksi</th>
        </tr>
      </template>
      <tr v-for="row in rows" :key="row.id" class="text-sm">
        <td class="whitespace-nowrap px-5 py-4 dark:text-slate-200">{{ row.date ?? '-' }}</td>
        <td class="px-5 py-4 font-mono text-xs font-semibold dark:text-white">{{ row.reference ?? '-' }}</td>
        <td class="px-5 py-4 dark:text-slate-200">{{ row.account ?? '-' }}</td>
        <td class="px-5 py-4 dark:text-slate-200">{{ row.category ?? '-' }}</td>
        <td class="px-5 py-4">
          <Badge :variant="typeVariant(row.type)">{{ typeLabel(row.type) }}</Badge>
        </td>
        <td :class="['px-5 py-4 text-right font-semibold tabular-nums', row.amount >= 0 ? 'text-emerald-600' : 'text-red-600']">
          {{ row.amount >= 0 ? '+' : '-' }}{{ formatCurrency(Math.abs(row.amount ?? 0)) }}
        </td>
        <td class="max-w-[220px] truncate px-5 py-4 dark:text-slate-200">{{ row.note ?? row.description ?? '-' }}</td>
        <td class="px-5 py-4 text-right">
          <button type="button" class="text-emerald-600 hover:text-emerald-700" @click="emit('detail', row)">
            <Eye :size="15" />
          </button>
        </td>
      </tr>
    </DataTable>
  </DataPanel>
</template>
