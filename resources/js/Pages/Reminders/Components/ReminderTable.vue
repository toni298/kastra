<script setup>
import { router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
  Bell,
  Calendar,
  CheckCircle2,
  Clock,
  Eye,
  FilePenLine,
  MoreVertical,
  Trash2,
  Wifi,
  Zap,
  Building2,
  Droplets,
  CreditCard,
  Receipt,
  CircleDollarSign,
} from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import { useAccessControl } from '@/Composables/useAccessControl'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  loading: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
  search: { type: String, default: '' },
})

const emit = defineEmits(['request', 'load-more', 'action'])
const { can } = useAccessControl()

const openMenu = ref(null)

const rows = computed(() => props.items?.data ?? [])
const pagination = computed(() => ({
  ...props.items,
  per_page: props.items?.meta?.per_page ?? 10,
  next_page_url: props.items?.links?.next ?? null,
}))

const request = (overrides = {}) =>
  emit('request', { url: route('reminders.index'), data: overrides })

const formatDate = (date) => {
  if (!date) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(new Date(date))
}

const formatCurrency = (amount) => {
  if (!amount) return '-'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

const getStatusBadge = (reminder) => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const dueDate = new Date(reminder.due_date)
  dueDate.setHours(0, 0, 0, 0)

  if (reminder.status === 'completed') {
    return { variant: 'success', label: 'Selesai' }
  }
  if (reminder.status === 'cancelled') {
    return { variant: 'secondary', label: 'Dibatalkan' }
  }
  if (dueDate.getTime() === today.getTime()) {
    return { variant: 'error', label: 'Jatuh tempo hari ini' }
  }
  if (dueDate < today) {
    return { variant: 'error', label: 'Terlambat' }
  }
  if (dueDate <= new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)) {
    return { variant: 'warning', label: 'Akan jatuh tempo' }
  }
  return { variant: 'info', label: 'Terjadwal' }
}

const getIcon = (reminder) => {
  const name = reminder.name?.toLowerCase() ?? ''
  if (name.includes('wifi') || name.includes('internet')) return Wifi
  if (name.includes('listrik') || name.includes('pln')) return Zap
  if (name.includes('sewa') || name.includes('gudang') || name.includes('kantor')) return Building2
  if (name.includes('pdam') || name.includes('air')) return Droplets
  if (name.includes('kartu') || name.includes('kredit')) return CreditCard
  if (name.includes('pajak') || name.includes('tax')) return Receipt
  if (reminder.type_transcation === 'in') return CircleDollarSign
  return Bell
}

const getIconColor = (reminder) => {
  const status = getStatusBadge(reminder)
  if (status.variant === 'error')
    return 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400'
  if (status.variant === 'warning')
    return 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'
  if (status.variant === 'success')
    return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
  return 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'
}

const isOverdue = (reminder) => {
  if (reminder.status !== 'pending') return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const dueDate = new Date(reminder.due_date)
  dueDate.setHours(0, 0, 0, 0)
  return dueDate < today
}

const isPastDue = (reminder) => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const dueDate = new Date(reminder.due_date)
  dueDate.setHours(0, 0, 0, 0)
  return dueDate < today
}

const canEditDelete = (reminder) => {
  // Cannot edit/delete if past due
  if (isPastDue(reminder)) return false
  return true
}

const groupedRows = computed(() => {
  const groups = new Map()
  rows.value.forEach((row) => {
    const date = row.due_date
    if (!groups.has(date)) groups.set(date, [])
    groups.get(date).push(row)
  })
  return [...groups.entries()].map(([date, items]) => ({ date, items }))
})

const labelDate = (date) => {
  const value = new Date(`${date}T00:00:00`)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const tomorrow = new Date(today)
  tomorrow.setDate(today.getDate() + 1)

  if (value.getTime() === today.getTime()) return 'Hari Ini'
  if (value.getTime() === tomorrow.getTime()) return 'Besok'
  return formatDate(date)
}

const completeReminder = (reminder) => {
  router.post(
    route('reminders.complete', reminder.id),
    {},
    {
      preserveScroll: true,
    }
  )
}
</script>

