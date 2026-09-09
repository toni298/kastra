<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { ShoppingCart } from 'lucide-vue-next'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SalesTabHeader from '../Components/SalesTabHeader.vue'
import SalesCustomerTable from '../Components/SalesCustomerTable.vue'
import SalesCustomerDrawer from '../Components/SalesCustomerDrawer.vue'
import CustomerModal from '../Components/CustomerModal.vue'

const props = defineProps({ customerItems: { type: Object, default: null }, customerFilters: { type: Object, default: () => ({}) }, capabilities: { type: Object, default: () => ({ create: false, edit: false, delete: false }) } })
const selectedCustomer = ref(null)
const detailLoading = ref(false)
const detailError = ref(null)
const modal = ref(null)
const tableLoading = ref(false)

const requestCustomers = ({ url, data }) => {
  tableLoading.value = true
  router.get(url, data, { preserveState: true, preserveScroll: true, replace: true, onFinish: () => { tableLoading.value = false } })
}
const openAction = async ({ action, item }) => {
  if (action === 'create') { modal.value = 'create'; return }
  if (action === 'edit') { selectedCustomer.value = item; modal.value = 'edit'; return }
  if (action !== 'customer-detail') return
  selectedCustomer.value = item
  detailLoading.value = true
  detailError.value = null
  try { selectedCustomer.value = (await window.axios.get(route('sales.customers.show', item.id))).data.data } catch { detailError.value = 'Detail pelanggan tidak dapat dimuat. Silakan coba lagi.' } finally { detailLoading.value = false }
}
const closeDetail = () => { selectedCustomer.value = null; detailError.value = null; modal.value = null }
const requestDocuments = async ({ url, data }) => {
  detailLoading.value = true
  detailError.value = null
  try { selectedCustomer.value = (await window.axios.get(url, { params: data })).data.data } catch { detailError.value = 'Dokumen pelanggan tidak dapat dimuat. Silakan coba lagi.' } finally { detailLoading.value = false }
}
</script>
<template><Head title="Pelanggan" /><AuthenticatedLayout><template #header>Penjualan</template><div class="space-y-5"><PageHeader title="Penjualan" description="Kelola data pelanggan."><template #icon><ShoppingCart :size="22" /></template></PageHeader><SalesTabHeader active="customers" /><SalesCustomerTable :items="customerItems" :filters="customerFilters" :loading="tableLoading" @action="openAction" @request="requestCustomers" /></div><SalesCustomerDrawer v-if="selectedCustomer && !modal" :customer="selectedCustomer" :loading="detailLoading" :error="detailError" @close="closeDetail" @request-documents="requestDocuments" /><CustomerModal v-if="modal === 'create' || modal === 'edit'" :item="modal === 'edit' ? selectedCustomer : null" @close="closeDetail" @saved="closeDetail" /></AuthenticatedLayout></template>
