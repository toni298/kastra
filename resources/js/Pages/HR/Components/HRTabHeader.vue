<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  active: { type: String, default: 'employees' },
})
const tabs = [
  { id: 'summary', label: 'Ringkasan', route: 'hr.summary.index' },
  { id: 'employees', label: 'Data Karyawan', route: 'hr.employees.index' },
  { id: 'attendance', label: 'Absensi & Shift', route: 'hr.attendance.index' },
  { id: 'commission', label: 'Komisi & Lembur', route: 'hr.commission.index' },
  { id: 'payroll', label: 'Penggajian', route: 'hr.payroll.index' },
]
</script>

<template>
  <nav class="border-b border-slate-200 dark:border-[#29476b]" aria-label="Navigasi SDM">
    <div class="flex flex-wrap gap-x-7 gap-y-3 px-1" role="tablist">
      <Link
        v-for="tab in tabs"
        :key="tab.id"
        :href="route(tab.route)"
        :class="[
          'relative pb-3 text-sm font-medium transition',
          props.active === tab.id
            ? 'text-emerald-700 dark:text-emerald-300'
            : 'text-slate-500 hover:text-slate-800 dark:text-slate-400',
        ]"
        :aria-current="props.active === tab.id ? 'page' : undefined"
      >
        {{ tab.label }}
        <span
          v-if="props.active === tab.id"
          class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-emerald-600"
        ></span>
      </Link>
    </div>
  </nav>
</template>
