<script setup>
import ProductMasterPanel from '../Components/ProductMasterPanel.vue'

defineProps({
  items: { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})
defineEmits(['request', 'mutated'])
</script>

<template>
  <ProductMasterPanel
    master="product_brands"
    :items="items ?? { data: [] }"
    :filters="filters"
    :loading="loading"
    @request="$emit('request', $event)"
    @mutated="$emit('mutated')"
  />
</template>
