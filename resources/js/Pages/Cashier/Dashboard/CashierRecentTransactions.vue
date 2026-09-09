<script setup>
import { Link } from '@inertiajs/vue3'
import { ArrowRight, FileText } from 'lucide-vue-next'
const props = defineProps({ items: { type: Array, default: () => [] } })
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
</script>
<template>
  <section
    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
  >
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-base font-bold text-slate-950 dark:text-white">Transaksi Terbaru</h2>
        <p class="mt-1 text-xs font-medium text-slate-500">Aktivitas penjualan terakhir.</p>
      </div>
      <FileText :size="19" class="text-blue-500" />
    </div>
    <div class="mt-4 divide-y divide-slate-100 dark:divide-[#29476b]">
      <div
        v-for="item in items"
        :key="item.id"
        class="flex items-center gap-3 rounded-xl px-2 py-3 transition hover:bg-slate-50 dark:hover:bg-[#0d2039]"
      >
        <span
          class="grid size-8 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300"
          ><FileText :size="15"
        /></span>
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ item.number }}</p>
          <p class="mt-1 truncate text-[11px] font-medium text-slate-500">
            {{ item.customer }} · {{ item.time }}
          </p>
        </div>
        <div class="text-right">
          <p class="text-sm font-bold text-slate-900 dark:text-white">{{ money(item.total) }}</p>
          <span
            :class="[
              'mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold',
              item.payment_status === 'paid'
                ? 'bg-emerald-50 text-emerald-700'
                : 'bg-amber-50 text-amber-700',
            ]"
            >{{ item.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas' }}</span
          >
        </div>
      </div>
      <p v-if="!items.length" class="py-8 text-center text-sm text-slate-500">
        Belum ada transaksi hari ini.
      </p>
    </div>
    <Link
      :href="route('cashier.sales.tab', { salesTab: 'transactions' })"
      class="mt-3 flex items-center justify-center gap-2 text-xs font-bold text-emerald-600 hover:text-emerald-700"
      >Lihat Semua Transaksi <ArrowRight :size="14"
    /></Link>
  </section>
</template>
