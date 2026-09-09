<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { ShoppingCart } from 'lucide-vue-next'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SalesTabHeader from '../Components/SalesTabHeader.vue'
import SalesTransactionDrawer from '../Components/SalesTransactionDrawer.vue'
import SalesTransactionList from '../Components/SalesTransactionList.vue'
import SalesTransactionModal from '../Components/SalesTransactionModal.vue'
import SalesPaymentModal from '../Components/SalesPaymentModal.vue'
import SalesReturnModal from '../Components/SalesReturnModal.vue'
import { autoPrintThermalReceipt } from '@/Utils/thermalReceiptExport'

const props = defineProps({
  transactionItems: { type: Object, default: null },
  transactionFilters: { type: Object, default: () => ({}) },
  salesOptions: { type: Object, default: () => ({ branches: [] }) },
  capabilities: { type: Object, default: () => ({ create: false, edit: false, delete: false }) },
})

// Watch for invoice download flash data
watch(
  () => usePage().props.flash?.invoiceData,
  (invoiceData) => {
    if (!invoiceData) return

    const { transaction, branch, company } = invoiceData

    // Buka tab untuk struk thermal
    const thermalTab = window.open('about:blank', '_blank')
    if (thermalTab) {
      thermalTab.document.open()
      thermalTab.document.write(`
        <html>
          <body style="display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:'Courier New',monospace;background:#fff">
            <p style="color:#333;font-size:14px">Memproses struk...</p>
          </body>
        </html>
      `)
      thermalTab.document.close()
    }

    // Delay slightly to allow modal close animation
    setTimeout(() => {
      // Auto-print struk thermal (open new tab + auto print + auto close)
      autoPrintThermalReceipt(thermalTab, transaction, branch, company, {
        method: transaction.payment_method,
        amount: transaction.payment_amount,
        change: 0,
      })

      // Clear flash after print by reloading with only flash
      router.get(
        route('sales.transactions.index'),
        {},
        {
          preserveState: true,
          replace: true,
        }
      )
    }, 600)
  },
  { immediate: false }
)

const selectedTransaction = ref(null)
const detailLoading = ref(false)
const detailError = ref(null)
const modal = ref(null)
const tableLoading = ref(false)

const requestTransactions = ({ url, data }) => {
  tableLoading.value = true

  const options = {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      tableLoading.value = false
    },
  }

  router.get(url, data, options)
}

const loadDetail = async (item) => {
  selectedTransaction.value = item
  detailLoading.value = true
  detailError.value = null

  try {
    selectedTransaction.value = (
      await window.axios.get(route('sales.transactions.show', item.id))
    ).data.data
  } catch {
    detailError.value = 'Detail transaksi tidak dapat dimuat. Silakan coba lagi.'
  } finally {
    detailLoading.value = false
  }
}
const handleAction = async ({ action, item = null }) => {
  if (action === 'transaction-create') {
    modal.value = 'create'
    return
  }
  if (action === 'transaction-print') {
    window.print()
    return
  }
  if (
    [
      'transaction-detail',
      'transaction-edit',
      'transaction-payment',
      'transaction-return',
    ].includes(action)
  ) {
    await loadDetail(item)
    if (action === 'transaction-detail') return
    modal.value = action.replace('transaction-', '')
  }
}

const closeModal = () => {
  modal.value = null
}

const refreshTransactions = () => {
  router.reload({
    only: ['transactionItems'],
    preserveState: true,
    preserveScroll: true,
  })
}

const onPaymentSaved = async () => {
  modal.value = null
  if (selectedTransaction.value) {
    await loadDetail(selectedTransaction.value)
  }
  refreshTransactions()
}

const onReturnSaved = async () => {
  modal.value = null
  if (selectedTransaction.value) {
    await loadDetail(selectedTransaction.value)
  }
  refreshTransactions()
}

const onDeliveryCompleted = async () => {
  if (selectedTransaction.value) {
    await loadDetail(selectedTransaction.value)
  }
  refreshTransactions()
}

const closeDetail = () => {
  selectedTransaction.value = null
  detailError.value = null
  modal.value = null
}
</script>

<template>
  <Head title="Transaksi Penjualan" />
  <AuthenticatedLayout>
    <template #header>Penjualan</template>
    <div class="space-y-5">
      <PageHeader title="Penjualan" description="Kelola transaksi penjualan.">
        <template #icon><ShoppingCart :size="22" /></template>
      </PageHeader>
      <SalesTabHeader active="transactions" />
      <SalesTransactionList
        :items="transactionItems"
        :filters="transactionFilters"
        :options="salesOptions"
        :loading="tableLoading"
        @action="handleAction"
        @request="requestTransactions"
      />
    </div>
    <SalesTransactionDrawer
      v-if="selectedTransaction && !modal"
      :transaction="selectedTransaction"
      :loading="detailLoading"
      :error="detailError"
      @close="closeDetail"
      @payment="modal = 'payment'"
      @return="modal = 'return'"
      @completed="onDeliveryCompleted"
    />
    <SalesTransactionModal
      v-if="modal === 'create' || modal === 'edit'"
      :branches="salesOptions.branches"
      :item="modal === 'edit' ? selectedTransaction : null"
      @close="closeModal"
      @saved="closeModal"
    />
    <SalesPaymentModal
      v-if="modal === 'payment' && selectedTransaction"
      :transaction="selectedTransaction"
      @close="closeModal"
      @saved="onPaymentSaved"
    />
    <SalesReturnModal
      v-if="modal === 'return' && selectedTransaction"
      :transaction="selectedTransaction"
      @close="closeModal"
      @saved="onReturnSaved"
    />
  </AuthenticatedLayout>
</template>
