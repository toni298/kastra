import { ref } from 'vue'

const toasts = ref([])

const addToast = (message, type = 'info', duration = 3000) => {
  const id = Date.now()
  toasts.value.push({ id, message, type })
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }, duration)
}

const success = (message) => addToast(message, 'success')
const error = (message) => addToast(message, 'error')
const warning = (message) => addToast(message, 'warning')
const info = (message) => addToast(message, 'info')

export function useToast() {
  return { toasts, success, error, warning, info }
}
