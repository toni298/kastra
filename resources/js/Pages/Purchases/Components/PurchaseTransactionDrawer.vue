<script setup>
import { ref, onBeforeUnmount, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Printer, X } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import Modal from '@/Components/UI/Modal.vue'
import { generateInvoicePDFBlob } from '@/Utils/invoiceExport'
import {
  activateOverlayLayer,
  deactivateOverlayLayer,
  isTopOverlayLayer,
  lockOverlayBodyScroll,
  unlockOverlayBodyScroll,
} from '@/Composables/useModalLayer'
import PurchaseDetailActions from './PurchaseDetailActions.vue'
import PurchaseDetailItems from './PurchaseDetailItems.vue'
import PurchaseDetailTimeline from './PurchaseDetailTimeline.vue'
import PurchaseDetailTransactionInfo from './PurchaseDetailTransactionInfo.vue'
import PurchaseWorkflowModal from './PurchaseWorkflowModal.vue'

const props = defineProps({
  transaction: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
})

const emit = defineEmits(['close', 'action', 'workflow-submit'])

const workflowType = ref(null)
const showInvoicePreview = ref(false)
const invoiceUrl = ref('')
const { props: pageProps } = usePage()
const drawerToken = Symbol('purchase-detail-drawer')
const workflowActions = ['receive-goods', 'create-invoice', 'pay-supplier', 'return-goods']

const closeDrawer = () => {
  if (workflowType.value) return

  emit('close')
}
const closeWorkflow = () => {
  workflowType.value = null
}
const closeOnEscape = (event) => {
  if (event.key !== 'Escape' || workflowType.value || !isTopOverlayLayer(drawerToken)) {
    return
  }

  event.preventDefault()
  event.stopImmediatePropagation()
  closeDrawer()
}
const handleAction = (payload) => {
  if (payload.action === 'print-purchase') {
    printInvoice()
    return
  }

  if (workflowActions.includes(payload.action)) {
    workflowType.value = payload.action
    return
  }

  emit('action', payload)
}
const printInvoice = () => {
  const transaction = props.transaction
  const details = (transaction.items ?? []).map((item) => ({
    name: item.name ?? '-',
    quantity: item.qty ?? item.quantity ?? 0,
    unit_price: item.price ?? item.unit_price ?? 0,
    subtotal: item.subtotal ?? 0,
  }))
  const paidAmount = (transaction.payments ?? []).reduce(
    (sum, payment) => sum + Number(payment.amount || 0),
    0
  )
  const transactionData = {
    transaction_number: transaction.number,
    transaction_date: transaction.date_iso ?? transaction.date,
    customer: transaction.supplier ?? 'Pembelian Umum',
    customer_detail: {
      name: transaction.supplier ?? 'Pembelian Umum',
      address: transaction.supplierAddress ?? '',
      email: transaction.supplierEmail ?? '',
      phone: transaction.supplierPhone ?? '',
    },
    discount: 0,
    tax: 0,
    total: transaction.totalValue ?? transaction.total ?? 0,
    payment_method: transaction.payments?.at(-1)?.method ?? 'cash',
    payment: {
      method: transaction.payments?.at(-1)?.method ?? 'cash',
      status: transaction.payment ?? 'unpaid',
      amount: paidAmount,
    },
    note: transaction.note ?? null,
    details,
  }
  const company = pageProps.context?.company ?? { name: 'PT/CV' }
  const branch = { name: transaction.warehouse ?? 'Gudang', address: '' }

  invoiceUrl.value = generateInvoicePDFBlob(transactionData, branch, company)
  showInvoicePreview.value = true
}
const downloadInvoice = () => {
  if (!invoiceUrl.value) printInvoice()
  setTimeout(() => {
    const link = document.createElement('a')
    link.href = invoiceUrl.value
    link.download = `Invoice-Pembelian-${props.transaction.number}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }, 100)
}
const submitWorkflow = (payload) => {
  workflowType.value = null
  emit('workflow-submit', payload)
}

onMounted(() => {
  lockOverlayBodyScroll()
  activateOverlayLayer(drawerToken)
  document.addEventListener('keydown', closeOnEscape, true)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', closeOnEscape, true)
  deactivateOverlayLayer(drawerToken)
  unlockOverlayBodyScroll()
})
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-[2px]"
      aria-hidden="true"
      @click="closeDrawer"
    ></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 flex w-full max-w-5xl flex-col border-l border-slate-200 bg-slate-50 shadow-2xl dark:border-[#29476b] dark:bg-[#071426]"
      role="dialog"
      aria-modal="true"
      aria-labelledby="purchase-detail-title"
      :aria-hidden="workflowType ? 'true' : undefined"
      :inert="Boolean(workflowType)"
    >
      <header
        class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white px-5 py-5 dark:border-[#29476b] dark:bg-[#102542] sm:px-6"
      >
        <div class="min-w-0">
          <p
            class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-300"
          >
            Purchase Detail
          </p>
          <h2
            id="purchase-detail-title"
            class="mt-1 truncate font-mono text-xl font-semibold text-slate-950 dark:text-white"
          >
            {{ transaction.number }}
          </h2>
          <div class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-300">
            <p class="font-semibold text-slate-900 dark:text-white">{{ transaction.supplier }}</p>
            <p v-if="transaction.supplierPhone">📞 {{ transaction.supplierPhone }}</p>
            <p v-if="transaction.supplierAddress">📍 {{ transaction.supplierAddress }}</p>
          </div>
          <p class="mt-3 truncate font-mono text-sm font-medium text-slate-500">
            {{ transaction.date }}
          </p>
          <div class="mt-3 flex flex-wrap gap-2">
            <Badge :variant="transaction.typeVariant">{{ transaction.typeShort }}</Badge>
            <Badge :variant="transaction.statusVariant">{{ transaction.status }}</Badge>
            <Badge :variant="transaction.paymentVariant">{{ transaction.payment }}</Badge>
            <Badge v-if="transaction.due_status" :variant="transaction.due_status.variant">{{
              transaction.due_status.label
            }}</Badge>
          </div>
          <div v-if="transaction.due_date" class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            <span class="font-medium">Jatuh Tempo:</span> {{ transaction.due_date }}
          </div>
        </div>
        <IconButton label="Tutup detail pembelian" @click="closeDrawer">
          <X :size="20" />
        </IconButton>
      </header>

      <PurchaseDetailActions
        v-if="!loading && !error"
        :transaction="transaction"
        @action="handleAction"
      />

      <div v-if="loading" class="flex-1 p-6 text-center text-sm text-slate-500">
        Memuat detail transaksi pembelian…
      </div>
      <div v-else-if="error" class="m-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
        {{ error }}
      </div>
      <div v-else class="flex-1 overflow-y-auto p-4 sm:p-6">
        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
          <main class="space-y-5">
            <PurchaseDetailTransactionInfo :transaction="transaction" />
            <PurchaseDetailItems :items="transaction.items" :total="transaction.totalValue" />
          </main>
          <aside class="space-y-5 xl:sticky xl:top-0">
            <PurchaseDetailTimeline :events="transaction.timeline" />
          </aside>
        </div>
      </div>
    </aside>
  </Teleport>
  <PurchaseWorkflowModal
    v-if="workflowType"
    :type="workflowType"
    :transaction="transaction"
    @close="closeWorkflow"
    @submit="submitWorkflow"
  />
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
</template>
