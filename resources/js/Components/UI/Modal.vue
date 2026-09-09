<script setup>
import { computed, nextTick, onBeforeUnmount, ref, useId, watch } from 'vue'
import {
  activateOverlayLayer,
  deactivateOverlayLayer,
  isTopOverlayLayer,
  lockOverlayBodyScroll,
  unlockOverlayBodyScroll,
} from '@/Composables/useModalLayer'
import Icon from './Icon.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  size: { type: String, default: 'md' },
  zIndexClass: { type: String, default: 'z-50' },
  closeOnOverlay: { type: Boolean, default: true },
  closeOnEscape: { type: Boolean, default: true },
  lockScroll: { type: Boolean, default: true },
  trapFocus: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue'])

const dialog = ref(null)
const previouslyFocusedElement = ref(null)
const titleId = `modal-title-${useId()}`
const descriptionId = `modal-description-${useId()}`
let ownsBodyLock = false
let listensForKeydown = false
const modalToken = Symbol('modal')

const sizeClasses = computed(
  () =>
    ({
      sm: 'max-w-sm',
      md: 'max-w-md',
      lg: 'max-w-lg',
      xl: 'max-w-xl',
      return: 'max-w-[47rem]',
      purchase: 'max-w-5xl',
      full: 'max-w-4xl',
      screen: 'max-w-7xl',
    })[props.size] ?? 'max-w-lg'
)

const focusableSelector = [
  'a[href]',
  'button:not([disabled])',
  'input:not([disabled]):not([type="hidden"])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  '[tabindex]:not([tabindex="-1"])',
].join(',')

const getFocusableElements = () =>
  Array.from(dialog.value?.querySelectorAll(focusableSelector) ?? []).filter(
    (element) => element.getAttribute('aria-hidden') !== 'true'
  )

const requestClose = () => {
  if (props.modelValue) {
    emit('update:modelValue', false)
  }
}

const handleOverlayClick = (event) => {
  event.stopPropagation()

  if (props.closeOnOverlay) {
    requestClose()
  }
}

const trapTabKey = (event) => {
  if (!props.trapFocus || !dialog.value) return

  const focusableElements = getFocusableElements()
  const firstElement = focusableElements[0]
  const lastElement = focusableElements.at(-1)
  const activeElement = document.activeElement

  if (!firstElement) {
    event.preventDefault()
    dialog.value.focus({ preventScroll: true })
    return
  }

  if (event.shiftKey && (activeElement === firstElement || !dialog.value.contains(activeElement))) {
    event.preventDefault()
    lastElement.focus()
    return
  }

  if (!event.shiftKey && (activeElement === lastElement || !dialog.value.contains(activeElement))) {
    event.preventDefault()
    firstElement.focus()
  }
}

const handleKeydown = (event) => {
  if (!props.modelValue || !isTopOverlayLayer(modalToken)) return

  if (event.key === 'Escape') {
    if (event.target?.closest?.('[data-async-select-open="true"]')) return

    event.preventDefault()
    event.stopImmediatePropagation()

    if (props.closeOnEscape) {
      requestClose()
    }

    return
  }

  if (event.key === 'Tab') {
    trapTabKey(event)
  }
}

const addKeydownListener = () => {
  if (listensForKeydown) return

  document.addEventListener('keydown', handleKeydown, true)
  listensForKeydown = true
}

const removeKeydownListener = () => {
  if (!listensForKeydown) return

  document.removeEventListener('keydown', handleKeydown, true)
  listensForKeydown = false
}

const activateModal = async () => {
  if (typeof document === 'undefined') return

  const activeElement = document.activeElement
  previouslyFocusedElement.value =
    activeElement && typeof activeElement.focus === 'function' ? activeElement : null

  if (props.lockScroll && !ownsBodyLock) {
    lockOverlayBodyScroll()
    ownsBodyLock = true
  }

  activateOverlayLayer(modalToken)
  addKeydownListener()
  await nextTick()

  if (!props.modelValue || !dialog.value) return

  const [firstElement] = getFocusableElements()
  ;(firstElement ?? dialog.value).focus({ preventScroll: true })
}

const deactivateModal = () => {
  removeKeydownListener()
  deactivateOverlayLayer(modalToken)

  if (ownsBodyLock) {
    unlockOverlayBodyScroll()
    ownsBodyLock = false
  }

  const elementToRestore = previouslyFocusedElement.value
  previouslyFocusedElement.value = null

  nextTick(() => {
    if (elementToRestore?.isConnected) {
      elementToRestore.focus({ preventScroll: true })
    }
  })
}

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      activateModal()
      return
    }

    deactivateModal()
  },
  { immediate: true }
)

onBeforeUnmount(() => {
  removeKeydownListener()
  deactivateOverlayLayer(modalToken)

  if (ownsBodyLock) {
    unlockOverlayBodyScroll()
    ownsBodyLock = false
  }

  const elementToRestore = previouslyFocusedElement.value
  previouslyFocusedElement.value = null

  nextTick(() => {
    if (elementToRestore?.isConnected) {
      elementToRestore.focus({ preventScroll: true })
    }
  })
})
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="modelValue"
        :class="zIndexClass"
        class="fixed inset-0 isolate overflow-y-auto"
        data-ui-modal
      >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" aria-hidden="true"></div>
        <div
          class="relative z-10 flex min-h-full items-center justify-center px-3 py-4 text-center sm:px-4 sm:py-6"
          @click.self="handleOverlayClick"
        >
          <section
            ref="dialog"
            :class="sizeClasses"
            class="relative w-full transform overflow-hidden rounded-2xl border border-slate-200 bg-white text-left shadow-2xl transition-all focus:outline-none dark:border-[#29476b] dark:bg-[#102542]"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="title ? titleId : undefined"
            :aria-describedby="description ? descriptionId : undefined"
            tabindex="-1"
            @click.stop
          >
            <div
              v-if="title"
              class="border-b border-slate-100 px-5 py-5 pr-14 dark:border-[#29476b] sm:px-6 sm:pr-16"
            >
              <h3 :id="titleId" class="text-lg font-semibold text-slate-950 dark:text-white">
                {{ title }}
              </h3>
              <p
                v-if="description"
                :id="descriptionId"
                class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300"
              >
                {{ description }}
              </p>
            </div>
            <button
              type="button"
              class="absolute right-5 top-5 z-20 rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-300/30 dark:hover:bg-[#163354] dark:hover:text-white"
              aria-label="Tutup modal"
              @click.stop="requestClose"
            >
              <Icon name="x" :size="20" />
            </button>
            <div class="px-5 py-5 sm:px-6"><slot></slot></div>
            <div
              v-if="$slots.footer"
              class="flex flex-wrap justify-end gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4 dark:border-[#29476b] dark:bg-[#0d2039] sm:px-6"
            >
              <slot name="footer"></slot>
            </div>
          </section>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
