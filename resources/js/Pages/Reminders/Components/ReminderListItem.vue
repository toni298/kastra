<script setup>
import { computed } from 'vue'
import { Check, Pencil, Trash2 } from 'lucide-vue-next'
import IconButton from '@/Components/UI/IconButton.vue'
import { useDayReminderHelpers } from '../Composables/useDayReminderHelpers'

const props = defineProps({
  reminder: { type: Object, required: true },
})

const emit = defineEmits(['edit', 'delete', 'complete'])

const { canEditDelete, canComplete, getIcon, getIconColor, getStatusBadge, formatCurrency } =
  useDayReminderHelpers(props, emit)

const title = computed(() => props.reminder.title || props.reminder.name || 'Reminder')
const notes = computed(() => props.reminder.notes || 'Pengingat pembayaran')
const statusBadge = computed(() => getStatusBadge(props.reminder))
const isIncoming = computed(() => props.reminder.type_transcation === 'in')
const amountPrefix = computed(() => (isIncoming.value ? '+' : '−'))
const amountClass = computed(() =>
  isIncoming.value ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'
)

const transactionChipClass = computed(() =>
  props.reminder.type_transcation === 'in'
    ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
    : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400'
)

const statusChipClass = computed(
  () =>
    ({
      success: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
      error: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
      warning: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
      info: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
      secondary: 'bg-slate-100 text-slate-500 dark:bg-white/5 dark:text-slate-400',
    })[statusBadge.value.variant] ??
    'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'
)
</script>

<template>
  <div
    class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-5 py-5 dark:border-[#29476b] dark:bg-[#0d2138]"
  >
    <!-- Left: icon + info -->
    <div class="flex min-w-0 flex-1 items-center gap-4">
      <span
        class="grid size-14 shrink-0 place-items-center rounded-2xl"
        :class="getIconColor(reminder)"
      >
        <component :is="getIcon(reminder)" :size="26" :stroke-width="1.75" />
      </span>

      <div class="min-w-0">
        <p class="truncate text-base font-bold text-slate-900 dark:text-white">{{ title }}</p>
        <p class="mt-0.5 truncate text-sm text-slate-400 dark:text-slate-500">{{ notes }}</p>
        <div class="mt-2.5 flex flex-wrap items-center gap-2">
          <span
            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
            :class="transactionChipClass"
          >
            {{ reminder.type_transcation === 'in' ? 'Penerimaan' : 'Pengeluaran' }}
          </span>
          <span
            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
            :class="statusChipClass"
          >
            {{ statusBadge.label }}
          </span>
        </div>
      </div>
    </div>

    <!-- Right: amount + actions -->
    <div class="flex shrink-0 flex-col items-end gap-3">
      <p
        class="whitespace-nowrap text-xl font-bold tabular-nums tracking-tight"
        :class="amountClass"
      >
        {{ amountPrefix }} {{ formatCurrency(reminder.amount) }}
      </p>

      <div v-if="!reminder.readonly" class="flex items-center gap-2">
        <IconButton
          v-if="canComplete(reminder)"
          label="Tandai selesai"
          class="!size-11 !rounded-xl bg-emerald-50 text-emerald-600 hover:!bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:!bg-emerald-500/20"
          @click="emit('complete', reminder)"
        >
          <Check :size="20" :stroke-width="2" />
        </IconButton>
        <IconButton
          v-if="canEditDelete(reminder)"
          label="Edit"
          class="!size-11 !rounded-xl bg-slate-100 text-slate-500 hover:!bg-slate-200 dark:bg-white/5 dark:text-slate-300 dark:hover:!bg-white/10"
          @click="emit('edit', reminder)"
        >
          <Pencil :size="18" :stroke-width="1.75" />
        </IconButton>
        <IconButton
          v-if="canEditDelete(reminder)"
          label="Hapus"
          class="!size-11 !rounded-xl bg-red-50 text-red-600 hover:!bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:!bg-red-500/20"
          @click="emit('delete', reminder)"
        >
          <Trash2 :size="18" :stroke-width="1.75" />
        </IconButton>
      </div>
    </div>
  </div>
</template>
