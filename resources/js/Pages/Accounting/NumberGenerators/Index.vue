<script setup>
import AccountingIndex from '@/Pages/Accounting/Components/AccountingIndex.vue'
defineProps({
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})
</script>
<template><AccountingIndex type="numbers" :items="items" :filters="filters" /></template>
