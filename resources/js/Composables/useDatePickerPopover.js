import { nextTick, onBeforeUnmount, ref } from 'vue'
import {
  activateOverlayLayer,
  deactivateOverlayLayer,
  isTopOverlayLayer,
} from '@/Composables/useModalLayer'

export const useDatePickerPopover = ({ rootElement, inputElement, calendarId, maxPanelWidth = 320 }) => {
  const isOpen = ref(false)
  const panelStyle = ref({})
  const overlayToken = Symbol('date-picker')

  const updatePanelPosition = () => {
    if (!isOpen.value || typeof window === 'undefined') return

    const trigger = rootElement.value?.querySelector('[data-date-picker-trigger]')
    if (!trigger) return

    const triggerRect = trigger.getBoundingClientRect()
    const panel = document.getElementById(calendarId.value)
    const panelWidth = Math.min(maxPanelWidth, window.innerWidth - 24)
    const panelHeight = panel?.offsetHeight || 390
    const spaceBelow = window.innerHeight - triggerRect.bottom
    const openAbove = spaceBelow < panelHeight + 12 && triggerRect.top > spaceBelow
    const preferredTop = openAbove ? triggerRect.top - panelHeight - 8 : triggerRect.bottom + 8

    panelStyle.value = {
      left: `${Math.max(12, Math.min(triggerRect.left, window.innerWidth - panelWidth - 12))}px`,
      top: `${Math.max(12, Math.min(preferredTop, window.innerHeight - panelHeight - 12))}px`,
    }
  }

  const removeDocumentListeners = () => {
    if (typeof document === 'undefined') return

    document.removeEventListener('pointerdown', handleOutsidePointerDown, true)
    document.removeEventListener('keydown', handleDocumentKeydown, true)
    window.removeEventListener('resize', updatePanelPosition)
    window.removeEventListener('scroll', updatePanelPosition, true)
  }

  const closePopover = (restoreFocus = false) => {
    if (!isOpen.value) return

    isOpen.value = false
    deactivateOverlayLayer(overlayToken)
    removeDocumentListeners()

    if (restoreFocus) nextTick(() => inputElement.value?.focus({ preventScroll: true }))
  }

  const handleOutsidePointerDown = (event) => {
    const panel = document.getElementById(calendarId.value)
    const clickedInside = rootElement.value?.contains(event.target) || panel?.contains(event.target)

    if (!clickedInside) closePopover()
  }

  const handleDocumentKeydown = (event) => {
    if (event.key !== 'Escape' || !isOpen.value || !isTopOverlayLayer(overlayToken)) return

    event.preventDefault()
    event.stopImmediatePropagation()
    closePopover(true)
  }

  const addDocumentListeners = () => {
    document.addEventListener('pointerdown', handleOutsidePointerDown, true)
    document.addEventListener('keydown', handleDocumentKeydown, true)
    window.addEventListener('resize', updatePanelPosition)
    window.addEventListener('scroll', updatePanelPosition, true)
  }

  const openPopover = async () => {
    if (isOpen.value || typeof document === 'undefined') return

    isOpen.value = true
    activateOverlayLayer(overlayToken)
    addDocumentListeners()
    await nextTick()
    updatePanelPosition()
    await nextTick()
    updatePanelPosition()
  }

  onBeforeUnmount(() => {
    removeDocumentListeners()
    deactivateOverlayLayer(overlayToken)
  })

  return {
    closePopover,
    isOpen,
    openPopover,
    panelStyle,
  }
}
