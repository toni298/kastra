<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[100] space-y-2">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="['px-4 py-3 rounded-lg shadow-soft flex items-center', toastClasses(toast.type)]"
        >
          <Icon :name="toastIcon(toast.type)" :size="20" class="mr-2" />
          {{ toast.message }}
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useToast } from '@/Composables/useToast'
import Icon from '@/Components/UI/Icon.vue'

const { toasts } = useToast()

const toastClasses = (type) => {
  const classes = {
    success: 'bg-green-50 text-green-800 border border-green-200',
    error: 'bg-red-50 text-red-800 border border-red-200',
    warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
    info: 'bg-blue-50 text-blue-800 border border-blue-200',
  }
  return classes[type]
}

const toastIcon = (type) => {
  const icons = {
    success: 'check-circle',
    error: 'alert-circle',
    warning: 'alert-triangle',
    info: 'info',
  }
  return icons[type]
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(2rem);
}
</style>
