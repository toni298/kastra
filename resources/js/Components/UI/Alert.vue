<template>
  <div :class="['p-4 rounded-lg border', variantClasses]">
    <div class="flex">
      <Icon :name="iconName" :size="20" class="mr-3 mt-0.5" />
      <div>
        <h4 v-if="title" class="font-medium">{{ title }}</h4>
        <p class="text-sm">{{ message }}</p>
      </div>
      <button v-if="dismissible" class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 hover:bg-white/50" @click="('dismiss')">
        <Icon name="x" :size="16" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Icon from './Icon.vue'

const props = defineProps({
  variant: { type: String, default: 'info' },
  title: { type: String, default: '' },
  message: { type: String, default: '' },
  dismissible: { type: Boolean, default: false }
})

defineEmits(['dismiss'])

const variantClasses = computed(() => {
  const variants = {
    success: 'bg-green-50 border-green-200 text-green-800',
    error: 'bg-red-50 border-red-200 text-red-800',
    warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
    info: 'bg-blue-50 border-blue-200 text-blue-800'
  }
  return variants[props.variant]
})

const iconName = computed(() => {
  const icons = { success: 'check-circle', error: 'alert-circle', warning: 'alert-triangle', info: 'info' }
  return icons[props.variant]
})
</script>
