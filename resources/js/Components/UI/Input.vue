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
        :type="inputType"
        :inputmode="inputMode"
        :pattern="props.type === 'number' ? '[0-9]*' : undefined"
        :value="displayValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="[
          'w-full rounded-xl border px-3.5 py-3 text-sm text-slate-900 outline-none transition duration-150 placeholder:text-slate-400',
          'focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10',
          'dark:bg-[#0a1b33] dark:text-white dark:placeholder:text-slate-500',
          isPassword && showPassword ? 'pr-12' : '',
          error ? 'border-red-500' : 'border-slate-300 dark:border-[#29476b]',
          disabled ? 'cursor-not-allowed bg-slate-100 opacity-70' : 'bg-white',
        ]"
        @input="onInput"
        @blur="onBlur"
      />
      <button
        v-if="isPassword && showPassword"
        type="button"
        class="absolute inset-y-0 right-0 grid w-11 place-items-center rounded-r-xl text-slate-500 transition hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:text-slate-400 dark:hover:text-emerald-300"
        :aria-label="passwordVisible ? 'Sembunyikan password' : 'Tampilkan password'"
        :title="passwordVisible ? 'Sembunyikan password' : 'Tampilkan password'"
        :disabled="disabled"
        @click="passwordVisible = !passwordVisible"
      >
        <EyeOff v-if="passwordVisible" :size="18" />
        <Eye v-else :size="18" />
      </button>
    </div>
    <p v-if="hint && !error" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
      {{ hint }}
    </p>
    <p v-if="error" class="mt-1.5 text-sm font-medium text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed, ref, useAttrs } from 'vue'
import { Eye, EyeOff } from '@lucide/vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  id: { type: String, default: () => `input-${Math.random().toString(36).slice(2, 11)}` },
  modelValue: { type: [String, Number], default: '' },
  type: { type: String, default: 'text' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  showPassword: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue'])

const attrs = useAttrs()
const passwordVisible = ref(false)
const isPassword = computed(() => props.type === 'password')
const inputType = computed(() => (isPassword.value && passwordVisible.value ? 'text' : props.type))
const inputMode = computed(() => (props.type === 'number' ? 'numeric' : undefined))

const normalizeNumberInput = (value) => {
  if (props.type !== 'number') {
    return value
  }

  if (value === '' || value === null || value === undefined) {
    return ''
  }

  let normalized = String(value).replace(/[^0-9.]/g, '')
  normalized = normalized.replace(/\.+/g, '.')

  if (normalized === '.') {
    return '0.'
  }

  const parts = normalized.split('.')
  const integerPart = parts[0] === '' ? '0' : parts[0]
  const cleanedInteger = integerPart.replace(/^0+(\d)/, '$1')
  normalized = cleanedInteger === '' ? '0' : cleanedInteger

  if (parts.length > 1) {
    normalized += `.${parts.slice(1).join('.')}`
  }

  return normalized
}

const displayValue = computed(() => {
  if (props.type === 'number') {
    return normalizeNumberInput(props.modelValue)
  }

  return props.modelValue
})

const onInput = (event) => {
  if (props.type === 'number') {
    const normalized = normalizeNumberInput(event.target.value)
    event.target.value = normalized
    emit('update:modelValue', normalized)
    return
  }

  emit('update:modelValue', event.target.value)
}

const onBlur = (event) => {
  if (props.type === 'number') {
    const value = String(event.target.value)
    if (value.endsWith('.')) {
      const cleaned = value.slice(0, -1)
      event.target.value = cleaned
      emit('update:modelValue', cleaned)
    }
  }
}
</script>
