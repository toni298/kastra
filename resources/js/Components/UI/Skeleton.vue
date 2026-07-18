<template>
  <div :class="['animate-pulse bg-secondary-200 rounded', sizeClasses]"></div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  width: { type: String, default: 'full' },
  height: { type: String, default: '4' },
  rounded: { type: Boolean, default: true },
})

const sizeClasses = computed(() => {
  return 'w-' + props.width + ' h-' + props.height + (props.rounded ? ' rounded' : '')
})
</script>
