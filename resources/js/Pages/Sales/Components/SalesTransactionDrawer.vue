<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Banknote, CheckCircle2, CreditCard, Printer, Plus, Undo2, X } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Modal from '@/Components/UI/Modal.vue'
import { useToastify } from '@/Composables/useToastify'
import { useAccessControl } from '@/Composables/useAccessControl'
import { generateInvoicePDFBlob } from '@/Utils/invoiceExport'

const props = defineProps({
  transaction: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
  hideActions: { type: Boolean, default: false },
})
const emit = defineEmits(['close', 'payment', 'return', 'completed'])
const { props: pageProps } = usePage()
const completeConfirm = ref(false)
const processingComplete = ref(false)
const showInvoicePreview = ref(false)
const invoiceUrl = ref('')
const toast = useToastify()
const { can } = useAccessControl()
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
const transaction = computed(() => {
  const source = props.transaction
  const details = source.details ?? source.items ?? []
  const subtotal = details.reduce(
    (sum, item) =>
      sum +
      Number(
        item.subtotal ?? (item.quantity ?? item.qty ?? 0) * (item.unit_price ?? item.price ?? 0)
      ),
    0
  )
  const status =
    source.status === 'completed'
      ? 'Completed'
      : source.status === 'return' || source.status === 'partial_return'
        ? 'Retur Sebagian'
        : source.status === 'full_return'
          ? 'Retur Penuh'
          : source.status === 'cancelled'
            ? 'Void'
            : source.status === 'pending'
              ? 'Dalam Proses'
              : 'Draft'
  const payments = source.payment_history ?? []
  const paid = payments.reduce((sum, payment) => sum + Number(payment.amount || 0), 0)
  const total = Number(source.total || 0)
  const paymentStatus =
    source.payment_status ??
    source.paymentStatus ??
    (paid >= total && total > 0 ? 'paid' : paid > 0 ? 'partial' : 'unpaid')
  const payment =
    paymentStatus === 'paid'
      ? 'Lunas'
      : paymentStatus === 'partial'
        ? 'Sebagian Lunas'
        : 'Belum Lunas'
  return {
    ...source,
    status,
    payment,
    paymentStatus,
    type: source.document_type ?? '-',
    displayDate: source.date ?? '-',
    dueDate: source.due_date ?? null,
    dueStatus: source.due_status ?? null,
    branch: source.branch ?? '-',
    cashier: source.cashier ?? source.created_by_name ?? '-',
    customer: source.customer ?? 'Penjualan Umum',
    customerTelp:
      source.customer_detail?.telp ?? source.customer?.telp ?? source.customerPhone ?? '-',
    customerAddress:
      source.customer_detail?.address ?? source.customer?.address ?? source.customerAddress ?? '-',
    deliveryMethod: source.delivery_method ?? source.deliveryMethod ?? '-',
    shippingRecipientName: source.shipping_recipient_name ?? source.shippingRecipientName ?? '-',
    shippingPhone: source.shipping_phone ?? source.shippingPhone ?? '-',
    shippingAddress: source.shipping_address ?? source.shippingAddress ?? '-',
    shippingCost: money(source.shipping_cost),
    paymentMethod: payments.at(-1)?.method ?? source.payment?.method ?? '-',
    items: details.map((item, index) => ({
      id: item.id ?? index,
      name: item.product ?? item.name ?? '-',
      qty: item.quantity ?? item.qty ?? 0,
      price: money(item.unit_price ?? item.price),
      subtotal: money(
        item.subtotal ?? (item.quantity ?? item.qty ?? 0) * (item.unit_price ?? item.price ?? 0)
      ),
    })),
    summary: source.summary ?? {
      subtotal: money(subtotal),
      discount: money(source.discount),
      tax: money(source.tax),
      shippingCost: money(source.shipping_cost),
    },
    total: money(total),
    outstanding: money(Math.max(0, total - paid)),
    payments,
  }
})
const canCompleteDelivery = computed(
  () => transaction.value.paymentStatus === 'paid' && transaction.value.status !== 'Completed'
)
const printInvoice = () => {
  const detailsData = (props.transaction.details ?? []).map((item) => ({
    name: item.product || item.name || item.product_name || '-',
    quantity: item.quantity || item.qty || 0,
    unit_price: item.unit_price || item.price || 0,
    subtotal:
      item.subtotal || (item.quantity || item.qty || 0) * (item.unit_price || item.price || 0),
  }))

  // Ambil customer data dengan proper fallback ke customer_detail atau customer object
  const customerDetail = props.transaction.customer_detail
  const customerObj =
    typeof props.transaction.customer === 'object' ? props.transaction.customer : null

  const customerName =
    customerDetail?.name || customerObj?.name || props.transaction.customer || 'Penjualan Umum'
  const customerPhone =
    customerDetail?.phone || customerDetail?.telp || customerObj?.telp || customerObj?.phone || ''
  const customerAddress = customerDetail?.address || customerObj?.address || ''

  // Hitung total yang sudah dibayar dari payment_history
  const paymentHistory = props.transaction.payment_history || []
  const paidAmount = paymentHistory.reduce((sum, payment) => sum + (payment.amount || 0), 0)

  // Map transaction data dengan field yang diharapkan generateInvoicePDF
  const transactionData = {
    transaction_number: props.transaction.number,
    transaction_date: props.transaction.date || new Date().toISOString(),
    customer: customerName,
    customer_detail: {
      name: customerName,
      address: customerAddress,
      email: customerDetail?.email || customerObj?.email || '',
      phone: customerPhone,
    },
    delivery_method: props.transaction.delivery_method,
    shipping_recipient_name: props.transaction.shipping_recipient_name || '',
    shipping_phone: props.transaction.shipping_phone || '',
    shipping_address: props.transaction.shipping_address || '',
    shipping_cost: props.transaction.shipping_cost || 0,
    discount: props.transaction.discount || 0,
    tax: props.transaction.tax || 0,
    total: props.transaction.total || 0,
    payment_method: props.transaction.payment_method || 'cash',
    payment: {
      method: props.transaction.payment_method || 'cash',
      status: props.transaction.payment_status || 'paid',
      amount: paidAmount || props.transaction.total || 0,
    },
    note: props.transaction.note || null,
    details: detailsData,
    branch_id: props.transaction.branch_id || null,
  }

  // Branch data - ambil dari transaction fields
  const branchData = {
    name: props.transaction.branch || 'PT/CV',
    address: props.transaction.branch_address || '',
    city: props.transaction.branch_city || '',
    province: props.transaction.branch_province || '',
    postal_code: props.transaction.branch_postal_code || '',
    phone: props.transaction.branch_phone || '',
    email: props.transaction.branch_email || '',
  }

  // Ambil company data dari page props context
  const companyData = pageProps.context?.company || { name: 'PT/CV' }

  const pdfBlobUrl = generateInvoicePDFBlob(transactionData, branchData, companyData)

  invoiceUrl.value = pdfBlobUrl
  showInvoicePreview.value = true
}

