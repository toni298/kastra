<script setup>
import { Bot, Printer } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
const props = defineProps({ journal: { type: Object, required: true } })
const emit = defineEmits(['close'])
const metadata = [
  ['Nomor Jurnal', props.journal.number],
  ['Referensi', props.journal.reference],
  ['Modul Asal', props.journal.module],
  ['Tanggal', props.journal.date],
  ['Status', props.journal.status],
]
const lines = [
  {
    account: 'Kas / Bank',
    description: 'Penerimaan pembayaran transaksi',
    debit: 'Rp 12.500.000',
    credit: '-',
  },
  {
    account: 'Pendapatan Penjualan',
    description: 'Pendapatan dari transaksi',
    debit: '-',
    credit: 'Rp 11.261.261',
  },
  {
    account: 'PPN Keluaran',
    description: 'Pajak pertambahan nilai',
    debit: '-',
    credit: 'Rp 1.238.739',
  },
]
</script>
<template>
  <Modal
    :model-value="true"
    title="Detail Jurnal"
    description="Rincian pembukuan yang dibuat dari aktivitas bisnis."
    size="full"
    @update:model-value="emit('close')"
    ><section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
      <div
        v-for="pair in metadata"
        :key="pair[0]"
        class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]"
      >
        <p class="text-xs font-medium text-slate-400">{{ pair[0] }}</p>
        <p class="mt-1 text-sm font-semibold dark:text-white">{{ pair[1] }}</p>
      </div>
    </section>
    <div
      v-if="journal.automatic"
      class="mt-5 flex items-center gap-2 rounded-xl bg-emerald-50 p-3 text-sm font-medium text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
    >
      <Bot :size="18" />Dibuat Otomatis oleh Kastra
    </div>
    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 dark:border-[#29476b]">
      <table class="min-w-full">
        <thead
          class="bg-slate-50 text-left text-[12px] font-semibold uppercase text-slate-400 dark:bg-[#0a1b33]"
        >
          <tr>
            <th class="px-6 py-4">Akun</th>
            <th class="px-6 py-4">Deskripsi</th>
            <th class="px-6 py-4 text-right">Debit</th>
            <th class="px-6 py-4 text-right">Kredit</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm dark:divide-[#29476b]">
          <tr v-for="line in lines" :key="line.account">
            <td class="px-6 py-4 font-medium dark:text-white">{{ line.account }}</td>
            <td class="px-6 py-4 text-slate-500">{{ line.description }}</td>
            <td class="px-6 py-4 text-right font-semibold">{{ line.debit }}</td>
            <td class="px-6 py-4 text-right font-semibold">{{ line.credit }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <template #footer
      ><Button variant="secondary"><Printer :size="16" class="mr-2" />Cetak</Button
      ><Button @click="emit('close')">Tutup</Button></template
    ></Modal
  >
</template>
