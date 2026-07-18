<template>
  <div class="flex flex-col items-center justify-center py-12">
    <Icon :name="icon" :size="48" color="text-secondary-400" class="mb-4" />
    <h3 class="text-lg font-medium text-secondary-700 mb-2">{{ title }}</h3>
    <p class="text-sm text-secondary-500">{{ message }}</p>
    <slot name="action" />
  </div>
</template>

<script setup>
import Icon from './Icon.vue'

defineProps({
  icon: { type: String, default: 'inbox' },
  title: { type: String, default: 'Tidak ada data' },
  message: { type: String, default: 'Belum ada data yang tersedia.' }
})
</script>
