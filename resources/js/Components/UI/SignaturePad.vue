<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useId, watch } from 'vue'
import { Eraser } from '@lucide/vue'
import Button from './Button.vue'

const props = defineProps({
  id: { type: String, default: '' },
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Tanda tangan' },
  error: { type: String, default: '' },
  hint: { type: String, default: 'Gunakan mouse, sentuhan, atau stylus untuk menandatangani.' },
  name: { type: String, default: '' },
  height: { type: Number, default: 180 },
  lineWidth: { type: Number, default: 2 },
  lineColor: { type: String, default: '#0f172a' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change'])

const generatedId = useId()
const canvas = ref(null)
const drawing = ref(false)
const hasSignature = ref(Boolean(props.modelValue))
let resizeObserver = null

const inputId = computed(() => props.id || `signature-${generatedId}`)
const errorId = computed(() => `${inputId.value}-error`)
const hintId = computed(() => `${inputId.value}-hint`)
const describedBy = computed(() => {
  if (props.error) return errorId.value
  if (props.hint) return hintId.value
  return undefined
})

const context = () => canvas.value?.getContext('2d') ?? null

const configureContext = () => {
  const drawingContext = context()
  if (!drawingContext) return

  drawingContext.lineCap = 'round'
  drawingContext.lineJoin = 'round'
  drawingContext.lineWidth = props.lineWidth
  drawingContext.strokeStyle = props.lineColor
}

const drawValue = (value) => {
  const drawingContext = context()
  if (!drawingContext || !value) return

  const image = new window.Image()
  image.onload = () => {
    drawingContext.clearRect(0, 0, canvas.value.clientWidth, props.height)
    drawingContext.drawImage(image, 0, 0, canvas.value.clientWidth, props.height)
    configureContext()
  }
  image.src = value
}

const resizeCanvas = () => {
  if (!canvas.value) return

  const savedValue = hasSignature.value ? canvas.value.toDataURL('image/png') : props.modelValue
  const width = canvas.value.getBoundingClientRect().width
  const ratio = window.devicePixelRatio || 1

  canvas.value.width = Math.max(1, Math.round(width * ratio))
  canvas.value.height = Math.max(1, Math.round(props.height * ratio))

  const drawingContext = context()
  drawingContext.setTransform(ratio, 0, 0, ratio, 0, 0)
  configureContext()
  drawValue(savedValue)
}

const pointFromEvent = (event) => {
  const bounds = canvas.value.getBoundingClientRect()

  return {
    x: event.clientX - bounds.left,
    y: event.clientY - bounds.top,
  }
}

const startDrawing = (event) => {
  if (props.disabled || !canvas.value) return

  event.preventDefault()
  canvas.value.setPointerCapture(event.pointerId)
  drawing.value = true

  const point = pointFromEvent(event)
  const drawingContext = context()
  drawingContext.beginPath()
  drawingContext.moveTo(point.x, point.y)
}

const continueDrawing = (event) => {
  if (!drawing.value || props.disabled) return

  event.preventDefault()
  const point = pointFromEvent(event)
  const drawingContext = context()
  drawingContext.lineTo(point.x, point.y)
  drawingContext.stroke()
  hasSignature.value = true
}

const finishDrawing = (event) => {
  if (!drawing.value) return

  drawing.value = false
  if (canvas.value?.hasPointerCapture(event.pointerId)) {
    canvas.value.releasePointerCapture(event.pointerId)
  }

  if (!hasSignature.value) return

  const value = canvas.value.toDataURL('image/png')
  emit('update:modelValue', value)
  emit('change', value)
}

const clear = () => {
  if (props.disabled) return

  context()?.clearRect(0, 0, canvas.value.clientWidth, props.height)
  hasSignature.value = false
  emit('update:modelValue', '')
  emit('change', '')
}

watch(
  () => props.modelValue,
  (value) => {
    if (value === canvas.value?.toDataURL('image/png')) return

    hasSignature.value = Boolean(value)
    context()?.clearRect(0, 0, canvas.value?.clientWidth ?? 0, props.height)
    drawValue(value)
  }
)

watch(
  () => [props.height, props.lineWidth, props.lineColor],
  () => nextTick(resizeCanvas)
)

onMounted(() => {
  resizeCanvas()
  resizeObserver = new window.ResizeObserver(resizeCanvas)
  resizeObserver.observe(canvas.value)
})

onBeforeUnmount(() => resizeObserver?.disconnect())
</script>

<template>
  <div class="w-full">
    <div class="mb-1.5 flex items-center justify-between gap-3">
      <label :for="inputId" class="text-sm font-medium text-slate-700 dark:text-slate-200">
        {{ label }}
        <span v-if="required" class="text-red-500">*</span>
      </label>
      <Button
        v-if="hasSignature"
        type="button"
        variant="secondary"
        size="sm"
        :disabled="disabled"
        @click="clear"
      >
        <Eraser :size="15" class="mr-1.5" />
        Hapus
      </Button>
    </div>

    <canvas
      :id="inputId"
      ref="canvas"
      :style="{ height: `${height}px` }"
      :aria-label="label"
      :aria-invalid="Boolean(error)"
      :aria-describedby="describedBy"
      :aria-disabled="disabled"
      class="block w-full touch-none rounded-xl border bg-white shadow-inner outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:bg-slate-50"
      :class="[
        error ? 'border-red-500' : 'border-slate-300 dark:border-[#29476b]',
        disabled ? 'cursor-not-allowed opacity-60' : 'cursor-crosshair',
      ]"
      tabindex="0"
      @pointerdown="startDrawing"
      @pointermove="continueDrawing"
      @pointerup="finishDrawing"
      @pointercancel="finishDrawing"
      @pointerleave="finishDrawing"
    ></canvas>

    <input v-if="name" type="hidden" :name="name" :value="modelValue" />

    <p v-if="error" :id="errorId" class="mt-1.5 text-sm font-medium text-red-500">
      {{ error }}
    </p>
    <p v-else-if="hint" :id="hintId" class="mt-1.5 text-xs text-slate-500 dark:text-slate-300">
      {{ hint }}
    </p>
  </div>
</template>
