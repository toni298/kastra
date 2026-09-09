<script setup>
import { Link } from '@inertiajs/vue3'
const props = defineProps({
  title: { type: String, default: '' },
  items: { type: Array, default: () => [] },
  type: { type: String, default: 'top' },
})
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
const methodLabel = (value) =>
  ({ cash: 'Tunai', transfer: 'Transfer', qris: 'QRIS', ewallet: 'E-Wallet' })[value] ?? value
const maxSold = () => Math.max(1, ...props.items.map((item) => item.sold ?? 0))
</script>
<template>
  <section
    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
  >
    <h2 class="text-base font-bold text-slate-950 dark:text-white">{{ title }}</h2>
    <p class="mt-1 text-xs font-medium text-slate-500">
      <span v-if="type === 'top'">Produk dengan penjualan tertinggi hari ini.</span>
      <span v-else-if="type === 'stock'">Produk yang perlu segera dipantau.</span>
      <span v-else-if="type === 'payment'">Total penerimaan berdasarkan metode.</span>
      <span v-else>Ringkasan transaksi yang membutuhkan perhatian.</span>
    </p>
    <div v-if="type === 'top'" class="mt-4 space-y-3">
      <div
        v-for="(item, index) in items"
        :key="item.id"
        class="flex items-center gap-3 rounded-xl px-2 py-2.5 transition hover:bg-slate-50 dark:hover:bg-[#0d2039]"
      >
        <span
          class="grid size-6 place-items-center rounded-full bg-emerald-50 text-[10px] font-bold text-emerald-700"
          >{{ index + 1 }}</span
        ><span
          class="min-w-0 flex-1 truncate text-sm font-bold text-slate-700 dark:text-slate-200"
          >{{ item.name }}</span
        >
        <div class="w-20">
          <div class="h-1.5 rounded-full bg-slate-100">
            <div
              class="h-1.5 rounded-full bg-emerald-500"
              :style="{ width: `${(item.sold / maxSold()) * 100}%` }"
            ></div>
          </div>
        </div>
        <span class="w-16 text-right text-[11px] font-bold text-slate-500"
          >{{ item.sold }} terjual</span
        >
      </div>
    </div>
    <div v-else-if="type === 'stock'" class="mt-4 space-y-3">
      <div
        v-for="item in items"
        :key="item.id"
        class="flex items-center gap-3 rounded-xl px-2 py-2.5 transition hover:bg-slate-50 dark:hover:bg-[#0d2039]"
      >
        <span class="size-2 rounded-full bg-rose-500"></span
        ><span
          class="min-w-0 flex-1 truncate text-sm font-bold text-slate-700 dark:text-slate-200"
          >{{ item.name }}</span
        ><span class="rounded-full bg-rose-50 px-2 py-1 text-[11px] font-bold text-rose-600"
          >{{ item.stock }} / {{ item.minimum }}</span
        >
      </div>
      <p v-if="!items.length" class="py-5 text-center text-sm text-slate-500">
        Stok aman hari ini.
      </p>
    </div>
    <div v-else-if="type === 'payment'" class="mt-4 space-y-3">
      <div
        v-for="item in items"
        :key="item.method"
        class="flex items-center justify-between rounded-xl px-2 py-2.5 text-sm transition hover:bg-slate-50 dark:hover:bg-[#0d2039]"
      >
        <span class="font-bold text-slate-700 dark:text-slate-200">{{
          methodLabel(item.method)
        }}</span
        ><span class="text-sm font-bold text-slate-900 dark:text-white">{{
          money(item.total)
        }}</span>
      </div>
      <p v-if="!items.length" class="py-5 text-center text-sm text-slate-500">
        Belum ada pembayaran.
      </p>
    </div>
    <div v-else class="mt-4 grid gap-3 sm:grid-cols-2">
      <Link
        v-for="item in items"
        :key="item.label"
        :href="item.href"
        :class="item.tone === 'rose' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700'"
        class="rounded-xl border border-current/10 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
      >
        <p class="text-sm font-bold">{{ item.label }}</p>
        <p class="mt-2 text-xl font-bold">{{ item.value }} transaksi</p>
        <p class="mt-1 text-xs font-semibold">{{ money(item.amount) }}</p>
      </Link>
    </div>
  </section>
</template>
