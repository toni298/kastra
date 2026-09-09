<script setup>
import { computed, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { ArrowRightLeft } from 'lucide-vue-next'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CashBankSummary from '../Components/CashBankSummary.vue'
import CashBankTabHeader from '../Components/CashBankTabHeader.vue'
import CashBankTransferList from '../Components/CashBankTransferList.vue'

const props = defineProps({
  cashBankAccounts: { type: Object, default: null },
  cashBankSummary: { type: Object, default: () => ({}) },
  cashBankTransfers: { type: Object, default: null },
  cashBankTransferFilters: { type: Object, default: () => ({}) },
})
const tableLoading = ref(false)
const accounts = computed(() =>
  Array.isArray(props.cashBankAccounts)
    ? props.cashBankAccounts
    : (props.cashBankAccounts?.data ?? [])
)
const requestTransfers = ({ url, data }) => {
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
  <Head title="Transfer Kas & Bank" />
  <AuthenticatedLayout>
    <template #header>Kas & Bank</template>
    <div class="space-y-5">
      <PageHeader
        title="Transfer Kas & Bank"
        description="Pantau transfer dana antar rekening perusahaan."
        ><template #icon><ArrowRightLeft :size="22" /></template
      ></PageHeader>
      <CashBankSummary :summary="cashBankSummary" />
      <CashBankTabHeader active="transfers" />
      <CashBankTransferList
        :items="cashBankTransfers"
        :accounts="accounts"
        :filters="cashBankTransferFilters"
        :loading="tableLoading"
        @request="requestTransfers"
      />
    </div>
  </AuthenticatedLayout>
</template>
