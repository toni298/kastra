<script setup>
import { ref } from 'vue'
import { Download, Eye, Search } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import { journals } from '../Data/accountingData'
const emit = defineEmits(['detail'])
const search = ref('')
</script>
<template>
  <article
    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
  >
    <header
      class="flex flex-col justify-between gap-4 border-b border-slate-100 p-5 dark:border-[#29476b] sm:flex-row sm:items-center"
    >
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Jurnal Pembukuan</h2>
        <p class="mt-1 text-sm text-slate-500">Gunakan halaman ini untuk audit dan pemeriksaan.</p>
      </div>
      <Button variant="secondary" size="sm"><Download :size="16" class="mr-2" />Export</Button>
    </header>
    <div
      class="flex flex-wrap gap-3 border-b border-slate-100 bg-slate-50/50 p-4 dark:border-[#29476b] dark:bg-[#0a1b33]/60"
    >
      <label class="relative min-w-[220px] flex-1"
        ><Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" /><input
          v-model="search"
          class="w-full rounded-xl border-slate-200 py-2 pl-9 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          placeholder="Cari jurnal atau referensi..." /></label
      ><select
        class="rounded-xl border-slate-200 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
      >
        <option>Semua Modul</option>
        <option>Penjualan</option>
        <option>Pembelian</option>
        <option>Kas & Bank</option></select
      ><select
        class="rounded-xl border-slate-200 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
      >
        <option>Semua Status</option>
        <option>Diposting</option>
        <option>Draft</option></select
      ><input
        class="rounded-xl border-slate-200 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
        placeholder="Tanggal"
      />
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-left">
        <thead
          class="bg-slate-50 text-[12px] font-semibold uppercase tracking-wide text-slate-400 dark:bg-[#0a1b33]"
        >
          <tr>
            <th
              v-for="col in [
                'Nomor Jurnal',
                'Tanggal',
                'Modul',
                'Referensi',
                'Status',
                'Total Debit',
                'Total Kredit',
                'Aksi',
              ]"
              :key="col"
              class="whitespace-nowrap px-5 py-3.5"
            >
              {{ col }}
            </th>
          </tr>
        </thead>
        <tbody
          class="divide-y divide-slate-100 text-sm text-slate-700 dark:divide-[#29476b] dark:text-slate-200"
        >
          <tr
            v-for="item in journals"
            :key="item.number"
            class="hover:bg-slate-50/70 dark:hover:bg-[#163354]/60"
          >
            <td class="px-5 py-4 font-medium dark:text-white">{{ item.number }}</td>
            <td class="px-5 py-4">{{ item.date }}</td>
            <td class="px-5 py-4">{{ item.module }}</td>
            <td class="px-5 py-4">{{ item.reference }}</td>
            <td class="px-5 py-4">
              <Badge :variant="item.variant">{{ item.status }}</Badge>
            </td>
            <td class="px-5 py-4 font-semibold">{{ item.debit }}</td>
            <td class="px-5 py-4 font-semibold">{{ item.credit }}</td>
            <td class="px-5 py-4">
              <button
                class="rounded-lg p-2 hover:bg-slate-100 dark:hover:bg-[#163354]"
                @click="emit('detail', item)"
              >
                <Eye :size="17" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </article>
</template>
