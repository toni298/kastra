<script setup>
import { reactive } from 'vue'
import { RotateCcw, SlidersHorizontal, X } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
  filters: { type: Object, required: true },
  transactions: { type: Array, default: () => [] },
})
const emit = defineEmits(['close', 'apply'])

const form = reactive({ ...props.filters })
const unique = (key) => [...new Set(props.transactions.map((transaction) => transaction[key]))]
const reset = () =>
  Object.assign(form, {
    status: '',
    supplier: '',
    type: '',
    payment: '',
    period: '',
    startDate: '',
    endDate: '',
  })
const apply = () => emit('apply', { ...form })
</script>

<template>
  <div
    class="absolute right-0 top-12 z-30 w-[min(92vw,440px)] rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
  >
    <div class="flex items-start justify-between gap-4">
      <div>
        <h3 class="font-semibold text-slate-950 dark:text-white">Filter Pembelian</h3>
        <p class="mt-1 text-xs text-slate-500">Persempit transaksi yang ingin ditampilkan.</p>
      </div>
      <button
        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-[#163354]"
        aria-label="Tutup filter"
        @click="emit('close')"
      >
        <X :size="18" />
      </button>
    </div>
    <div class="mt-5 grid gap-4 sm:grid-cols-2">
      <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
        Status
        <select
          v-model="form.status"
          class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <option value="">Semua Status</option>
          <option v-for="item in unique('status')" :key="item">{{ item }}</option>
        </select>
      </label>
      <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
        Supplier
        <select
          v-model="form.supplier"
          class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <option value="">Semua Supplier</option>
          <option v-for="item in unique('supplier')" :key="item">{{ item }}</option>
        </select>
      </label>
      <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
        Jenis Dokumen
        <select
          v-model="form.type"
          class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <option value="">Semua Dokumen</option>
          <option v-for="item in unique('type')" :key="item">{{ item }}</option>
        </select>
      </label>
      <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
        Status Pembayaran
        <select
          v-model="form.payment"
          class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <option value="">Semua Pembayaran</option>
          <option v-for="item in unique('payment')" :key="item">{{ item }}</option>
        </select>
      </label>
      <label class="text-sm font-medium text-slate-700 dark:text-slate-200 sm:col-span-2">
        Periode
        <select
          v-model="form.period"
          class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <option value="">Semua Periode</option>
          <option value="today">Hari Ini</option>
          <option value="yesterday">Kemarin</option>
          <option value="month">Bulan Ini</option>
          <option value="custom">Pilih Tanggal</option>
        </select>
      </label>
      <template v-if="form.period === 'custom'">
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
          Tanggal Mulai
          <input
            v-model="form.startDate"
            type="date"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
          />
        </label>
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
          Tanggal Akhir
          <input
            v-model="form.endDate"
            type="date"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
          />
        </label>
      </template>
    </div>
    <div class="mt-5 flex justify-between border-t border-slate-100 pt-4 dark:border-[#29476b]">
      <Button variant="ghost" size="sm" @click="reset">
        <RotateCcw :size="15" class="mr-2" />Reset
      </Button>
      <Button size="sm" @click="apply">
        <SlidersHorizontal :size="15" class="mr-2" />Terapkan
      </Button>
    </div>
  </div>
</template>
