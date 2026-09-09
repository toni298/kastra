<script setup>
import StockReportPage from './Index.vue'

defineOptions({ inheritAttrs: false })
</script>

<template>
  <StockReportPage v-bind="$attrs" />
</template>
