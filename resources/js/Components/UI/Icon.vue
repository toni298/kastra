<template>
  <component :is="iconComponent" :size="size" :class="colorClass" />
</template>

<script setup>
import { computed } from 'vue'
import * as icons from '@lucide/vue'

const props = defineProps({
  name: { type: String, required: true },
  size: { type: Number, default: 24 },
  color: { type: String, default: 'text-secondary-700' }
})

const iconComponent = computed(() => {
  const iconName = props.name.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join('')
  return icons[iconName] || icons['Circle']
})

const colorClass = computed(() => props.color)
</script>
