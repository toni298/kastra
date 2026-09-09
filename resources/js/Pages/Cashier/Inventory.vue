<script setup>
import { Head } from '@inertiajs/vue3'
import CashierLayout from '@/Components/Cashier/CashierLayout.vue'
import InventoryContent from '@/Pages/Inventory/Components/InventoryContent.vue'

const props = defineProps({
  activeTab: { type: String, default: 'overview' },
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
  inventoryRoutes: {
    type: Object,
    default: () => ({
      overview: 'cashier.inventory',
      stock: 'cashier.inventory.stock',
      transfer: 'cashier.inventory.transfers',
      opname: 'cashier.inventory.opnames',
    }),
  },
})
</script>

<template>
  <Head title="Inventory Kasir" />
  <CashierLayout full-width>
    <InventoryContent v-bind="props" :inventory-routes="inventoryRoutes" />
  </CashierLayout>
</template>
