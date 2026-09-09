<script setup>
import { Head } from '@inertiajs/vue3'
import CashierLayout from '@/Components/Cashier/CashierLayout.vue'
import ProductContent from '@/Pages/Products/Components/ProductContent.vue'

const props = defineProps({
  activeTab: { type: String, default: 'products' },
  products: { type: Object, default: null },
  product_categories: { type: Object, default: null },
  product_brands: { type: Object, default: null },
  units: { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
  suggestions: { type: Object, default: null },
  branches: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  canCreate: { type: Boolean, default: false },
  canEdit: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
  productRoute: { type: String, default: 'cashier.products' },
  productMasterRoutes: {
    type: Object,
    default: () => ({
      product_categories: 'cashier.product.master',
      product_brands: 'cashier.product.master',
      units: 'cashier.product.master',
    }),
  },
})
</script>

<template>
  <Head title="Produk Kasir" />
  <CashierLayout full-width>
    <ProductContent
      v-bind="props"
      product-route="cashier.products"
      :product-master-routes="productMasterRoutes"
    />
  </CashierLayout>
</template>
