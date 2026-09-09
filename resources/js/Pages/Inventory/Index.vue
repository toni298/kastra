<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InventoryContent from './Components/InventoryContent.vue'

const props = defineProps({
  activeTab: { type: String, default: 'overview' },
  enabledFeatures: { type: Array, default: () => [] },
  overviewSummary: { type: Object, default: null },
  overviewBranchStocks: { type: Object, default: null },
  overviewFilters: { type: Object, default: () => ({}) },
  overviewOptions: { type: Object, default: null },
  stockItems: { type: Object, default: null },
  stockFilters: { type: Object, default: () => ({}) },
  stockOptions: { type: Object, default: null },
  movementItems: { type: Object, default: null },
  movementFilters: { type: Object, default: () => ({}) },
  hasMultipleWarehouses: { type: Boolean, default: false },
  transferItems: { type: Object, default: null },
  transferFilters: { type: Object, default: () => ({}) },
  transferOptions: { type: Object, default: null },
  opnameItems: { type: Object, default: null },
  opnameFilters: { type: Object, default: () => ({}) },
  opnameOptions: { type: Object, default: null },
  inventoryRoutes: { type: Object, default: () => ({}) },
})
</script>

<template>
  <Head title="Persediaan" />
  <AuthenticatedLayout>
    <template #header>Persediaan</template>
    <InventoryContent v-bind="props" />
  </AuthenticatedLayout>
</template>
