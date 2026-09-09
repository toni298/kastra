<script setup>
import CompanyLayout from '@/Layouts/CompanyLayout.vue'
import CompanyOrganizationPanel from '../Components/CompanyOrganizationPanel.vue'

defineProps({
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})
</script>

<template>
  <CompanyLayout active-tab="branches">
    <CompanyOrganizationPanel type="cabang" :items="items" :filters="filters" />
  </CompanyLayout>
</template>
