<script setup>
import { computed } from 'vue'
import {
  Banknote,
  CalendarDays,
  CircleDollarSign,
  CreditCard,
  Package,
  UserRound,
  X,
} from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import { formatCurrency, formatQty } from '@/Utils/helpers'

const props = defineProps({
  transaction: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
})
const emit = defineEmits(['close'])

const money = (value) => formatCurrency(Number(value || 0))
const paymentLabel = (value) => ({
  cash: 'Tunai',
  transfer: 'Transfer',
  qris: 'QRIS',
  ewallet: 'E-Wallet',
}[value] ?? value ?? '-')
const paymentStatusLabel = (value) => value === 'paid' ? 'Lunas' : value === 'partial' ? 'Sebagian Lunas' : 'Belum Lunas'
const transaction = computed(() => {
  const source = props.transaction
  const details = source.details ?? []
  const payments = source.payment_history ?? []
  const total = Number(source.total || 0)
  const paid = payments.reduce((sum, payment) => sum + Number(payment.amount || 0), 0)
  const paymentStatus = source.payment_status ?? (paid >= total && total > 0 ? 'paid' : paid > 0 ? 'partial' : 'unpaid')
  const subtotal = details.reduce((sum, item) => sum + Number(item.subtotal || 0), 0)

  return {
    ...source,
    details,
    payments,
    total,
    paymentStatus,
    customer: source.customer ?? 'Penjualan Umum',
    customerPhone: source.customer_detail?.telp ?? '-',
    customerAddress: source.customer_detail?.address ?? '-',
    deliveryMethod: source.delivery_method,
    paymentMethod: paymentLabel(source.payment_method ?? source.payment?.method),
    payment: paymentStatusLabel(paymentStatus),
    outstanding: money(Math.max(0, total - paid)),
    summary: {
      subtotal: money(subtotal),
      discount: money(source.discount),
      tax: money(source.tax),
      shippingCost: money(source.shipping_cost),
    },
  }
})
const items = computed(() => transaction.value.details)
const statusLabel = (value) => ({
  completed: 'Selesai',
  draft: 'Draft',
  partial_return: 'Retur Sebagian',
  full_return: 'Retur Penuh',
  cancelled: 'Dibatalkan',
}[value] ?? value ?? '-')
const statusVariant = (value) => value === 'completed'
  ? 'success'
  : ['cancelled', 'full_return'].includes(value)
    ? 'error'
    : 'warning'
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-2xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      aria-label="Detail laporan penjualan"
    >
      <header class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-200 bg-white px-5 py-5 dark:border-[#29476b] dark:bg-[#102542] sm:px-6">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Detail Laporan Penjualan</p>
          <h2 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">{{ transaction.customer ?? 'Penjualan Umum' }}</h2>
          <p class="mt-1 font-mono text-sm text-slate-500">{{ transaction.number ?? '-' }}</p>
        </div>
        <button
          type="button"
          class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-[#163354]"
          aria-label="Tutup detail"
          @click="emit('close')"
        >
          <X :size="20" />
        </button>
      </header>

      <div v-if="loading" class="p-6 text-center text-sm text-slate-500">Memuat detail transaksi...</div>
      <div v-else-if="error" class="m-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ error }}</div>
      <div v-else class="space-y-6 p-5 sm:p-6">
        <section class="grid gap-3 sm:grid-cols-2">
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <CalendarDays :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Tanggal Transaksi</p>
            <p class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.date ?? '-' }}</p>
          </div>
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <UserRound :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Cabang</p>
            <p class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.branch ?? '-' }}</p>
          </div>
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <CircleDollarSign :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Total Nominal</p>
            <p class="mt-1 text-sm font-bold text-emerald-600">{{ formatCurrency(transaction.total) }}</p>
          </div>
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <Package :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Total Item</p>
            <p class="mt-1 text-sm font-semibold dark:text-white">{{ formatQty(transaction.total_qty) }}</p>
          </div>
        </section>

        <section class="flex flex-wrap gap-2">
          <Badge :variant="statusVariant(transaction.status)">{{ statusLabel(transaction.status) }}</Badge>
          <Badge variant="info">{{ transaction.paymentMethod }}</Badge>
          <Badge :variant="transaction.paymentStatus === 'paid' ? 'success' : 'warning'">{{ transaction.payment }}</Badge>
        </section>

        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pelanggan</h3>
          <div class="mt-3 grid gap-4 rounded-xl border border-slate-200 p-4 shadow-sm dark:border-[#29476b] sm:grid-cols-3">
            <div><p class="text-xs text-slate-400">Nama</p><p class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.customer }}</p></div>
            <div><p class="text-xs text-slate-400">Telepon</p><p class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.customerPhone }}</p></div>
            <div><p class="text-xs text-slate-400">Alamat</p><p class="mt-1 break-words text-sm font-semibold dark:text-white">{{ transaction.customerAddress }}</p></div>
          </div>
        </section>

        <section v-if="transaction.deliveryMethod === 'delivery'">
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Detail Pengiriman</h3>
          <div class="mt-3 grid gap-4 rounded-xl border border-slate-200 p-4 shadow-sm dark:border-[#29476b] sm:grid-cols-2">
            <div><p class="text-xs text-slate-400">Penerima</p><p class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.shipping_recipient_name ?? '-' }}</p></div>
            <div><p class="text-xs text-slate-400">Telepon</p><p class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.shipping_phone ?? '-' }}</p></div>
            <div class="sm:col-span-2"><p class="text-xs text-slate-400">Alamat Pengiriman</p><p class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.shipping_address ?? '-' }}</p></div>
          </div>
        </section>

        <section>
          <h3 class="font-semibold text-slate-950 dark:text-white">Item Penjualan</h3>
          <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-[#29476b]">
            <table class="min-w-full text-sm">
              <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 dark:bg-[#0a1b33]">
                <tr><th class="px-4 py-3">Produk</th><th class="px-4 py-3 text-right">Qty</th><th class="px-4 py-3 text-right">Harga</th><th class="px-4 py-3 text-right">Subtotal</th></tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-[#29476b]">
                <tr v-for="item in items" :key="item.id">
                  <td class="px-4 py-3 font-medium dark:text-white">{{ item.product ?? '-' }}<span v-if="item.sku" class="ml-2 text-xs text-slate-400">{{ item.sku }}</span></td>
                  <td class="px-4 py-3 text-right tabular-nums dark:text-slate-200">{{ formatQty(item.quantity) }}</td>
                  <td class="px-4 py-3 text-right tabular-nums dark:text-slate-200">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-4 py-3 text-right font-semibold tabular-nums dark:text-white">{{ formatCurrency(item.subtotal) }}</td>
                </tr>
                <tr v-if="!items.length"><td colspan="4" class="px-4 py-6 text-center text-slate-500">Tidak ada detail item.</td></tr>
              </tbody>
            </table>
          </div>
        </section>

        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Ringkasan</h3>
          <div class="mt-3 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 p-4 shadow-sm dark:border-[#29476b]">
              <div class="flex items-center gap-2"><CreditCard :size="16" class="text-emerald-600" /><h4 class="font-semibold dark:text-white">Pembayaran</h4></div>
              <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd><Badge :variant="transaction.paymentStatus === 'paid' ? 'success' : 'warning'">{{ transaction.payment }}</Badge></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Metode</dt><dd class="font-medium dark:text-white">{{ transaction.paymentMethod }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Sisa Hutang</dt><dd class="font-bold text-orange-600">{{ transaction.outstanding }}</dd></div>
              </dl>
            </div>
            <div class="rounded-xl border border-slate-200 p-4 shadow-sm dark:border-[#29476b]">
              <h4 class="font-semibold dark:text-white">Ringkasan Nominal</h4>
              <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-medium dark:text-white">{{ transaction.summary.subtotal }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Diskon</dt><dd class="font-medium dark:text-white">{{ transaction.summary.discount }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Pajak</dt><dd class="font-medium dark:text-white">{{ transaction.summary.tax }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Biaya Kirim</dt><dd class="font-medium dark:text-white">{{ transaction.summary.shippingCost }}</dd></div>
                <div class="flex justify-between border-t border-slate-200 pt-3 dark:border-[#29476b]"><dt class="font-semibold dark:text-white">Grand Total</dt><dd class="text-lg font-bold text-emerald-600">{{ money(transaction.total) }}</dd></div>
              </dl>
            </div>
          </div>
        </section>

        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">History Pembayaran</h3>
          <div class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 shadow-sm dark:divide-[#29476b] dark:border-[#29476b]">
            <div v-for="(payment, index) in transaction.payments" :key="payment.number ?? index" class="flex items-center gap-3 p-4">
              <span class="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"><Banknote :size="17" /></span>
              <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold dark:text-white">{{ payment.number ?? '-' }}</p><p class="mt-1 text-xs text-slate-500">{{ payment.date ?? '-' }} · {{ paymentLabel(payment.method) }}</p></div>
              <p class="font-semibold dark:text-white">{{ money(payment.amount) }}</p>
            </div>
            <p v-if="!transaction.payments.length" class="p-4 text-sm text-slate-500">Belum ada pembayaran.</p>
          </div>
        </section>
      </div>
    </aside>
  </Teleport>
</template>
