<script setup>
import PageHeader from '@/Components/UI/PageHeader.vue'
import { useToastify } from '@/Composables/useToastify'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import {
  WalletCards
} from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'
import CashBankAccountDeleteDialog from './Components/CashBankAccountDeleteDialog.vue'
import CashBankAccountForm from './Components/CashBankAccountForm.vue'
import CashBankActionModal from './Components/CashBankActionModal.vue'
import CashBankDeactivateDialog from './Components/CashBankDeactivateDialog.vue'
import CashBankDeleteDialog from './Components/CashBankDeleteDialog.vue'
import CashBankDetailModal from './Components/CashBankDetailModal.vue'
import CashBankSummary from './Components/CashBankSummary.vue'
import CashBankTab from './Components/CashBankTab.vue'
import CashBankTabHeader from './Components/CashBankTabHeader.vue'
import CashBankTransactionDrawer from './Components/CashBankTransactionDrawer.vue'
import CashBankTransactionForm from './Components/CashBankTransactionForm.vue'
import CashBankTransactionList from './Components/CashBankTransactionList.vue'
import CashBankTransferList from './Components/CashBankTransferList.vue'

const props = defineProps({
  accountingEnabled: { type: Boolean, default: true },
  activeTab: { type: String, default: 'transactions' },
  cashBankAccounts: { type: Object, default: () => ({ data: [], next_cursor: null }) },
  cashBankTransactions: { type: Object, default: () => ({ data: [] }) },
  cashBankTransactionFilters: { type: Object, default: () => ({}) },
  cashBankTransfers: { type: Object, default: () => ({ data: [] }) },
  cashBankTransferFilters: { type: Object, default: () => ({}) },
  cashBankSummary: { type: Object, default: () => ({}) },
  cashBankBranches: { type: Array, default: () => [] },
  cashBankAccountSettings: { type: Array, default: () => [] },
})

const toast = useToastify()
const activeTab = ref(props.activeTab)
watch(
  () => props.activeTab,
  (tab) => {
    activeTab.value = tab
  }
)
const activePanel = computed(() =>
  activeTab.value === 'transactions'
    ? CashBankTransactionList
    : activeTab.value === 'transfers'
      ? CashBankTransferList
      : CashBankTab
)
const modal = ref(null)
const selected = ref(null)
const transactionItems = ref([])
const tableLoadingTransactions = ref(false)
const loadingMoreTransactions = ref(false)
const pendingMoreTransactions = ref(false)
const displayedTransactionItems = computed(() => ({
  ...(props.cashBankTransactions ?? { data: [] }),
  data: transactionItems.value,
}))
const transferItems = ref([])
const tableLoadingTransfers = ref(false)
const loadingMoreTransfers = ref(false)
const pendingMoreTransfers = ref(false)
const displayedTransferItems = computed(() => ({
  ...(props.cashBankTransfers ?? { data: [] }),
  data: transferItems.value,
}))
watch(
  () => props.cashBankTransactions,
  (items) => {
    const data = items?.data ?? []
    if (pendingMoreTransactions.value) {
      pendingMoreTransactions.value = false
      const ids = new Set(transactionItems.value.map((item) => item.id))
      transactionItems.value.push(...data.filter((item) => !ids.has(item.id)))
      return
    }
    transactionItems.value = data.slice()
  },
  { immediate: true }
)
watch(
  () => props.cashBankTransfers,
  (items) => {
    const data = items?.data ?? []
    if (pendingMoreTransfers.value) {
      pendingMoreTransfers.value = false
      const ids = new Set(transferItems.value.map((item) => item.id))
      transferItems.value.push(...data.filter((item) => !ids.has(item.id)))
      return
    }
    transferItems.value = data.slice()
  },
  { immediate: true }
)

const openModal = (type, item = null) => {
  selected.value = item
  modal.value = type
}
const handleAction = ({ action, type, item = null }) => {
  const target = action ?? type

  if (target === 'detail') return openModal('transactionDrawer', item)
  if (target === 'delete') return openModal('transactionDelete', item)
  if (target === 'edit') {
    if (item.transactionType === 'Transfer') return openModal('transfer', item)
    return openModal(item.amount > 0 ? 'in' : 'out', item)
  }

  openModal(target, item)
}
const close = () => {
  modal.value = null
  selected.value = null
}
const saved = () => {
  close()
}
const deleted = () => {
  toast.success('Transaksi berhasil dihapus.')
  close()
}
const deactivated = () => {
  router.patch(
    route('cash-bank.accounts.deactivate', selected.value.id),
    {},
    { preserveScroll: true, onSuccess: close }
  )
}
const deleteAccount = () =>
  router.delete(route('cash-bank.accounts.destroy', selected.value.id), {
    preserveScroll: true,
    onSuccess: close,
  })
