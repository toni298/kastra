<script setup>
import { ArrowDownToLine, ArrowUpFromLine, Landmark, ReceiptText } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'

defineProps({
  filtered: { type: Boolean, default: false },
})
const emit = defineEmits(['action', 'reset'])
</script>

<template>
  <div class="grid min-h-80 place-items-center px-5 py-12 text-center sm:px-8">
    <div class="max-w-md">
      <div class="relative mx-auto h-24 w-28" aria-hidden="true">
        <span
          class="absolute bottom-0 left-2 grid size-16 place-items-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
        >
          <Landmark :size="30" />
        </span>
        <span
          class="absolute right-1 top-0 grid size-14 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
        >
          <ReceiptText :size="25" />
        </span>
      </div>
      <h3 class="mt-5 text-lg font-semibold text-slate-950 dark:text-white">
        {{ filtered ? 'Transaksi tidak ditemukan' : 'Belum ada transaksi kas' }}
      </h3>
      <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
        {{
          filtered
            ? 'Ubah kata pencarian atau filter untuk melihat transaksi lain.'
            : 'Semua pemasukan dan pengeluaran kas akan muncul di sini.'
        }}
      </p>
      <div v-if="filtered" class="mt-5">
        <Button variant="secondary" size="sm" @click="emit('reset')">Reset Pencarian</Button>
      </div>
      <div v-else class="mt-5 flex flex-wrap justify-center gap-2">
        <Button size="sm" @click="emit('action', 'in')">
          <ArrowDownToLine :size="16" class="mr-2" />Kas Masuk
        </Button>
        <Button variant="secondary" size="sm" @click="emit('action', 'out')">
          <ArrowUpFromLine :size="16" class="mr-2" />Kas Keluar
        </Button>
      </div>
    </div>
  </div>
</template>
