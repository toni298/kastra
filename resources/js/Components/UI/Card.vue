<template>
  <div class="bg-white rounded-lg shadow-soft overflow-hidden">
    <div v-if="title || .header" class="px-6 py-4 border-b border-secondary-200">
      <h3 v-if="title" class="text-lg font-semibold text-secondary-800">{{ title }}</h3>
      <slot name="header" />
    </div>
    <div :class="paddingClass">
      <slot />
    </div>
    <div v-if=".footer" class="px-6 py-4 bg-secondary-50 border-t border-secondary-200">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, default: '' },
  padding: { type: Boolean, default: true }
})

const paddingClass = computed(() => props.padding ? 'px-6 py-4' : '')
</script>
