<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="modelValue" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 transition-opacity bg-secondary-900/50 backdrop-blur-sm" @click="closeOnOverlay && close()" />
          <div :class="sizeClasses" class="relative z-10 w-full text-left transform transition-all">
            <div class="bg-white rounded-lg shadow-soft">
              <div v-if="title" class="px-6 py-4 border-b border-secondary-200">
                <h3 class="text-lg font-semibold text-secondary-800">{{ title }}</h3>
              </div>
              <div class="px-6 py-4">
                <slot />
              </div>
              <div v-if=".footer" class="px-6 py-4 bg-secondary-50 border-t border-secondary-200 rounded-b-lg">
                <slot name="footer" />
              </div>
            </div>
            <button class="absolute top-4 right-4 text-secondary-400 hover:text-secondary-600" @click="close()">
              <Icon name="x" :size="20" />
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import Icon from './Icon.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: '' },
  size: { type: String, default: 'md' },
  closeOnOverlay: { type: Boolean, default: true }
})

const emit = defineEmits(['update:modelValue'])

const sizeClasses = computed(() => {
  const sizes = { sm: 'max-w-sm', md: 'max-w-md', lg: 'max-w-lg', xl: 'max-w-xl', full: 'max-w-4xl' }
  return sizes[props.size]
})

const close = () => emit('update:modelValue', false)
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