const downloadInvoice = () => {
  if (!invoiceUrl.value) {
    printInvoice() // Generate jika belum ada
  }
  setTimeout(() => {
    const link = document.createElement('a')
    link.href = invoiceUrl.value
    link.download = `Invoice-${props.transaction.number}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }, 100)
}
const completeDelivery = async () => {
  if (processingComplete.value) return
  processingComplete.value = true

  try {
    await window.axios.post(route('sales.transactions.complete', transaction.value.id))
    toast.success('Transaksi pengiriman berhasil diselesaikan.')
    completeConfirm.value = false
    emit('completed')
  } catch (error) {
    toast.error(
      error?.response?.data?.message ??
        'Transaksi pengiriman gagal diselesaikan. Silakan coba lagi.'
    )
  } finally {
    processingComplete.value = false
  }
}
const transactionBadge = (value) =>
  value === 'Completed'
    ? 'info'
    : value === 'Retur Sebagian'
      ? 'info'
      : value === 'Dalam Proses'
        ? 'info'
        : value === 'Retur Penuh'
          ? 'error'
          : value === 'Void'
            ? 'error'
            : 'neutral'
const paymentBadge = (value) =>
  value === 'Lunas' ? 'success' : value === 'Sebagian Lunas' ? 'warning' : 'warning'
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-2xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
    >
      <header
        class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-200 bg-white px-5 py-5 dark:border-[#29476b] dark:bg-[#102542] sm:px-6"
      >
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
            Detail Transaksi
          </p>
          <h2 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">
            {{ transaction.customer }}
          </h2>
          <p class="mt-1 font-mono text-sm text-slate-500">{{ transaction.number }}</p>
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
      <div v-if="loading" class="p-6 text-center text-sm text-slate-500">
        Memuat detail transaksi…
      </div>
      <div v-else-if="error" class="m-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
        {{ error }}
      </div>
      <div v-else class="space-y-6 p-5 sm:p-6">
        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            Informasi Dokumen
          </h3>
          <dl class="mt-3 grid grid-cols-2 gap-x-5 gap-y-4">
            <div>
              <dt class="text-xs text-slate-400">Nomor Dokumen</dt>
              <dd class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.number }}</dd>
            </div>
            <div>
              <dt class="text-xs text-slate-400">Jenis Dokumen</dt>
              <dd class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.type }}</dd>
            </div>
            <div>
              <dt class="text-xs text-slate-400">Tanggal</dt>
              <dd class="mt-1 text-sm font-semibold dark:text-white">
                {{ transaction.displayDate }}
              </dd>
            </div>
            <div>
              <dt class="text-xs text-slate-400">Cabang</dt>
              <dd class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.branch }}</dd>
            </div>
            <div>
              <dt class="text-xs text-slate-400">Status</dt>
              <dd class="mt-1">
                <Badge :variant="transactionBadge(transaction.status)">{{
                  transaction.status
                }}</Badge>
              </dd>
            </div>
            <div>
              <dt class="text-xs text-slate-400">Status Pembayaran</dt>
              <dd class="mt-1">
                <Badge :variant="paymentBadge(transaction.payment)">{{
                  transaction.payment
                }}</Badge>
              </dd>
            </div>
            <div v-if="transaction.dueDate">
              <dt class="text-xs text-slate-400">Jatuh Tempo</dt>
              <dd class="mt-1 flex items-center gap-2">
                <span class="text-sm font-semibold dark:text-white">{{ transaction.dueDate }}</span>
                <Badge v-if="transaction.dueStatus" :variant="transaction.dueStatus.variant">{{
                  transaction.dueStatus.label
                }}</Badge>
              </dd>
            </div>
          </dl>
        </section>
        <div
          v-if="!hideActions"
          class="flex justify-end gap-2 border-y border-slate-200 py-3 dark:border-[#29476b]"
        >
          <button
            v-if="
              can('penjualan.transactions.return') &&
              (transaction.status === 'Completed' || transaction.status === 'Retur Sebagian') &&
              transaction.payment === 'Lunas'
            "
            type="button"
            class="inline-flex h-10 items-center gap-2 rounded-xl border border-orange-300 px-4 text-sm font-semibold text-orange-700 hover:bg-orange-50 dark:border-orange-400/40 dark:text-orange-300 dark:hover:bg-orange-400/10"
            @click="emit('return', transaction)"
          >
            <Undo2 :size="16" />Retur</button
          ><button
            v-if="
              can('penjualan.transactions.payment') &&
              transaction.payment !== 'Lunas' &&
              transaction.status !== 'Void' &&
              transaction.status !== 'Retur Penuh'
            "
            type="button"
            class="inline-flex h-10 items-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700"
            @click="emit('payment', transaction)"
          >
            <Plus :size="16" />Tambah Pembayaran</button
          ><button
            v-if="canCompleteDelivery && can('penjualan.transactions.edit')"
            type="button"
            class="inline-flex h-10 items-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700"
            @click="completeConfirm = true"
          >
            <CheckCircle2 :size="16" />Selesaikan Pengiriman
          </button>
          <button
            v-if="can('penjualan.transactions.print')"
            type="button"
            class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-[#29476b] dark:text-slate-200 dark:hover:bg-[#163354]"
            @click="printInvoice"
          >
            <Printer :size="16" />Cetak Invoice
          </button>
        </div>
        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pelanggan</h3>
          <div
            class="mt-3 grid grid-cols-1 gap-4 rounded-xl border border-slate-200 p-4 shadow-sm dark:border-[#29476b] sm:grid-cols-3"
          >
            <div>
              <p class="text-xs text-slate-400">Nama</p>
              <p class="mt-1 text-sm font-semibold dark:text-white">{{ transaction.customer }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-400">Telepon</p>
              <p class="mt-1 text-sm font-semibold dark:text-white">
                {{ transaction.customerTelp }}
              </p>
            </div>
            <div class="min-w-0">
              <p class="text-xs text-slate-400">Alamat</p>
              <p
                class="mt-1 break-words text-sm font-semibold dark:text-white [overflow-wrap:anywhere]"
              >
                {{ transaction.customerAddress }}
              </p>
            </div>
          </div>
        </section>
        <section v-if="transaction.deliveryMethod === 'delivery'">
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            Detail Pengiriman
          </h3>
          <div
            class="mt-3 grid grid-cols-1 gap-4 rounded-xl border border-slate-200 p-4 shadow-sm dark:border-[#29476b] sm:grid-cols-2"
          >
            <div>
              <p class="text-xs text-slate-400">Metode</p>
              <p class="mt-1 text-sm font-semibold dark:text-white">Delivery</p>
            </div>
            <div>
              <p class="text-xs text-slate-400">Penerima</p>
              <p class="mt-1 text-sm font-semibold dark:text-white">
                {{ transaction.shippingRecipientName }}
              </p>
            </div>
            <div>
              <p class="text-xs text-slate-400">Telepon</p>
              <p class="mt-1 text-sm font-semibold dark:text-white">
                {{ transaction.shippingPhone }}
              </p>
            </div>
            <div>
              <p class="text-xs text-slate-400">Alamat Pengiriman</p>
              <p class="mt-1 text-sm font-semibold dark:text-white">
                {{ transaction.shippingAddress }}
              </p>
            </div>
          </div>
        </section>
        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Daftar Item</h3>
          <div
            class="mt-3 overflow-hidden rounded-xl border border-slate-200 shadow-sm dark:border-[#29476b]"
          >
            <table class="w-full text-sm">
              <thead
                class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-[#0a1b33] dark:text-slate-300"
              >
                <tr>
                  <th class="px-4 py-3 text-left">Produk</th>
                  <th class="px-3 py-3 text-right">Qty</th>
                  <th class="px-3 py-3 text-right">Harga</th>
                  <th class="px-4 py-3 text-right">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-[#29476b]">
                <tr v-for="item in transaction.items" :key="item.id">
                  <td class="px-4 py-3 font-medium dark:text-white">{{ item.name }}</td>
                  <td class="px-3 py-3 text-right text-slate-500">{{ item.qty }}</td>
                  <td class="px-3 py-3 text-right text-slate-500">{{ item.price }}</td>
                  <td class="px-4 py-3 text-right font-semibold dark:text-white">
                    {{ item.subtotal }}
                  </td>
                </tr>
                <tr v-if="!transaction.items.length">
                  <td colspan="4" class="px-4 py-6 text-center text-slate-500">Tidak ada item.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Ringkasan</h3>
          <div class="mt-3 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 p-4 shadow-sm dark:border-[#29476b]">
              <div class="flex items-center gap-2">
                <CreditCard :size="16" class="text-emerald-600" />
                <h4 class="font-semibold dark:text-white">Pembayaran</h4>
              </div>
              <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                  <dt class="text-slate-500">Status</dt>
                  <dd>
                    <Badge :variant="paymentBadge(transaction.payment)">{{
                      transaction.payment
                    }}</Badge>
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-slate-500">Metode</dt>
                  <dd class="font-medium dark:text-white">{{ transaction.paymentMethod }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-slate-500">Sisa Hutang</dt>
                  <dd class="font-bold text-orange-600">{{ transaction.outstanding }}</dd>
                </div>
              </dl>
            </div>
            <div class="rounded-xl border border-slate-200 p-4 shadow-sm dark:border-[#29476b]">
              <h4 class="font-semibold dark:text-white">Ringkasan</h4>
              <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                  <dt class="text-slate-500">Subtotal</dt>
                  <dd class="font-medium dark:text-white">{{ transaction.summary.subtotal }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-slate-500">Diskon</dt>
                  <dd class="font-medium dark:text-white">{{ transaction.summary.discount }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-slate-500">Pajak</dt>
                  <dd class="font-medium dark:text-white">{{ transaction.summary.tax }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-slate-500">Biaya Kirim</dt>
                  <dd class="font-medium dark:text-white">
                    {{ transaction.summary.shippingCost }}
                  </dd>
                </div>
                <div
                  class="flex justify-between border-t border-slate-200 pt-3 dark:border-[#29476b]"
                >
                  <dt class="font-semibold dark:text-white">Grand Total</dt>
                  <dd class="text-lg font-bold text-emerald-600">{{ transaction.total }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </section>
        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            History Pembayaran
          </h3>
          <div
            class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 shadow-sm dark:divide-[#29476b] dark:border-[#29476b]"
          >
            <div
              v-for="(payment, index) in transaction.payments"
              :key="payment.number ?? index"
              class="flex items-center gap-3 p-4"
            >
              <span
                class="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"
                ><Banknote :size="17"
              /></span>
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold dark:text-white">{{ payment.number }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ payment.date }} · {{ payment.method }}</p>
              </div>
              <div class="text-right">
                <p class="font-semibold dark:text-white">{{ money(payment.amount) }}</p>
              </div>
            </div>
            <p v-if="!transaction.payments.length" class="p-4 text-sm text-slate-500">
              Belum ada pembayaran.
            </p>
          </div>
        </section>
      </div>
      <Modal
        :model-value="showInvoicePreview"
        title="Preview Invoice"
        description="Preview dan download invoice PDF"
        size="screen"
        @update:model-value="showInvoicePreview = $event"
      >
        <div class="flex flex-col gap-4">
          <div class="flex justify-end gap-2">
            <button
              type="button"
              class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-[#29476b] dark:text-slate-200 dark:hover:bg-[#163354]"
              @click="downloadInvoice"
            >
              <Printer :size="16" />Download PDF
            </button>
          </div>
          <iframe
            :src="invoiceUrl"
            class="h-[70vh] w-full rounded-xl border border-slate-200 dark:border-[#29476b]"
            title="Invoice Preview"
          ></iframe>
        </div>
        <template #footer>
          <button
            type="button"
            class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-[#29476b] dark:bg-[#102542] dark:text-slate-200 dark:hover:bg-[#163354]"
            @click="showInvoicePreview = false"
          >
            Tutup
          </button>
        </template>
      </Modal>

      <Modal
        :model-value="completeConfirm"
        title="Selesaikan Pengiriman"
        description="Tandai transaksi delivery ini sebagai selesai. Status akan diubah menjadi Completed."
        size="md"
        @update:model-value="completeConfirm = $event"
      >
        <p class="text-sm text-slate-600 dark:text-slate-300">
          Transaksi <strong>{{ transaction.number }}</strong> akan diselesaikan dan statusnya
          diperbarui.
        </p>
        <template #footer>
          <button
            type="button"
            class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-[#29476b] dark:bg-[#102542] dark:text-slate-200 dark:hover:bg-[#163354]"
            @click="completeConfirm = false"
          >
            Batal
          </button>
          <button
            type="button"
            class="inline-flex h-10 items-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="processingComplete"
            @click="completeDelivery"
          >
            <CheckCircle2 :size="16" />Ya, Selesaikan
          </button>
        </template>
      </Modal>
    </aside>
  </Teleport>
</template>
