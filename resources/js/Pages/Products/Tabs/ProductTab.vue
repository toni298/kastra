<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import ProductTabPanel from '../Components/ProductTabPanel.vue'

defineEmits(['form', 'detail', 'delete', 'mutated'])

const props = defineProps({
  items: { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
  routeName: { type: String, default: 'products.index' },
})

const tableLoading = ref(false)

const requestProducts = ({ url, data = {}, replace = false }) => {
  tableLoading.value = true
  router.get(url, data, {
    only: ['products', 'filters'],
    preserveScroll: true,
    preserveState: true,
    replace,
    onFinish: () => {
      tableLoading.value = false
    },
  })
}
</script>

<template>
  <ProductTabPanel
    :items="props.items ?? { data: [] }"
    :filters="filters"
    :route-name="routeName"
    :loading="tableLoading"
    @request="requestProducts"
    @form="$emit('form', $event)"
    @detail="$emit('detail', $event)"
    @delete="$emit('delete', $event)"
    @mutated="$emit('mutated')"
  />
</template>
