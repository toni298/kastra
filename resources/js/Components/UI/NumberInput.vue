<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="id"
      class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <input
        v-bind="attrs"
        :id="id"
        type="text"
        inputmode="numeric"
        pattern="[0-9]*"
        :value="displayValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="[
          'w-full rounded-xl border px-3.5 py-3 text-sm text-slate-900 outline-none transition duration-150 placeholder:text-slate-400',
          'focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10',
          'dark:bg-[#0a1b33] dark:text-white dark:placeholder:text-slate-500',
          error ? 'border-red-500' : 'border-slate-300 dark:border-[#29476b]',
          disabled ? 'cursor-not-allowed bg-slate-100 opacity-70' : 'bg-white',
        ]"
        @input="onInput"
        @blur="onBlur"
      />
      <span
        v-if="suffix"
        class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm font-medium text-slate-500"
      >
        {{ suffix }}
      </span>
    </div>

    <p v-if="error" class="mt-1.5 text-sm font-medium text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed, defineEmits, defineProps, useAttrs } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  id: { type: String, default: () => `number-input-${Math.random().toString(36).slice(2, 11)}` },
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  suffix: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])
const attrs = useAttrs()

const normalizeValue = (value) => {
  if (value === '' || value === null || value === undefined) {
    return ''
  }

  let normalized = String(value)
    .replace(/[^0-9.]/g, '')
    .replace(/\.+/g, '.')

  if (normalized === '.') {
    return '0.'
  }

  const parts = normalized.split('.')
  const integer = parts[0] === '' ? '0' : parts[0]
  const fraction = parts.length > 1 ? parts.slice(1).join('.') : ''

  const cleanedInteger = integer.replace(/^0+(\d)/, '$1')
  normalized = cleanedInteger === '' ? '0' : cleanedInteger

  if (fraction !== '') {
    normalized += `.${fraction}`
  }

  return normalized
}

const displayValue = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined) {
    return ''
  }

  return String(props.modelValue)
})

const onInput = (event) => {
  const rawValue = event.target.value
  const cleaned = normalizeValue(rawValue)
  event.target.value = cleaned
  emit('update:modelValue', cleaned)
}

const onBlur = (event) => {
  const value = String(event.target.value)
  if (value.endsWith('.')) {
    const cleaned = value.slice(0, -1)
    event.target.value = cleaned
    emit('update:modelValue', cleaned)
  }
}
</script>
