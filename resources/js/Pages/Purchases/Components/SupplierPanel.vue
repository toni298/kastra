<script setup>
import SupplierTable from './SupplierTable.vue'

defineProps({
  items: { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
  capabilities: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['action', 'request'])
</script>

<template>
  <section class="space-y-5">
    <SupplierTable
      :items="items"
      :filters="filters"
      :loading="loading"
      :capabilities="capabilities"
      @action="emit('action', $event)"
      @request="emit('request', $event)"
    />
  </section>
</template>