const exported = () => toast.info('Export sedang diproses. Anda akan mendapat notifikasi.')
const requestTransactions = ({ url, data }) => {
  tableLoadingTransactions.value = true
  router.get(url, data, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onFinish: () => {
      tableLoadingTransactions.value = false
    },
  })
}
const loadMoreTransactions = (url) => {
  if (!url || loadingMoreTransactions.value || tableLoadingTransactions.value) return
  pendingMoreTransactions.value = true
  loadingMoreTransactions.value = true
  router.get(
    url,
    {},
    {
      only: ['cashBankTransactions'],
      preserveScroll: true,
      preserveState: true,
      onFinish: () => {
        loadingMoreTransactions.value = false
        pendingMoreTransactions.value = false
      },
    }
  )
}
const requestTransfers = ({ url, data }) => {
  tableLoadingTransfers.value = true
  router.get(url, data, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onFinish: () => {
      tableLoadingTransfers.value = false
    },
  })
}
const loadMoreTransfers = (url) => {
  if (!url || loadingMoreTransfers.value || tableLoadingTransfers.value) return
  pendingMoreTransfers.value = true
  loadingMoreTransfers.value = true
  router.get(
    url,
    {},
    {
      only: ['cashBankTransfers'],
      preserveScroll: true,
      preserveState: true,
      onFinish: () => {
        loadingMoreTransfers.value = false
        pendingMoreTransfers.value = false
      },
    }
  )
}
</script>

<template>
  <Head title="Kas & Bank" />
  <AuthenticatedLayout>
    <template #header>Kas & Bank</template>
    <div class="space-y-5">
      <PageHeader
        title="Kas & Bank"
        description="Pantau saldo dan seluruh arus kas perusahaan dalam satu financial timeline."
      >
        <template #icon><WalletCards :size="22" /></template>
      </PageHeader>

      <CashBankSummary :summary="props.cashBankSummary" />

      <CashBankTabHeader :active="activeTab" :accounting-enabled="props.accountingEnabled" />

      <KeepAlive>
        <component
          :is="activePanel"
          :key="activeTab"
          :type="activeTab"
          :accounts="
            activeTab === 'accounts'
              ? props.cashBankAccounts
              : Array.isArray(props.cashBankAccounts)
                ? props.cashBankAccounts
                : props.cashBankAccounts.data
          "
          :items="activeTab === 'transfers' ? displayedTransferItems : displayedTransactionItems"
          :filters="
            activeTab === 'transfers'
              ? props.cashBankTransferFilters
              : props.cashBankTransactionFilters
          "
          :loading="activeTab === 'transfers' ? tableLoadingTransfers : tableLoadingTransactions"
          :loading-more="activeTab === 'transfers' ? loadingMoreTransfers : loadingMoreTransactions"
          @action="handleAction"
          @request="activeTab === 'transfers' ? requestTransfers : requestTransactions"
          @load-more="activeTab === 'transfers' ? loadMoreTransfers : loadMoreTransactions"
        />
      </KeepAlive>
    </div>

    <CashBankActionModal
      v-if="modal === 'transfer'"
      type="transfer"
      :item="selected"
      :accounts="
        Array.isArray(props.cashBankAccounts) ? props.cashBankAccounts : props.cashBankAccounts.data
      "
      @close="close"
      @saved="saved"
    />
    <CashBankTransactionForm
      v-if="modal === 'in' || modal === 'out'"
      :item="selected"
      :initial-type="modal"
      :accounts="
        Array.isArray(props.cashBankAccounts) ? props.cashBankAccounts : props.cashBankAccounts.data
      "
      @close="close"
      @saved="saved"
    />
    <CashBankAccountForm
      v-if="modal === 'account' || modal === 'accountEdit'"
      :item="selected"
      :branches="props.cashBankBranches"
      :settings="props.cashBankAccountSettings"
      @close="close"
      @saved="saved"
    />
    <CashBankTransactionDrawer
      v-if="modal === 'transactionDrawer'"
      :transaction="selected"
      @close="close"
    />
    <CashBankDeleteDialog
      v-if="modal === 'transactionDelete'"
      :transaction="selected"
      @close="close"
      @confirm="deleted"
    />
    <CashBankDetailModal
      v-if="['accountDetail', 'transferDetail', 'reconciliationDetail'].includes(modal)"
      :type="modal === 'accountDetail' ? 'account' : modal"
      :item="selected"
      @delete-account="openModal('accountDelete', $event)"
      @close="close"
    />
    <CashBankAccountDeleteDialog
      v-if="modal === 'accountDelete'"
      :account="selected"
      @close="close"
      @confirm="deleteAccount"
    />
    <CashBankDeactivateDialog
      v-if="modal === 'deactivate'"
      :item="selected"
      @close="close"
      @confirm="deactivated"
    />
  </AuthenticatedLayout>
</template>
