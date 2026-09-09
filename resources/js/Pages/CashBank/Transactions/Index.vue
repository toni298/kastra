<script setup>
import { computed, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { WalletCards } from 'lucide-vue-next'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CashBankDeleteDialog from '../Components/CashBankDeleteDialog.vue'
import CashBankSummary from '../Components/CashBankSummary.vue'
import CashBankTabHeader from '../Components/CashBankTabHeader.vue'
import CashBankTransactionForm from '../Components/CashBankTransactionForm.vue'
import CashBankTransactionDrawer from '../Components/CashBankTransactionDrawer.vue'
import CashBankTransactionList from '../Components/CashBankTransactionList.vue'
import CashBankTransferModal from '../Components/CashBankTransferModal.vue'

const props = defineProps({
  cashBankAccounts: { type: Object, default: () => ({ data: [] }) },
  cashBankTransactionFilters: { type: Object, default: () => ({}) },
  cashBankTransactions: { type: Object, default: () => ({ data: [] }) },
  cashBankSummary: { type: Object, default: () => ({}) },
})

const modal = ref(null)
const selected = ref(null)
const transferModal = ref(false)
const tableLoading = ref(false)
const accounts = computed(() =>
  Array.isArray(props.cashBankAccounts)
    ? props.cashBankAccounts
    : (props.cashBankAccounts?.data ?? [])
)

const close = () => {
  modal.value = null
  selected.value = null
}

const handleAction = ({ action, item }) => {
  selected.value = item
  if (action === 'detail') return (modal.value = 'detail')
  if (action === 'delete') return (modal.value = 'delete')
  if (['in', 'out', 'none'].includes(action)) return (modal.value = action)
  modal.value = item?.type
}

const requestTransactions = ({ url, data }) => {
  tableLoading.value = true
  router.get(url, data, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onFinish: () => {
      tableLoading.value = false
    },
  })
}
</script>

<template>
  <Head title="Transaksi Kas & Bank" />
  <AuthenticatedLayout>
    <template #header>Kas & Bank</template>
    <div class="space-y-5">
      <PageHeader
        title="Kas & Bank"
        description="Pantau saldo dan seluruh arus kas perusahaan dalam satu financial timeline."
      >
        <template #icon><WalletCards :size="22" /></template>
      </PageHeader>
      <CashBankSummary :summary="cashBankSummary" />
      <CashBankTabHeader active="transactions" />
      <CashBankTransactionList
        :items="cashBankTransactions"
        :filters="cashBankTransactionFilters"
        :loading="tableLoading"
        @action="handleAction"
        @request="requestTransactions"
        @transfer="transferModal = true"
      />
    </div>
    <CashBankTransactionForm
      v-if="['in', 'out', 'none'].includes(modal)"
      :initial-type="modal"
      :item="selected"
      :accounts="accounts"
      @close="close"
      @saved="close"
    />
    <CashBankTransactionDrawer v-if="modal === 'detail'" :transaction="selected" @close="close" />
    <CashBankDeleteDialog
      v-if="modal === 'delete'"
      :transaction="selected"
      @close="close"
      @confirm="close"
    />
    <CashBankTransferModal
      v-model="transferModal"
      :accounts="accounts"
      @saved="router.reload({ only: ['cashBankTransactions', 'cashBankSummary'], preserveScroll: true })"
    />
  </AuthenticatedLayout>
</template>
