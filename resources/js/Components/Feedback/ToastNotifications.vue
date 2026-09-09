<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { onMounted, onUnmounted, watch } from 'vue'
import { useToastify } from '@/Composables/useToastify'

const page = usePage()
const toast = useToastify()
const flashTypes = ['success', 'error', 'warning', 'info']
let removeExceptionListener
let removeInvalidListener
let lastErrorKey = ''

const notifyFlash = (flash) => {
  if (!flash) return

  const notifications = flashTypes.filter((type) => Boolean(flash[type]))
  if (!notifications.length) return

  notifications.forEach((type) => toast[type](flash[type]))

  router.replace({
    preserveScroll: true,
    preserveState: true,
    props: (currentProps) => ({
      ...currentProps,
      flash: flashTypes.reduce((consumed, type) => ({ ...consumed, [type]: null }), {
        ...(currentProps.flash ?? {}),
      }),
    }),
  })
}

const notifyValidationErrors = (errors) => {
  const messages = Object.values(errors || {})
    .flat()
    .filter(Boolean)
  const errorKey = JSON.stringify(messages)

  if (!messages.length || errorKey === lastErrorKey) return

  lastErrorKey = errorKey
  toast.error(messages[0], { timeout: 6000 })
}

watch(
  () => page.props.flash,
  (flash) => notifyFlash(flash),
  { deep: true, immediate: true }
)

watch(
  () => page.props.errors,
  (errors) => notifyValidationErrors(errors),
  { deep: true }
)

onMounted(() => {
  removeExceptionListener = router.on('exception', () => {
    toast.error('Terjadi gangguan sistem. Silakan coba lagi.')
  })

  removeInvalidListener = router.on('invalid', () => {
    toast.error('Permintaan tidak dapat diproses.')
  })
})

onUnmounted(() => {
  removeExceptionListener?.()
  removeInvalidListener?.()
})
</script>

<template><span class="hidden" aria-hidden="true"></span></template>
