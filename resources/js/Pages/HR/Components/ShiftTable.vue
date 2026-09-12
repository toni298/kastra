<script setup>
import { Eye, Pencil, Trash2, Users } from 'lucide-vue-next'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import Spinner from '@/Components/UI/Spinner.vue'

const props = defineProps({
  items: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['detail', 'edit', 'delete'])
const columns = [
  { key: 'name', label: 'Nama Shift' },
  { key: 'hours', label: 'Jam Kerja' },
  { key: 'grace_period_minutes', label: 'Toleransi' },
  { key: 'employees_count', label: 'Karyawan Assigned' },
  { key: 'actions', label: 'Aksi' },
]
</script>

<template>
  <DataPanel>
    <div class="relative">
      <div v-if="loading" class="pointer-events-none absolute inset-0 z-10 flex min-h-40 items-center justify-center bg-white/70 dark:bg-[#102542]/70">
        <Spinner label="Memuat data shift..." />
      </div>
      <DataTable
        :items="props.items"
        :columns="columns"
        :loading="loading"
        :show-pagination="false"
        empty-icon="clock-3"
        empty-title="Belum ada shift"
        empty-message="Tambahkan shift untuk mulai mengatur jadwal karyawan."
      >
      <template #cell-name="{ item }">
        <span class="font-semibold text-slate-900 dark:text-white">{{ item.name }}</span>
      </template>
      <template #cell-hours="{ item }">
        <span class="text-slate-700 dark:text-slate-200">
          {{ item.start_time?.slice(0, 5) }} - {{ item.end_time?.slice(0, 5) }}
        </span>
      </template>
      <template #cell-grace_period_minutes="{ item }">
        {{ item.grace_period_minutes }} menit
      </template>
      <template #cell-employees_count="{ item }">
        <span class="inline-flex items-center gap-1.5 text-slate-700 dark:text-slate-200">
          <Users :size="15" class="text-emerald-600" />{{ item.employees_count ?? 0 }} orang
        </span>
      </template>
      <template #cell-actions="{ item }">
        <div class="flex items-center gap-1">
          <button
            type="button"
            class="rounded-lg p-2 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700"
            title="Detail karyawan"
            @click="emit('detail', item)"
          >
            <Eye :size="16" />
          </button>
          <button
            type="button"
            class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-700"
            title="Edit shift"
            @click="emit('edit', item)"
          >
            <Pencil :size="16" />
          </button>
          <button
            type="button"
            class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-700"
            title="Hapus shift"
            @click="emit('delete', item)"
          >
            <Trash2 :size="16" />
          </button>
        </div>
      </template>
      </DataTable>
    </div>
  </DataPanel>
</template>
