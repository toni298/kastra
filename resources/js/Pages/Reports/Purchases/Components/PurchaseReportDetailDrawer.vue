<script setup>
import { computed } from 'vue'
import { CalendarDays, CircleDollarSign, Package, Warehouse, X } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import { formatCurrency, formatQty } from '@/Utils/helpers'

const props = defineProps({
  transaction: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
})
const emit = defineEmits(['close'])
const transaction = computed(() => {
  const source = props.transaction
  const items = source.items ?? []
  return {
    ...source,
    items,
    supplier: source.supplier ?? 'Pembelian Umum',
    warehouse: source.warehouse ?? '-',
    total: Number(source.total ?? 0),
  }
})
const paymentLabel = (value) =>
  ({ paid: 'Lunas', partial: 'Utang / Pending', unpaid: 'Utang / Pending' })[value] ?? value ?? '-'
const paymentVariant = (value) => (value === 'paid' ? 'success' : 'warning')
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-2xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      aria-label="Detail laporan pembelian"
    >
      <header
        class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-200 bg-white px-5 py-5 dark:border-[#29476b] dark:bg-[#102542] sm:px-6"
      >
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
            Detail Laporan Pembelian
          </p>
          <h2 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">
            {{ transaction.supplier }}
          </h2>
          <p class="mt-1 font-mono text-sm text-slate-500">{{ transaction.number ?? '-' }}</p>
        </div>
        <button
          type="button"
          class="rounded-lg p-2 text-slate-400 hover:bg-slate-100"
          aria-label="Tutup detail"
          @click="emit('close')"
        >
          <X :size="20" />
        </button>
      </header>
      <div v-if="loading" class="p-6 text-center text-sm text-slate-500">
        Memuat detail pembelian...
      </div>
      <div v-else-if="error" class="m-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
        {{ error }}
      </div>
      <div v-else class="space-y-6 p-5 sm:p-6">
        <section class="grid gap-3 sm:grid-cols-2">
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <CalendarDays :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Tanggal Transaksi</p>
            <p class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.date ?? '-' }}</p>
          </div>
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <Warehouse :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Gudang Penerima</p>
            <p class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.warehouse }}</p>
          </div>
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <CircleDollarSign :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Total Nominal</p>
            <p class="mt-1 text-sm font-bold text-emerald-600">
              {{ formatCurrency(transaction.total) }}
            </p>
          </div>
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <Package :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Total Item</p>
            <p class="mt-1 text-sm font-semibold dark:text-white">
              {{ formatQty(transaction.total_qty) }}
            </p>
          </div>
        </section>
        <section class="flex flex-wrap gap-2">
          <Badge :variant="transaction.status === 'cancelled' ? 'error' : 'info'">{{
            transaction.status === 'cancelled' ? 'Dibatalkan' : (transaction.type ?? '-')
          }}</Badge
          ><Badge :variant="paymentVariant(transaction.payment)">{{
            paymentLabel(transaction.payment)
          }}</Badge>
        </section>
        <section>
          <h3 class="font-semibold text-slate-950 dark:text-white">Item Pembelian</h3>
          <div
            class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-[#29476b]"
          >
            <table class="min-w-full text-sm">
              <thead
                class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 dark:bg-[#0a1b33]"
              >
                <tr>
                  <th class="px-4 py-3">Produk</th>
                  <th class="px-4 py-3 text-right">Qty</th>
                  <th class="px-4 py-3 text-right">Harga</th>
                  <th class="px-4 py-3 text-right">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-[#29476b]">
                <tr v-for="item in transaction.items" :key="item.id">
                  <td class="px-4 py-3 font-medium dark:text-white">{{ item.name ?? '-' }}</td>
                  <td class="px-4 py-3 text-right tabular-nums dark:text-slate-200">
                    {{ formatQty(item.qty) }}
                  </td>
                  <td class="px-4 py-3 text-right tabular-nums dark:text-slate-200">
                    {{ formatCurrency(item.price) }}
                  </td>
                  <td class="px-4 py-3 text-right font-semibold tabular-nums dark:text-white">
                    {{ formatCurrency(item.subtotal) }}
                  </td>
                </tr>
                <tr v-if="!transaction.items.length">
                  <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                    Tidak ada detail item.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </aside>
  </Teleport>
</template>
