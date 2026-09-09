<script setup>
import OrganizationIndex from '@/Pages/OrganizationIndex.vue'
defineProps({
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})
</script>
<template>
  <OrganizationIndex title="Outlet" type="outlet" :items="items" :filters="filters" />
</template>
