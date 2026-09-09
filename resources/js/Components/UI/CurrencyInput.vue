<script setup>
import { computed, useAttrs, useId } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  id: { type: String, default: '' },
  modelValue: { type: [Number, String], default: 0 },
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  placeholder: { type: String, default: '0' },
  name: { type: String, default: '' },
  min: { type: Number, default: 0 },
  max: { type: Number, default: Number.MAX_SAFE_INTEGER },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  currency: { type: String, default: 'IDR' },
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

const attrs = useAttrs()
const generatedId = useId()

const inputId = computed(() => props.id || `currency-${generatedId}`)
const errorId = computed(() => `${inputId.value}-error`)
const hintId = computed(() => `${inputId.value}-hint`)
const describedBy = computed(() => {
  if (props.error) return errorId.value
  if (props.hint) return hintId.value
  return undefined
})
const normalizedValue = computed(() => {
  const parsed = Number(props.modelValue)
  const value = Number.isFinite(parsed) ? Math.trunc(parsed) : props.min
  return Math.min(Math.max(value, props.min), props.max)
})
const display = computed(() =>
  normalizedValue.value === 0
    ? '0'
    : new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(normalizedValue.value)
)

const update = (event) => {
  const digits = event.target.value.replace(/\D/g, '')
  if (digits === '') {
    emit('update:modelValue', 0)
    return
  }
  const parsed = Number.parseInt(digits, 10)
  const safeValue = Number.isSafeInteger(parsed) ? parsed : props.min
  const value = Math.min(Math.max(safeValue, props.min), props.max)

  event.target.value = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value)
  emit('update:modelValue', value)
}
</script>

<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="inputId"
      class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <span
        class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-sm font-semibold text-slate-500 dark:text-slate-300"
        aria-hidden="true"
      >
        {{ currency }}
      </span>
      <input
        v-bind="attrs"
        :id="inputId"
        :name="name || undefined"
        :value="display"
        type="text"
        inputmode="numeric"
        pattern="[0-9.]*"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :aria-invalid="Boolean(error)"
        :aria-describedby="describedBy"
        :class="[
           'w-full rounded-xl border py-3 pl-14 pr-3.5 text-sm text-slate-900 outline-none transition duration-150 placeholder:text-slate-400',
          'focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10',
          'dark:bg-[#0a1b33] dark:text-white dark:placeholder:text-slate-500',
          error ? 'border-red-500' : 'border-slate-300 dark:border-[#29476b]',
          disabled ? 'cursor-not-allowed bg-slate-100 opacity-70' : 'bg-white',
        ]"
        @input="update"
        @blur="emit('blur', $event)"
        @focus="emit('focus', $event)"
      />
    </div>

    <p v-if="error" :id="errorId" class="mt-1.5 text-sm font-medium text-red-500">
      {{ error }}
    </p>
    <p v-else-if="hint" :id="hintId" class="mt-1.5 text-xs text-slate-500 dark:text-slate-300">
      {{ hint }}
    </p>
  </div>
</template>
