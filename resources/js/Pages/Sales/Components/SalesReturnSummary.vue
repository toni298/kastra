<script setup>
defineProps({
  resolution: { type: String, required: true },
  settlement: { type: String, required: true },
  selectedCount: { type: Number, default: 0 },
  totalQty: { type: Number, default: 0 },
  totalReturnValue: { type: Number, default: 0 },
  replacementTotal: { type: Number, default: 0 },
  difference: { type: Number, default: 0 },
  refundAmount: { type: Number, default: 0 },
  customerCreditAmount: { type: Number, default: 0 },
  customerPaysAmount: { type: Number, default: 0 },
})

const emit = defineEmits(['update:settlement'])
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
</script>

<template>
  <section
    v-if="resolution === 'ganti_produk' && difference > 0"
    class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]"
  >
    <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
      Selisih Lebih — Nilai Retur &gt; Nilai Pengganti
    </h4>
    <div class="mt-3 grid grid-cols-2 gap-3">
      <button
        type="button"
        :class="[
          'rounded-lg border p-3 text-left text-sm transition',
          settlement === 'refund'
            ? 'border-emerald-500 bg-emerald-50 dark:border-emerald-400 dark:bg-emerald-400/10'
            : 'border-slate-200 dark:border-[#29476b]',
        ]"
        @click="emit('update:settlement', 'refund')"
      >
        <span class="font-semibold dark:text-white">Refund Selisih</span>
        <p class="mt-0.5 text-xs text-slate-500">{{ money(difference) }}</p>
      </button>
      <button
        type="button"
        :class="[
          'rounded-lg border p-3 text-left text-sm transition',
          settlement === 'potong_tagihan'
            ? 'border-emerald-500 bg-emerald-50 dark:border-emerald-400 dark:bg-emerald-400/10'
            : 'border-slate-200 dark:border-[#29476b]',
        ]"
        @click="emit('update:settlement', 'potong_tagihan')"
      >
        <span class="font-semibold dark:text-white">Potong Tagihan</span>
        <p class="mt-0.5 text-xs text-slate-500">{{ money(difference) }} (kredit)</p>
      </button>
    </div>
  </section>

  <section class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
      Ringkasan Penyelesaian
    </h3>
    <dl class="mt-3 space-y-2 text-sm">
      <div class="flex justify-between">
        <dt class="text-slate-500">Total Item Diretur</dt>
        <dd class="font-bold dark:text-white">{{ selectedCount }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Total Qty</dt>
        <dd class="font-bold dark:text-white">{{ totalQty }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-slate-500">Total Nilai Retur</dt>
        <dd class="font-bold text-emerald-600">{{ money(totalReturnValue) }}</dd>
      </div>
      <div v-if="resolution === 'ganti_produk'" class="flex justify-between">
        <dt class="text-slate-500">Total Produk Pengganti</dt>
        <dd class="font-bold dark:text-white">{{ money(replacementTotal) }}</dd>
      </div>
      <div
        v-if="resolution === 'ganti_produk'"
        class="flex justify-between border-t border-slate-200 pt-2 dark:border-[#29476b]"
      >
        <dt class="text-slate-500">Selisih</dt>
        <dd :class="['font-bold', difference >= 0 ? 'text-emerald-600' : 'text-red-600']">
          {{ difference >= 0 ? '+' : '' }}{{ money(difference) }}
        </dd>
      </div>
      <div v-if="refundAmount > 0" class="flex justify-between">
        <dt class="text-slate-500">Refund Dibayar</dt>
        <dd class="font-bold text-orange-600">{{ money(refundAmount) }}</dd>
      </div>
      <div v-if="customerCreditAmount > 0" class="flex justify-between">
        <dt class="text-slate-500">Kredit Pelanggan</dt>
        <dd class="font-bold text-blue-600">{{ money(customerCreditAmount) }}</dd>
      </div>
      <div v-if="customerPaysAmount > 0" class="flex justify-between">
        <dt class="text-slate-500">Tagihan Pelanggan</dt>
        <dd class="font-bold text-red-600">{{ money(customerPaysAmount) }}</dd>
      </div>
    </dl>
  </section>
</template>
