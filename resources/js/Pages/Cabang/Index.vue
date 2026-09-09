<script setup>
import OrganizationIndex from '@/Pages/OrganizationIndex.vue'
defineProps({
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})
</script>
<template>
  <OrganizationIndex title="Cabang" type="cabang" :items="items" :filters="filters" />
</template>
