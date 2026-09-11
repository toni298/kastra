<script setup>
import { computed } from 'vue'
import { Package } from '@lucide/vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import ProductDeleteModal from './ProductDeleteModal.vue'
import ProductDetailDrawer from './ProductDetailDrawer.vue'
import ProductFormModal from './ProductFormModal.vue'
import { useProductTabs } from '../Composables/useProductTabs'
import BrandTab from '../Tabs/BrandTab.vue'
import CategoryTab from '../Tabs/CategoryTab.vue'
import ProductTab from '../Tabs/ProductTab.vue'
import UnitTab from '../Tabs/UnitTab.vue'

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

const {
  activeTab,
  tabs,
  can,
  productModal,
  selectedProduct,
  productFormData,
  openProductModal,
  openProductForm,
  handleRequest,
  switchTab,
  handleProductMutation,
  handleMutation,
} = useProductTabs(props)

</script>

<template>
  <div class="space-y-5">
    <PageHeader
      title="Manajemen Produk"
      description="Kelola produk, kategori, brand, dan satuan dalam satu tempat."
    >
      <template #icon><Package :size="22" /></template>
    </PageHeader>

    <nav class="border-b border-slate-200 dark:border-[#29476b]" aria-label="Data produk">
      <div class="flex flex-wrap gap-x-7 gap-y-3 px-1" role="tablist">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          role="tab"
          :aria-selected="activeTab === tab.id"
          :class="[
            'relative shrink-0 pb-3 text-sm font-medium transition',
            activeTab === tab.id
              ? 'text-emerald-700 dark:text-emerald-300'
              : 'text-slate-500 hover:text-slate-800 dark:text-slate-400',
          ]"
          @click="switchTab(tab)"
        >
          {{ tab.label }}
          <span
            v-if="activeTab === tab.id"
            class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-emerald-600"
          ></span>
        </button>
      </div>
    </nav>

    <ProductTab
      v-if="activeTab === 'products'"
      :items="props.products"
      :filters="filters"
      :route-name="productRoute"
      @form="openProductForm"
      @detail="openProductModal('detail', $event)"
      @delete="openProductModal('delete', $event)"
      @mutated="handleProductMutation"
    />
    <CategoryTab
      v-else-if="activeTab === 'product_categories'"
      :items="props.product_categories"
      :filters="filters"
      :loading="loading"
      @request="handleRequest"
      @mutated="handleMutation"
    />
    <BrandTab
      v-else-if="activeTab === 'product_brands'"
      :items="props.product_brands"
      :filters="filters"
      :loading="loading"
      @request="handleRequest"
      @mutated="handleMutation"
    />
    <UnitTab
      v-else
      :items="props.units"
      :filters="filters"
      :loading="loading"
      @request="handleRequest"
      @mutated="handleMutation"
    />

    <ProductFormModal
      v-if="productModal === 'form' && productFormData.ready"
      :product="selectedProduct"
      :suggestions="productFormData.suggestions"
      :branches="branches"
      :can-manage-images="can('products.create') || can('products.edit')"
      @close="productModal = null"
      @saved="handleProductMutation"
    />
    <ProductDeleteModal
      v-if="productModal === 'delete'"
      :product="selectedProduct"
      @close="productModal = null"
      @deleted="handleProductMutation"
    />
    <ProductDetailDrawer
      v-if="productModal === 'detail'"
      :product="selectedProduct"
      @close="productModal = null"
    />
  </div>
</template>
