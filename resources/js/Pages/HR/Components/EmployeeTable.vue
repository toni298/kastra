<script setup>
import { computed, ref } from 'vue'
import { Edit, Filter, KeyRound, Trash2 } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import EmployeeFilters from './EmployeeFilters.vue'
import { formatCurrency } from '@/Utils/helpers'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['filter', 'navigate', 'create', 'edit', 'reset-pin', 'delete'])
const rows = computed(() => props.items?.data ?? [])
const roleLabel = (role) =>
  ({ cashier: 'Kasir', supervisor: 'Supervisor', staff: 'Staff' })[role] ?? role
const statusVariant = (status) => (status === 'active' ? 'success' : 'warning')
const pagination = computed(() => ({ ...props.items, per_page: props.items?.meta?.per_page ?? 10 }))
const search = ref(props.filters.search ?? '')
const filterOpen = ref(false)
const updateSearch = ({ search: value }) => {
  search.value = value
  emit('filter', value)
}
const applyFilters = (filters) => {
  filterOpen.value = false
  emit('filter', filters)
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
      :search="search"
      search-placeholder="Cari nama, NIK, atau nomor telepon..."
      empty-icon="users"
      empty-title="Tidak ada karyawan"
      empty-message="Belum ada data karyawan pada filter yang dipilih."
      @navigate="emit('navigate', $event)"
      @filter="updateSearch"
    >
      <template #filters>
        <div class="relative inline-block">
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-[#29476b] dark:bg-[#102542] dark:text-slate-200 dark:hover:bg-[#163354]"
            @click="filterOpen = !filterOpen"
          >
            <Filter :size="16" class="mr-2" />Filter
          </button>
          <EmployeeFilters
            v-if="filterOpen"
            :filters="filters"
            @close="filterOpen = false"
            @apply="applyFilters"
          />
        </div>
      </template>
      <template #thead
        ><tr>
          <th>Identitas</th>
          <th>Kontak</th>
          <th>Role</th>
          <th>Gaji & Tunjangan</th>
          <th>Komisi</th>
          <th>Status</th>
          <th class="text-right">Aksi</th>
        </tr></template
      >
      <tr v-for="row in rows" :key="row.id" class="text-sm">
        <td class="px-5 py-4">
          <p class="font-semibold text-slate-900 dark:text-white">{{ row.name }}</p>
          <p class="mt-1 text-xs text-slate-500">NIK: {{ row.nik }}</p>
        </td>
        <td class="px-5 py-4 dark:text-slate-200">
          <p>{{ row.phone || '-' }}</p>
          <p class="mt-1 text-xs text-slate-500">{{ row.email || '-' }}</p>
        </td>
        <td class="px-5 py-4">
          <Badge variant="info">{{ roleLabel(row.role) }}</Badge>
        </td>
        <td class="px-5 py-4 dark:text-slate-200">
          <p class="font-medium">{{ formatCurrency(row.base_salary) }}</p>
          <p class="mt-1 text-xs text-slate-500">Tunjangan {{ formatCurrency(row.allowance) }}</p>
        </td>
        <td class="px-5 py-4 dark:text-slate-200">{{ row.commission_label }}</td>
        <td class="px-5 py-4">
          <Badge :variant="statusVariant(row.status)">{{ row.status_label }}</Badge>
        </td>
        <td class="px-5 py-4">
          <div class="flex justify-end gap-1">
            <button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-emerald-700"
              title="Edit"
              @click="emit('edit', row)"
            >
              <Edit :size="16" /></button
            ><button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-blue-700"
              title="Reset PIN"
              @click="emit('reset-pin', row)"
            >
              <KeyRound :size="16" /></button
            ><button
              type="button"
              class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-700"
              title="Hapus"
              @click="emit('delete', row)"
            >
              <Trash2 :size="16" />
            </button>
          </div>
        </td>
      </tr>
    </DataTable>
  </DataPanel>
</template>
