<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { ShoppingCart } from 'lucide-vue-next'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SalesTabHeader from '../Components/SalesTabHeader.vue'
import SalesReturnDrawer from '../Components/SalesReturnDrawer.vue'
import SalesReturnTable from '../Components/SalesReturnTable.vue'

const props = defineProps({
  returnItems: { type: Object, default: null },
  returnFilters: { type: Object, default: () => ({}) },
})
const selectedReturn = ref(null)
const detailLoading = ref(false)
const detailError = ref(null)
const tableLoading = ref(false)

const requestReturns = ({ url, data }) => {
  tableLoading.value = true
  router.get(url, data, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => {
      tableLoading.value = false
    },
  })
}
const openAction = async ({ action, item }) => {
  if (action !== 'return-detail') return
  selectedReturn.value = item
  detailLoading.value = true
  detailError.value = null
  try {
    selectedReturn.value = (await window.axios.get(route('sales.returns.show', item.id))).data.data
  } catch {
    detailError.value = 'Detail retur tidak dapat dimuat. Silakan coba lagi.'
  } finally {
    detailLoading.value = false
  }
}
const closeDetail = () => {
  selectedReturn.value = null
  detailError.value = null
}
</script>

<template>
  <Head title="Retur Penjualan" />
  <AuthenticatedLayout>
    <template #header>Penjualan</template>
    <div class="space-y-5">
      <PageHeader title="Penjualan" description="Kelola retur transaksi penjualan.">
        <template #icon><ShoppingCart :size="22" /></template>
      </PageHeader>
      <SalesTabHeader active="returns" />
      <SalesReturnTable
        :items="returnItems"
        :filters="returnFilters"
        :loading="tableLoading"
        @action="openAction"
        @request="requestReturns"
      />
    </div>
    <SalesReturnDrawer
      v-if="selectedReturn"
      :item="selectedReturn"
      :loading="detailLoading"
      :error="detailError"
      @close="closeDetail"
    />
  </AuthenticatedLayout>
</template>
