<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ProductContent from './Components/ProductContent.vue'

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
  productRoute: { type: String, default: 'products.index' },
  productMasterRoutes: { type: Object, default: () => ({}) },
})
</script>

<template>
  <Head title="Produk" />
  <AuthenticatedLayout>
    <template #header>Produk</template>
    <ProductContent v-bind="props" />
  </AuthenticatedLayout>
</template>
