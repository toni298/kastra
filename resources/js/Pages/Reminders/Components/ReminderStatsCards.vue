<script setup>
import { Bell, Calendar, CheckCircle2, Clock } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps({
  stats: { type: Object, required: true },
})

const cards = computed(() => [
  {
    label: 'Total Reminder',
    value: props.stats.total ?? 0,
    description: 'Aktif dan terjadwal',
    icon: Bell,
    iconClass: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
  },
  {
    label: 'Jatuh Tempo Hari Ini',
    value: props.stats.due_today ?? 0,
    description: 'Perlu segera dibayar',
    icon: Calendar,
    iconClass: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
  },
  {
    label: 'Akan Jatuh Tempo',
    value: props.stats.due_soon ?? 0,
    description: 'Dalam 7 hari ke depan',
    icon: Clock,
    iconClass: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
  },
  {
    label: 'Selesai Bulan Ini',
    value: props.stats.completed_this_month ?? 0,
    description: new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(new Date()),
    icon: CheckCircle2,
    iconClass: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
  },
])
</script>

<template>
  <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div
      v-for="card in cards"
      :key="card.label"
      class="rounded-xl border border-slate-200 bg-white p-4 dark:border-[#29476b] dark:bg-[#0a1b33]"
    >
      <div class="flex items-start gap-3">
        <span class="grid size-10 shrink-0 place-items-center rounded-xl" :class="card.iconClass">
          <component :is="card.icon" :size="20" />
        </span>
        <div class="min-w-0">
          <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ card.value }}</p>
          <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ card.label }}</p>
          <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ card.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