<template>
  <DataPanel>
    <DataTable
      :items="rows"
      :pagination="pagination"
      :loading="loading"
      :loading-more="loadingMore"
      infinite
      sticky-toolbar
      searchable
      :search="search"
      search-placeholder="Cari reminder..."
      empty-icon="bell"
      empty-title="Belum ada reminder"
      empty-message="Reminder akan tampil di sini."
      @filter="({ search: value }) => request({ search: value })"
      @load-more="emit('load-more', $event)"
      @per-page-change="(perPage) => request({ per_page: perPage })"
    >
      <template #thead>
        <tr>
          <th>Reminder</th>
          <th>Jatuh Tempo</th>
          <th class="text-right">Nominal</th>
          <th>Status</th>
          <th class="w-14 text-right">Aksi</th>
        </tr>
      </template>

      <template v-for="group in groupedRows" :key="group.date">
        <tr class="bg-slate-50/80 dark:bg-[#0a1b33]">
          <td
            colspan="5"
            class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500"
          >
            {{ labelDate(group.date) }}
            <span class="ml-2 font-normal normal-case">{{ group.items.length }} reminder</span>
          </td>
        </tr>
        <tr
          v-for="row in group.items"
          :key="row.id"
          class="cursor-pointer transition hover:bg-slate-50 dark:hover:bg-[#163354]"
          :class="{ 'bg-red-50/50 dark:bg-red-500/5': isOverdue(row) }"
          @click="emit('action', { action: 'detail', item: row })"
        >
          <td class="px-5 py-4">
            <div class="flex items-center gap-3">
              <span
                class="grid size-10 shrink-0 place-items-center rounded-xl"
                :class="getIconColor(row)"
              >
                <component :is="getIcon(row)" :size="20" />
              </span>
              <div class="min-w-0">
                <p class="truncate font-medium text-slate-900 dark:text-white">{{ row.name }}</p>
                <p class="truncate text-sm text-slate-500">
                  {{ row.branch?.name ?? 'Operasional' }}
                </p>
              </div>
            </div>
          </td>
          <td class="px-5 py-4 text-sm">
            <div class="flex items-center gap-1.5">
              <Calendar :size="14" class="text-slate-400" />
              <span
                :class="
                  isOverdue(row)
                    ? 'font-medium text-red-600 dark:text-red-400'
                    : 'text-slate-600 dark:text-slate-300'
                "
              >
                {{ formatDate(row.due_date) }}
              </span>
            </div>
          </td>
          <td class="px-5 py-4 text-right">
            <span
              class="font-semibold"
              :class="row.type_transcation === 'in' ? 'text-emerald-600' : 'text-red-600'"
            >
              {{ row.type_transcation === 'in' ? '+' : '-' }}{{ formatCurrency(row.amount) }}
            </span>
          </td>
          <td class="px-5 py-4">
            <Badge :variant="getStatusBadge(row).variant">
              {{ getStatusBadge(row).label }}
            </Badge>
          </td>
          <td class="relative px-5 py-4 text-right" @click.stop>
            <button
              class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 dark:hover:bg-[#163354]"
              aria-label="Aksi reminder"
              @click="openMenu = openMenu === row.id ? null : row.id"
            >
              <MoreVertical :size="18" />
            </button>
            <div
              v-if="openMenu === row.id"
              class="absolute right-5 top-11 z-20 w-40 rounded-xl border bg-white py-1 shadow-xl dark:border-[#29476b] dark:bg-[#102542]"
            >
              <button
                class="flex w-full items-center gap-2 px-3 py-2 text-sm transition hover:bg-slate-50 dark:hover:bg-[#163354]"
                @click="(emit('action', { action: 'detail', item: row }), (openMenu = null))"
              >
                <Eye :size="15" />Detail
              </button>
              <button
                v-if="can('reminders.edit') && row.status === 'pending'"
                class="flex w-full items-center gap-2 px-3 py-2 text-sm transition hover:bg-slate-50 dark:hover:bg-[#163354]"
                @click="(completeReminder(row), (openMenu = null))"
              >
                <CheckCircle2 :size="15" />Tandai Selesai
              </button>
              <div
                v-if="isPastDue(row)"
                class="border-t border-slate-100 px-3 py-2 dark:border-[#29476b]"
              >
                <span class="text-xs text-slate-400">Sudah terlewati</span>
              </div>
            </div>
          </td>
        </tr>
      </template>
    </DataTable>
  </DataPanel>
</template>
