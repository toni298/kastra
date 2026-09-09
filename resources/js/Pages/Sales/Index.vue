<script setup>
import { computed, ref } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ShoppingCart } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CashierLayout from '@/Components/Cashier/CashierLayout.vue'
import SalesOverview from './Components/SalesOverview.vue'
import SalesCustomerTable from './Components/SalesCustomerTable.vue'
import SalesCustomerDrawer from './Components/SalesCustomerDrawer.vue'
import CustomerModal from './Components/CustomerModal.vue'
import SalesTransactionModal from './Components/SalesTransactionModal.vue'
import SalesTransactionDrawer from './Components/SalesTransactionDrawer.vue'
import SalesPaymentModal from './Components/SalesPaymentModal.vue'
import SalesReturnModal from './Components/SalesReturnModal.vue'
import SalesReturnTable from './Components/SalesReturnTable.vue'
import SalesReturnDrawer from './Components/SalesReturnDrawer.vue'
import SalesTransactionList from './Components/SalesTransactionList.vue'
import { useAccessControl } from '@/Composables/useAccessControl'
const props = defineProps({
  activeTab: { type: String, default: 'overview' },
  transactionItems: { type: Object, default: null },
  transactionFilters: { type: Object, default: () => ({}) },
  customerItems: { type: Object, default: null },
  customerFilters: { type: Object, default: () => ({}) },
  returnItems: { type: Object, default: null },
  returnFilters: { type: Object, default: () => ({}) },
  salesOptions: { type: Object, default: () => ({ branches: [] }) },
  cashierLayout: { type: Boolean, default: false },
  salesRoutes: { type: Object, default: () => ({}) },
})
const activeTab = computed(() => props.activeTab)
const { can } = useAccessControl()
const page = usePage()
const modal = ref(null)
const selected = ref(null)
const paymentTransaction = ref(null)
const returnTransaction = ref(null)
const tabs = computed(() => page.props.salesTabs ?? [])
const visibleTabs = computed(() => tabs.value.filter((tab) => can(tab.permission)))
const routeForTab = (tab) => {
  const configured = props.salesRoutes?.[tab.id] ?? tab.route
  return Array.isArray(configured) ? route(configured[0], configured[1]) : route(configured)
}
const action = ({ action, item = null }) => {
  selected.value = item
  modal.value = action
  if (action === 'complete-return') {
    returnTransaction.value = item
    modal.value = null
    return
  }
  if (action === 'cancel-return') {
    if (window.confirm(`Batalkan draft retur ${item.return_number}?`))
      router.delete(route('sales.returns.destroy', item.id), {
        preserveScroll: true,
        onSuccess: close,
      })
    return
  }
  if (action === 'print-return') {
    window.print()
    return
  }
  if (action === 'customer-detail') {
    window
      .fetch(route('sales.customers.show', item.id))
      .then((response) => response.json())
      .then((customer) => {
        selected.value = customer
        modal.value = 'customer-detail'
      })
  }
}
const close = () => {
  modal.value = null
  selected.value = null
}
const onPaymentSaved = () => {
  paymentTransaction.value = null
  close()
}
const onReturnSaved = () => {
  returnTransaction.value = null
  close()
}
const selectTab = (tab) => {
  if (tab.id !== activeTab.value)
    router.get(routeForTab(tab), {}, { preserveScroll: true, preserveState: true, replace: true })
}
const requestTransactions = ({ url, data }) =>
  router.get(url, data, { preserveScroll: true, preserveState: true, replace: true })
</script>
<template>
  <Head title="Penjualan" />
  <component :is="cashierLayout ? CashierLayout : AuthenticatedLayout" :full-width="cashierLayout">
    <template v-if="!cashierLayout" #header>Penjualan</template>
    <div class="space-y-5">
      <PageHeader
        title="Penjualan"
        description="Kelola transaksi penjualan, pelanggan, dan dokumen penjualan dalam satu tempat."
        ><template #icon><ShoppingCart :size="22" /></template><template #actions></template
      ></PageHeader>
      <nav
        class="sales-tabs-scroll overflow-x-auto border-b border-slate-200 dark:border-[#29476b]"
        aria-label="Navigasi penjualan"
      >
        <div class="flex min-w-max gap-7 px-1">
          <button
            v-for="tab in visibleTabs"
            :key="tab.id"
            :class="[
              'relative pb-3 text-sm font-medium transition',
              activeTab === tab.id
                ? 'text-emerald-700 dark:text-emerald-300'
                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400',
            ]"
            @click="selectTab(tab)"
          >
            {{ tab.label
            }}<span
              v-if="activeTab === tab.id"
              class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-emerald-600"
            ></span>
          </button>
        </div>
      </nav>
      <SalesOverview v-if="activeTab === 'overview'" @navigate="activeTab = $event" />
      <SalesTransactionList
        v-else-if="activeTab === 'transactions'"
        :items="transactionItems"
        :filters="transactionFilters"
        :options="salesOptions"
        @action="action"
        @request="requestTransactions"
      />
      <SalesCustomerTable
        v-else-if="activeTab === 'customers'"
        :items="customerItems"
        :filters="customerFilters"
        @action="action"
      />
      <SalesReturnTable
        v-else-if="activeTab === 'returns'"
        :items="returnItems"
        :filters="returnFilters"
        @action="action"
      />
    </div>
    <CustomerModal
      v-if="modal === 'create' || modal === 'edit'"
      :item="selected"
      @close="close"
      @saved="close"
    />
    <SalesTransactionModal
      v-if="modal === 'sale' || modal === 'edit-transaction'"
      :item="modal === 'edit-transaction' ? selected : null"
      :branches="salesOptions?.branches ?? []"
      @close="close"
      @saved="close"
    />
    <SalesTransactionDrawer
      v-if="modal === 'transaction-detail'"
      :transaction="selected"
      @close="close"
      @payment="paymentTransaction = $event"
      @return="returnTransaction = $event"
    />
    <SalesPaymentModal
      v-if="paymentTransaction"
      :transaction="paymentTransaction"
      @close="paymentTransaction = null"
      @saved="onPaymentSaved"
    />
    <SalesReturnModal
      v-if="returnTransaction"
      :transaction="
        returnTransaction.transaction ?? {
          number: returnTransaction.reference_number,
          customer: returnTransaction.customer,
          details: returnTransaction.details,
        }
      "
      :return-draft="returnTransaction"
      @close="returnTransaction = null"
      @saved="onReturnSaved"
    />
    <SalesCustomerDrawer v-if="modal === 'customer-detail'" :customer="selected" @close="close" />
    <SalesReturnDrawer v-if="modal === 'return-detail'" :item="selected" @close="close" />
    <div
      v-if="modal === 'delete'"
      class="fixed inset-0 z-50 grid place-items-center bg-slate-950/60 p-4"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-[#102542]">
        <h2 class="text-lg font-semibold dark:text-white">Hapus Pelanggan?</h2>
        <p class="mt-2 text-sm text-slate-500">{{ selected?.name }} akan dihapus dari data demo.</p>
        <div class="mt-6 flex justify-end gap-2">
          <Button variant="secondary" @click="close">Batal</Button
          ><Button variant="danger" @click="close">Hapus</Button>
        </div>
      </div>
    </div>
  </component>
</template>
<style scoped>
.sales-tabs-scroll {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.sales-tabs-scroll::-webkit-scrollbar {
  display: none;
}
</style>
