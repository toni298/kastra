<script setup>
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CashierLayout from '@/Components/Cashier/CashierLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ShoppingCart } from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'
import { useToastify } from '@/Composables/useToastify'
import PurchaseReturnDetailDrawer from './Components/PurchaseReturnDetailDrawer.vue'
import PurchaseReturnHistory from './Components/PurchaseReturnHistory.vue'
import PurchaseTransactionDrawer from './Components/PurchaseTransactionDrawer.vue'
import PurchaseTransactionDeleteDialog from './Components/PurchaseTransactionDeleteDialog.vue'
import PurchaseTransactionList from './Components/PurchaseTransactionList.vue'
import PurchaseTransactionModal from './Components/PurchaseTransactionModal.vue'
import PurchasesTabHeader from './Components/PurchasesTabHeader.vue'
import SupplierDeleteDialog from './Components/SupplierDeleteDialog.vue'
import SupplierDrawer from './Components/SupplierDrawer.vue'
import SupplierModal from './Components/SupplierModal.vue'
import SupplierPanel from './Components/SupplierPanel.vue'
import { transactions } from './Data/purchaseData'
const toast = useToastify()
const props = defineProps({
  activeTab: { type: String, default: 'transactions' },
  purchaseItems: { type: Object, default: null },
  purchaseFilters: { type: Object, default: () => ({}) },
  purchaseOptions: { type: Object, default: () => ({ suppliers: [] }) },
  purchaseSummary: { type: Object, default: () => ({}) },
  supplierItems: { type: Object, default: null },
  supplierFilters: { type: Object, default: () => ({}) },
  purchaseReturnItems: { type: Object, default: null },
  purchaseReturnFilters: { type: Object, default: () => ({}) },
  capabilities: { type: Object, default: () => ({ create: false, edit: false, delete: false }) },
  cashierLayout: { type: Boolean, default: false },
})
const modal = ref(null)
const selected = ref(null)
const transactionDetailLoading = ref(false)
const transactionDetailError = ref(null)
const completeConfirmation = ref(null)
const transactionItems = ref(props.purchaseItems)
const tableLoadingSuppliers = ref(false)
const tableLoadingReturns = ref(false)
const purchaseActionLabels = {
  'edit-purchase': 'Edit pembelian',
  'send-po': 'Kirim PO',
  'print-purchase': 'Cetak pembelian',
}
const workflowLabels = {
  'receive-goods': 'Penerimaan barang',
  'create-invoice': 'Purchase Invoice',
  'pay-supplier': 'Pembayaran supplier',
  'return-goods': 'Retur barang',
}
const action = async ({ action: actionName, item = null }) => {
  if (actionName === 'edit-purchase') {
    selected.value = item
    modal.value = 'edit-transaction'
    return
  }
  if (actionName === 'delete-purchase') {
    selected.value = item
    modal.value = 'transaction-delete'
    return
  }
  if (actionName === 'supplier-detail') {
    selected.value = item
    modal.value = 'supplier-detail'
    try {
      selected.value = (
        await window.axios.get(route('purchases.suppliers.show', item.id))
      ).data.data
    } catch {
      toast.error('Detail supplier tidak dapat dimuat.')
    }
    return
  }
  if (actionName === 'return-detail') {
    selected.value = (await window.axios.get(route('purchases.returns.show', item.id))).data.data
    modal.value = 'return-detail'
    return
  }

  if (actionName === 'export' || actionName === 'export-transactions') {
    toast.info(
      actionName === 'export'
        ? 'Export supplier sedang diproses.'
        : 'Export transaksi sedang diproses.'
    )
    return
  }
  if (actionName === 'complete-purchase') {
    completeConfirmation.value = item
    return
  }
  if (actionName === 'transaction-detail') {
    selected.value = item
    transactionDetailLoading.value = true
    transactionDetailError.value = null
    modal.value = 'transaction-detail'
    try {
      selected.value = (
        await window.axios.get(route('purchases.transactions.show', item.id))
      ).data.data
    } catch {
      transactionDetailError.value =
        'Detail transaksi pembelian tidak dapat dimuat. Silakan coba lagi.'
    } finally {
      transactionDetailLoading.value = false
    }
    return
  }
  if (purchaseActionLabels[actionName]) {
    toast.info(`${purchaseActionLabels[actionName]} disiapkan pada tahap berikutnya.`)
    return
  }

  selected.value = item
  modal.value = actionName
}
const loadReturnItems = async (cursor, done = () => {}) => {
  if (!selected.value?.id || !cursor) {
    done()
    return
  }
  try {
    const data = (
      await window.axios.get(route('purchases.returns.show', selected.value.id), {
        params: { cursor },
      })
    ).data.data
    selected.value = {
      ...selected.value,
      items: {
        data: [...(selected.value.items?.data ?? []), ...(data.items?.data ?? [])],
        next_cursor: data.items?.next_cursor ?? null,
      },
    }
  } catch {
    toast.error('Barang retur tidak dapat dimuat.')
  } finally {
    done()
  }
}
const supplierInvoiceFilters = ref({})
const fetchSupplierDetail = async (id, params = {}) =>
  (await window.axios.get(route('purchases.suppliers.show', id), { params })).data.data
