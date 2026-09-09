<script setup>
import {
  BadgeCheck,
  CalendarDays,
  CircleDollarSign,
  Clock3,
  FileText,
  UserRound,
} from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
defineProps({ transaction: { type: Object, required: true } })
const emit = defineEmits(['close'])
const items = [
  {
    name: 'Kopi Arabika 100 gr',
    type: 'Produk',
    qty: 10,
    price: 'Rp 55.000',
    subtotal: 'Rp 550.000',
  },
  {
    name: 'Paket Konsultasi Bisnis',
    type: 'Jasa',
    qty: 1,
    price: 'Rp 750.000',
    subtotal: 'Rp 750.000',
  },
  { name: 'Layanan Instalasi', type: 'Jasa', qty: 1, price: 'Rp 350.000', subtotal: 'Rp 350.000' },
]
const statusVariant = (value) =>
  value === 'Lunas' || value === 'Disetujui'
    ? 'success'
    : value === 'Belum Dibayar'
      ? 'warning'
      : 'info'
</script>
<template>
  <Modal
    :model-value="true"
    title="Detail Transaksi Penjualan"
    description="Informasi lengkap dokumen, item, dan pembayaran."
    size="full"
    @update:model-value="emit('close')"
    ><div class="space-y-6">
      <section
        class="flex flex-col justify-between gap-4 rounded-2xl bg-slate-50 p-5 dark:bg-[#0a1b33] sm:flex-row sm:items-start"
      >
        <div class="flex gap-3">
          <span
            class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
            ><FileText :size="21"
          /></span>
          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Nomor Dokumen</p>
            <h2 class="mt-1 text-xl font-semibold text-slate-950 dark:text-white">
              {{ transaction.number }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">{{ transaction.type }}</p>
          </div>
        </div>
        <div class="flex flex-wrap gap-2">
          <Badge :variant="statusVariant(transaction.status)">{{ transaction.status }}</Badge
          ><Badge :variant="statusVariant(transaction.payment)">{{ transaction.payment }}</Badge>
        </div>
      </section>
      <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
          <CalendarDays :size="17" class="text-emerald-600" />
          <p class="mt-3 text-xs font-medium text-slate-400">Tanggal</p>
          <p class="mt-1 text-sm font-medium dark:text-white">{{ transaction.date }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
          <UserRound :size="17" class="text-emerald-600" />
          <p class="mt-3 text-xs font-medium text-slate-400">Pelanggan</p>
          <p class="mt-1 text-sm font-medium dark:text-white">{{ transaction.customer }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
          <BadgeCheck :size="17" class="text-emerald-600" />
          <p class="mt-3 text-xs font-medium text-slate-400">Sales</p>
          <p class="mt-1 text-sm font-medium dark:text-white">{{ transaction.sales }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
          <CircleDollarSign :size="17" class="text-emerald-600" />
          <p class="mt-3 text-xs font-medium text-slate-400">Total Transaksi</p>
          <p class="mt-1 text-sm font-bold dark:text-white">{{ transaction.total }}</p>
        </div>
      </section>
      <section>
        <h3 class="font-semibold text-slate-950 dark:text-white">Item Penjualan</h3>
        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-[#29476b]">
          <table class="min-w-full">
            <thead
              class="bg-slate-50 text-left text-[12px] font-semibold uppercase tracking-wide text-slate-400 dark:bg-[#0a1b33]"
            >
              <tr>
                <th class="px-4 py-3">Item</th>
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3">Qty</th>
                <th class="px-4 py-3">Harga</th>
                <th class="px-4 py-3 text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm dark:divide-[#29476b]">
              <tr v-for="item in items" :key="item.name">
                <td class="px-4 py-3 font-medium dark:text-white">{{ item.name }}</td>
                <td class="px-4 py-3">
                  <Badge :variant="item.type === 'Produk' ? 'info' : 'success'">{{
                    item.type
                  }}</Badge>
                </td>
                <td class="px-4 py-3">{{ item.qty }}</td>
                <td class="px-4 py-3">{{ item.price }}</td>
                <td class="px-4 py-3 text-right font-bold">{{ item.subtotal }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
      <section class="grid gap-5 lg:grid-cols-[1fr_320px]">
        <div>
          <h3 class="font-semibold text-slate-950 dark:text-white">Riwayat Dokumen</h3>
          <ol class="mt-4 space-y-4 border-l-2 border-emerald-100 pl-5 dark:border-emerald-400/20">
            <li>
              <p class="text-sm font-medium dark:text-white">Dokumen dibuat</p>
              <p class="text-xs text-slate-500">20/07/2026 · 09:15 oleh {{ transaction.sales }}</p>
            </li>
            <li>
              <p class="text-sm font-medium dark:text-white">Dokumen diterbitkan</p>
              <p class="text-xs text-slate-500">20/07/2026 · 09:20</p>
            </li>
            <li>
              <div class="flex items-center gap-2">
                <Clock3 :size="15" class="text-amber-500" />
                <p class="text-sm font-medium dark:text-white">Menunggu pembayaran pelanggan</p>
              </div>
            </li>
          </ol>
        </div>
        <div
          class="rounded-2xl border border-slate-200 p-5 dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <h3 class="font-semibold dark:text-white">Ringkasan Pembayaran</h3>
          <dl class="mt-4 space-y-2 text-sm">
            <div class="flex justify-between">
              <dt>Subtotal</dt>
              <dd class="font-medium">Rp 11.261.261</dd>
            </div>
            <div class="flex justify-between">
              <dt>Diskon</dt>
              <dd class="font-medium">Rp 0</dd>
            </div>
            <div class="flex justify-between">
              <dt>PPN 11%</dt>
              <dd class="font-medium">Rp 1.238.739</dd>
            </div>
            <div
              class="mt-3 flex justify-between border-t border-slate-200 pt-3 dark:border-[#29476b]"
            >
              <dt class="font-semibold">Grand Total</dt>
              <dd class="text-lg font-bold text-emerald-600">{{ transaction.total }}</dd>
            </div>
            <div class="flex justify-between">
              <dt>Sudah Dibayar</dt>
              <dd class="font-medium">
                {{ transaction.payment === 'Lunas' ? transaction.total : 'Rp 0' }}
              </dd>
            </div>
          </dl>
        </div>
      </section>
    </div>
    <template #footer
      ><Button variant="secondary">Cetak Dokumen</Button
      ><Button @click="emit('close')">Tutup</Button></template
    ></Modal
  >
</template>
