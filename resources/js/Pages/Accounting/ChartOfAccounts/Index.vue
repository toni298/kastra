<script setup>
import AccountingIndex from '@/Pages/Accounting/Components/AccountingIndex.vue'
defineProps({
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  options: { type: Array, default: () => [] },
})
</script>
<template>
  <AccountingIndex type="coa" :items="items" :filters="filters" :options="options" />
</template>
