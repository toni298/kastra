<template>
  <div class="flex justify-center items-center py-8">
    <Icon name="loader-2" :size="size" color="text-primary-500" class="animate-spin" />
  </div>
</template>

<script setup>
import Icon from './Icon.vue'

defineProps({
  size: { type: Number, default: 32 },
})
</script>