const loadSupplierInvoices = async (cursor, done = () => {}) => {
  if (!selected.value?.id || !cursor) {
    done()
    return
  }
  try {
    const data = await fetchSupplierDetail(selected.value.id, {
      ...supplierInvoiceFilters.value,
      cursor,
    })
    selected.value = {
      ...selected.value,
      invoices: {
        data: [...(selected.value.invoices?.data ?? []), ...(data.invoices?.data ?? [])],
        next_cursor: data.invoices?.next_cursor ?? null,
      },
    }
  } catch {
    toast.error('Invoice supplier tidak dapat dimuat.')
  } finally {
    done()
  }
}
const filterSupplierInvoices = async (filters) => {
  if (!selected.value?.id) return
  supplierInvoiceFilters.value = filters
  try {
    selected.value = await fetchSupplierDetail(selected.value.id, filters)
  } catch {
    toast.error('Invoice supplier tidak dapat dimuat.')
  }
}
const close = () => {
  modal.value = null
  selected.value = null
  transactionDetailError.value = null
}
const supplierSaved = () => close()
const supplierDeleted = () => close()
const transactionSaved = () => close()
const transactionDeleted = () => {
  router.delete(route('purchases.transactions.destroy', selected.value.id), {
    preserveScroll: true,
    onSuccess: close,
  })
}
const confirmComplete = () => {
  const transaction = completeConfirmation.value
  completeConfirmation.value = null
  router.post(
    route('purchases.transactions.complete', transaction.id),
    {},
    { preserveScroll: true, onSuccess: close }
  )
}
const workflowSubmitted = ({ type, data = {} }) => {
  if (type === 'return-goods') {
    router.post(route('purchases.transactions.returns.store', selected.value.id), data, {
      preserveScroll: true,
      onSuccess: close,
    })
    return
  }
  if (type === 'pay-supplier') {
    const formData = new FormData()
    formData.append('payment_date', data.paymentDate)
    formData.append('amount', data.amount)
    formData.append('owner_amount', data.owner_amount)
    formData.append('method', data.method)
    if (data.reference) formData.append('reference', data.reference)
    if (data.note) formData.append('note', data.note)
    if (data.proof_file) formData.append('proof_file', data.proof_file)
    router.post(route('purchases.transactions.payments.store', selected.value.id), formData, {
      preserveScroll: true,
      onSuccess: () =>
        router.reload({
          only: ['purchaseItems'],
          preserveScroll: true,
          onSuccess: async (page) => {
            const updated = page.props.purchaseItems?.data?.find(
              (row) => row.id === selected.value?.id
            )
            if (updated) selected.value = updated

            try {
              selected.value = (
                await window.axios.get(route('purchases.transactions.show', selected.value.id))
              ).data.data
            } catch {
              toast.error(
                'Detail pembayaran berhasil disimpan, tetapi detail transaksi gagal diperbarui.'
              )
            }
          },
        }),
    })
    return
  }
  toast.success(`${workflowLabels[type]} berhasil divalidasi.`)
}
const requestSuppliers = ({ url, data }) => {
  tableLoadingSuppliers.value = true
  router.get(url, data, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onFinish: () => {
      tableLoadingSuppliers.value = false
    },
  })
}
const requestReturns = ({ url, data }) => {
  tableLoadingReturns.value = true
  router.get(url, data, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onFinish: () => {
      tableLoadingReturns.value = false
    },
  })
}
watch(
  () => props.purchaseItems,
  (value) => {
    if (!value) return
    transactionItems.value = value
  }
)
watch(
  () => props.supplierItems,
  (items) => {
    // No longer needed with cursor pagination
  }
)
watch(
  () => props.purchaseReturnItems,
  (items) => {
    // No longer needed with cursor pagination
  }
)
</script>
<template>
  <Head title="Pembelian" />
  <component :is="cashierLayout ? CashierLayout : AuthenticatedLayout" :full-width="cashierLayout">
    <template v-if="!cashierLayout" #header>Pembelian</template>
    <div class="space-y-5">
      <PageHeader
        title="Pembelian"
        description="Kelola transaksi pembelian, supplier, dan dokumen pembelian dalam satu tempat."
        ><template #icon><ShoppingCart :size="22" /></template
      ></PageHeader>
      <PurchasesTabHeader :active="props.activeTab" :cashier-layout="cashierLayout" />
      <PurchaseTransactionList
        v-if="props.activeTab === 'transactions'"
        :transactions="transactionItems?.data ?? transactions"
        :pagination="transactionItems"
        :filters="purchaseFilters"
        :options="purchaseOptions"
        :summary="purchaseSummary"
        :capabilities="capabilities"
        @create="modal = 'sale'"
        @action="action"
      />
      <SupplierPanel
        v-else-if="props.activeTab === 'suppliers'"
        :items="supplierItems"
        :filters="supplierFilters"
        :loading="tableLoadingSuppliers"
        :capabilities="capabilities"
        @action="action"
        @request="requestSuppliers"
      />
      <PurchaseReturnHistory
        v-else-if="props.activeTab === 'returns'"
        :items="purchaseReturnItems"
        :filters="purchaseReturnFilters"
        :loading="tableLoadingReturns"
        @select="action({ action: 'return-detail', item: $event })"
        @request="requestReturns"
      />
    </div>
    <SupplierModal
      v-if="modal === 'create' || modal === 'edit'"
      :item="selected"
      @close="close"
      @saved="supplierSaved"
    />
    <SupplierDrawer
      v-if="modal === 'supplier-detail'"
      :supplier="selected"
      @filter-invoices="filterSupplierInvoices"
      @load-invoices="loadSupplierInvoices"
      @close="close"
    />
    <SupplierDeleteDialog
      v-if="modal === 'delete'"
      :supplier="selected"
      @close="close"
      @confirm="supplierDeleted"
    />
    <PurchaseTransactionModal
      v-if="modal === 'sale' || modal === 'edit-transaction'"
      :transaction="selected"
      :warehouses="purchaseOptions?.warehouses ?? []"
      :branches="purchaseOptions?.branches ?? []"
      :organization-mode="purchaseOptions?.organizationMode ?? 'branch'"
      @close="close"
      @saved="transactionSaved"
    />
    <PurchaseTransactionDeleteDialog
      v-if="modal === 'transaction-delete'"
      :transaction="selected"
      @close="close"
      @confirm="transactionDeleted"
    />
    <PurchaseTransactionDrawer
      v-if="modal === 'transaction-detail'"
      :transaction="selected"
      :loading="transactionDetailLoading"
      :error="transactionDetailError"
      @action="action"
      @close="close"
      @workflow-submit="workflowSubmitted"
    />
    <PurchaseReturnDetailDrawer
      v-if="modal === 'return-detail'"
      :purchase-return="selected"
      @load-items="loadReturnItems"
      @close="close"
    />
    <Modal
      v-if="completeConfirmation"
      :model-value="true"
      title="Selesaikan Transaksi"
      description="Pastikan seluruh proses pembelian dan pembayaran sudah selesai."
      size="sm"
      @update:model-value="completeConfirmation = null"
    >
      <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">
        Transaksi <strong class="font-mono">{{ completeConfirmation.number }}</strong> akan ditutup
        dan tidak dapat diproses kembali. Lanjutkan?
      </p>
      <template #footer
        ><Button variant="secondary" @click="completeConfirmation = null">Batal</Button
        ><Button @click="confirmComplete">Ya, Selesaikan Transaksi</Button></template
      >
    </Modal>
  </component>
</template>
