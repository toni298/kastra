<script setup>
import CompanyLayout from '@/Layouts/CompanyLayout.vue'
import CompanyOrganizationPanel from '../Components/CompanyOrganizationPanel.vue'

defineProps({
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})
</script>

<template>
  <CompanyLayout active-tab="warehouses">
    <CompanyOrganizationPanel type="gudang" :items="items" :filters="filters" />
  </CompanyLayout>
</template>
