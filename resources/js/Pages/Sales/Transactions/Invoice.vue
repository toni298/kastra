<script setup>
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  transaction: { type: Object, required: true },
  branch: { type: Object, default: null },
  company: { type: Object, default: null },
})

const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`

const invoiceNumber = () => props.transaction?.number || props.transaction?.transaction_number || '-'
const invoiceDate = () => props.transaction?.date || props.transaction?.date_iso || '-'
const customerName = () => props.transaction?.customer || 'Penjualan Umum'
const customerPhone = () => props.transaction?.customer_detail?.telp || props.transaction?.shipping_phone || '-'
const customerAddress = () => props.transaction?.customer_detail?.address || props.transaction?.shipping_address || '-'
const branchName = () => props.branch?.name || props.transaction?.branch || 'Cabang'

const formatDate = (date) => {
  if (!date) return '-'

  const value = typeof date === 'string' && date.includes('/')
    ? date.split('/').reverse().join('-')
    : date

  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(value))
}

const handlePrint = () => window.print()
</script>

<template>
  <Head :title="`Invoice ${invoiceNumber()}`" />
  <main class="mx-auto max-w-4xl bg-white p-8 text-slate-900 print:max-w-none print:p-0">
    <div class="mb-6 flex justify-end print:hidden">
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
        @click="handlePrint"
      >
        Cetak Invoice
      </button>
    </div>
    <!-- Header with Logo and Company Info -->
    <header class="mb-8 flex items-start justify-between border-b-2 border-slate-300 pb-6">
      <div>
        <div v-if="branch || company" class="mb-4">
          <h1 class="text-3xl font-bold text-slate-900">{{ branch?.name || company?.name || 'Cabang Usaha' }}</h1>
          <p v-if="branch?.address" class="mt-1 text-sm text-slate-600">{{ branch.address }}</p>
          <p v-if="branch?.city || branch?.province || branch?.postal_code" class="text-sm text-slate-600">
            {{ branch?.city || '' }}
            <span v-if="branch?.province">, {{ branch.province }}</span>
            <span v-if="branch?.postal_code"> {{ branch.postal_code }}</span>
          </p>
          <div v-if="branch?.phone || branch?.email" class="mt-2 text-sm text-slate-600">
            <p v-if="branch?.phone">Telepon: {{ branch.phone }}</p>
            <p v-if="branch?.email">Email: {{ branch.email }}</p>
          </div>
        </div>
      </div>

      <div class="text-right">
        <div class="mb-4">
          <h2 class="text-2xl font-bold text-slate-900">{{ invoiceNumber() }}</h2>
          <p class="mt-1 text-sm font-semibold uppercase tracking-[0.2em] text-slate-600">Quitansi / Invoice</p>
        </div>
        <dl class="space-y-1 text-sm">
          <div class="flex justify-between gap-4">
            <dt class="text-slate-600">Tanggal:</dt>
            <dd class="font-semibold">{{ formatDate(invoiceDate()) }}</dd>
          </div>
          <div class="flex justify-between gap-4">
            <dt class="text-slate-600">Status:</dt>
            <dd class="inline-block rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">
              {{ props.transaction.status || '-' }}
            </dd>
          </div>
        </dl>
      </div>
    </header>

    <!-- Customer and Transaction Info -->
    <section class="mb-8 grid grid-cols-2 gap-8 text-sm">
      <!-- Customer Info -->
      <div>
        <h3 class="font-bold text-slate-900">Kepada</h3>
        <dl class="mt-3 space-y-1">
          <div>
            <dt class="text-slate-600">Nama:</dt>
            <dd class="font-semibold">{{ customerName() }}</dd>
          </div>
          <div v-if="customerAddress() !== '-'">
            <dt class="text-slate-600">Alamat:</dt>
            <dd class="text-slate-700">{{ customerAddress() }}</dd>
          </div>
          <div v-if="customerPhone() !== '-'">
            <dt class="text-slate-600">Nomor Hp:</dt>
            <dd class="text-slate-700">{{ customerPhone() }}</dd>
          </div>
        </dl>
      </div>

      <div>
        <h3 class="font-bold text-slate-900">Detail Transaksi</h3>
        <dl class="mt-3 space-y-1">
          <div>
            <dt class="text-slate-600">Jenis Dokumen:</dt>
            <dd class="font-semibold">{{ props.transaction.document_type || '-' }}</dd>
          </div>
          <div v-if="props.transaction.due_date">
            <dt class="text-slate-600">Jatuh Tempo:</dt>
            <dd class="text-slate-700">{{ formatDate(props.transaction.due_date) }}</dd>
          </div>
          <div v-if="props.transaction.payment_status">
            <dt class="text-slate-600">Status Pembayaran:</dt>
            <dd class="font-semibold text-slate-700">{{ props.transaction.payment_status }}</dd>
          </div>
        </dl>
      </div>
    </section>

    <!-- Items Table -->
    <section class="mb-8">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b-2 border-slate-300 bg-slate-50">
            <th class="px-4 py-3 text-left font-bold text-slate-900">Barang</th>
            <th class="px-4 py-3 text-right font-bold text-slate-900">Jumlah</th>
            <th class="px-4 py-3 text-right font-bold text-slate-900">Harga</th>
            <th class="px-4 py-3 text-right font-bold text-slate-900">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in props.transaction.details ?? []"
            :key="item.id"
            class="border-b border-slate-200"
          >
            <td class="px-4 py-3 text-slate-900">{{ item.product || item.name || '-' }}</td>
            <td class="px-4 py-3 text-right text-slate-700">{{ item.quantity || 0 }}</td>
            <td class="px-4 py-3 text-right text-slate-700">{{ money(item.unit_price || 0) }}</td>
            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ money(item.subtotal || 0) }}</td>
          </tr>
        </tbody>
      </table>
    </section>

    <!-- Summary and Total -->
    <section class="mb-8 flex justify-end">
      <div class="w-64 space-y-2 text-sm">
        <div class="flex justify-between border-b border-slate-200 pb-2">
          <dt class="text-slate-600">Subtotal</dt>
          <dd class="text-slate-900">{{ money((props.transaction.total || 0) + (props.transaction.discount || 0) - (props.transaction.tax || 0)) }}</dd>
        </div>
        <div v-if="props.transaction.discount" class="flex justify-between">
          <dt class="text-slate-600">Diskon</dt>
          <dd class="text-red-600">-{{ money(props.transaction.discount) }}</dd>
        </div>
        <div v-if="props.transaction.tax" class="flex justify-between">
          <dt class="text-slate-600">Pajak / PPN</dt>
          <dd class="text-slate-900">{{ money(props.transaction.tax) }}</dd>
        </div>
        <div v-if="props.transaction.shipping_cost" class="flex justify-between">
          <dt class="text-slate-600">Biaya Pengiriman</dt>
          <dd class="text-slate-900">{{ money(props.transaction.shipping_cost) }}</dd>
        </div>
        <div class="flex justify-between border-t-2 border-slate-300 pt-2 text-base font-bold">
          <dt class="text-slate-900">Total</dt>
          <dd class="text-slate-900">{{ money(props.transaction.total) }}</dd>
        </div>
      </div>
    </section>

    <!-- Payment Info -->
    <section v-if="props.transaction.payment" class="mb-8 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm">
      <h3 class="mb-3 font-bold text-slate-900">Informasi Pembayaran</h3>
      <dl class="space-y-1">
        <div class="flex justify-between">
          <dt class="text-slate-600">Metode Pembayaran:</dt>
          <dd class="font-semibold">{{ props.transaction.payment.method || props.transaction.payment.payment_method || '-' }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-slate-600">Jumlah Bayar:</dt>
          <dd class="font-semibold">{{ money(props.transaction.payment.amount || 0) }}</dd>
        </div>
      </dl>
    </section>

    <!-- Notes -->
    <section v-if="props.transaction.customer_note || props.transaction.note" class="mb-8 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm">
      <h3 class="mb-2 font-bold text-slate-900">Catatan</h3>
      <p v-if="props.transaction.customer_note" class="text-slate-700">{{ props.transaction.customer_note }}</p>
      <p v-if="props.transaction.note" class="text-slate-700">{{ props.transaction.note }}</p>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-300 pt-6 text-center text-xs text-slate-500">
      <p>Terima kasih telah berbelanja di {{ branch?.name || company?.name }}!</p>
      <p class="mt-1">Dokumen ini adalah bukti sah dari transaksi. Simpan baik-baik untuk keperluan Anda.</p>
    </footer>
  </main>
</template>
