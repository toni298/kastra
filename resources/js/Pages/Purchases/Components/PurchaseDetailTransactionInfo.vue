<script setup>
import { CalendarDays, FileText, UserRound } from 'lucide-vue-next'
import DataPanel from '@/Components/UI/DataPanel.vue'
import { formatCurrency } from '@/Utils/helpers'

const props = defineProps({
  transaction: { type: Object, required: true },
})

const fields = [
  { label: 'Nomor Dokumen', key: 'number', icon: FileText },
  { label: 'Jenis Pembelian', key: 'type', icon: FileText },
  { label: 'Tanggal Transaksi', key: 'date', icon: CalendarDays },
  { label: 'Dibuat Oleh', key: 'createdBy', icon: UserRound },
]
</script>

<template>
  <DataPanel>
    <header class="border-b border-slate-100 px-5 py-4 dark:border-[#29476b]">
      <div class="flex items-center gap-3">
        <span
          class="grid size-9 place-items-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
        >
          <FileText :size="18" />
        </span>
        <div>
          <h3 class="font-semibold text-slate-950 dark:text-white">Informasi Transaksi</h3>
          <p class="mt-0.5 text-xs text-slate-500">Ringkasan dokumen dan pembayaran.</p>
        </div>
      </div>
    </header>
    <dl class="grid gap-px bg-slate-100 dark:bg-[#29476b] sm:grid-cols-2">
      <div
        v-for="field in fields"
        :key="field.key"
        class="flex gap-3 bg-white px-5 py-4 dark:bg-[#102542]"
      >
        <component :is="field.icon" :size="16" class="mt-0.5 shrink-0 text-slate-400" />
        <div class="min-w-0">
          <dt class="text-xs font-medium text-slate-400">{{ field.label }}</dt>
          <dd class="mt-1 break-words text-sm font-semibold text-slate-900 dark:text-white">
            {{ props.transaction[field.key] }}
          </dd>
        </div>
      </div>
    </dl>
    <dl class="grid gap-3 border-t border-slate-100 p-5 dark:border-[#29476b] sm:grid-cols-3">
      <div class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
        <dt class="text-xs font-medium text-slate-400">Total</dt>
        <dd class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">
          {{ formatCurrency(transaction.totalValue) }}
        </dd>
      </div>
      <div class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
        <dt class="text-xs font-medium text-slate-400">Sudah Dibayar</dt>
        <dd class="mt-2 font-semibold text-emerald-700 dark:text-emerald-300">
          {{ formatCurrency(transaction.paidValue) }}
        </dd>
      </div>
      <div class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
        <dt class="text-xs font-medium text-slate-400">Sisa Pembayaran</dt>
        <dd class="mt-2 font-semibold text-slate-950 dark:text-white">
          {{ formatCurrency(transaction.totalValue - transaction.paidValue) }}
        </dd>
      </div>
    </dl>
  </DataPanel>
</template>
