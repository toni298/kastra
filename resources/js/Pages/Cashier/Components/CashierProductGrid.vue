<script setup>
import ProductCatalog from '@/Components/Products/ProductCatalog.vue'

defineProps({
  products: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  categoriesHasNext: { type: Boolean, default: false },
  categoriesLoadingMore: { type: Boolean, default: false },
  search: { type: String, default: '' },
  selectedCategory: { type: [String, Number], default: 'all' },
  loading: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
  hasNext: { type: Boolean, default: false },
})
defineEmits(['update:search', 'select-category', 'add', 'load-more', 'load-more-categories'])
</script>

<template>
  <ProductCatalog
    :products="products"
    :categories="categories"
    :search="search"
    :selected-category="selectedCategory"
    :loading="loading"
    :loading-more="loadingMore"
    :has-next="hasNext"
    :categories-has-next="categoriesHasNext"
    :categories-loading-more="categoriesLoadingMore"
    @update:search="$emit('update:search', $event)"
    @select-category="$emit('select-category', $event)"
    @add="$emit('add', $event)"
    @load-more="$emit('load-more')"
    @load-more-categories="$emit('load-more-categories')"
  />
</template>
