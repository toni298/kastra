<script setup>
import ProductMasterPanel from '../Components/ProductMasterPanel.vue'

defineProps({
  items: { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
})
defineEmits(['request', 'mutated', 'load-more'])
</script>

<template>
  <ProductMasterPanel
    master="units"
    :items="items ?? { data: [] }"
    :filters="filters"
    :loading="loading"
    :loading-more="loadingMore"
    @request="$emit('request', $event)"
    @mutated="$emit('mutated')"
    @load-more="$emit('load-more', $event)"
  />
</template>
