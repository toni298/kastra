<script setup>
import { computed } from 'vue'
import { PackageOpen } from 'lucide-vue-next'
import DataPanel from '@/Components/UI/DataPanel.vue'
import { formatCurrency } from '@/Utils/helpers'

const props = defineProps({
  items: { type: Array, default: () => [] },
  total: { type: Number, default: 0 },
})

const itemCount = computed(() => props.items.length)
</script>

<template>
  <DataPanel>
    <header class="flex items-center justify-between gap-3 px-4 py-3">
      <div class="flex items-center gap-3">
        <span
          class="grid size-8 place-items-center rounded-lg bg-violet-50 text-violet-700 dark:bg-violet-400/10 dark:text-violet-300"
        >
          <PackageOpen :size="17" />
        </span>
        <div>
          <h3 class="font-semibold text-slate-950 dark:text-white">Daftar Barang</h3>
          <p class="mt-0.5 text-xs text-slate-500">{{ itemCount }} item transaksi.</p>
        </div>
      </div>
    </header>

    <div
      v-if="items.length"
      class="overflow-x-auto border-t border-slate-100 dark:border-[#29476b]"
    >
      <table class="w-full min-w-[560px] text-left">
        <thead class="bg-slate-50 dark:bg-[#0a1b33]">
          <tr class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            <th class="px-4 py-2.5">Nama Produk</th>
            <th class="px-4 py-2.5 text-right">Qty</th>
            <th class="px-4 py-2.5 text-right">Harga Satuan</th>
            <th class="px-4 py-2.5 text-right">Subtotal</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-[#29476b]">
          <tr v-for="item in items" :key="`${item.name}-${item.unit}`">
            <td class="px-4 py-3">
              <p class="font-semibold leading-tight text-slate-900 dark:text-white">
                {{ item.name }}
              </p>
            </td>
            <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900 dark:text-white">
              {{ item.qty }} {{ item.unit }}
            </td>
            <td class="px-4 py-3 text-right text-sm text-slate-600 dark:text-slate-300">
              {{ formatCurrency(item.price) }}
            </td>
            <td class="px-4 py-3 text-right text-sm font-semibold text-slate-950 dark:text-white">
              {{ formatCurrency(item.subtotal) }}
            </td>
          </tr>
        </tbody>
        <tfoot
          class="border-t border-slate-200 bg-slate-50 dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <tr>
            <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-slate-500">
              Total Pembelian
            </td>
            <td class="px-4 py-3 text-right text-base font-semibold text-slate-950 dark:text-white">
              {{ formatCurrency(total) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
    <div
      v-else
      class="grid min-h-40 place-items-center border-t border-slate-100 p-6 text-center dark:border-[#29476b]"
    >
      <div>
        <PackageOpen class="mx-auto size-9 text-slate-300" />
        <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">Tidak ada data</p>
        <p class="mt-1 text-xs text-slate-500">Barang pembelian belum tersedia.</p>
      </div>
    </div>
  </DataPanel>
</template>
