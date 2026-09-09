<script setup>
import { computed, ref, toRef, useAttrs, useId, watch } from 'vue'
import { CalendarDays } from '@lucide/vue'
import { parseDateOnly, toDisplayDate, toIsoDate, useDatePicker } from '@/Composables/useDatePicker'
import { useDatePickerPopover } from '@/Composables/useDatePickerPopover'
import DatePickerCalendar from './DatePickerCalendar.vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  id: { type: String, default: '' },
  modelValue: { type: String, default: '' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: 'DD/MM/YYYY' },
  error: { type: String, default: '' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
  showClear: { type: Boolean, default: true },
  showToday: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue'])

const attrs = useAttrs()
const modelValue = toRef(props, 'modelValue')
const {
  calendarDays,
  changeMonth,
  changeViewMonth,
  changeViewYear,
  monthNames,
  setViewDate,
  today,
  weekdays,
  yearOptions,
  viewMonth,
  viewYear,
} = useDatePicker(modelValue)

const rootElement = ref(null)
const inputElement = ref(null)
const displayValue = ref('')
const internalError = ref('')
const generatedId = useId()

const inputId = computed(() => props.id || `date-picker-${generatedId}`)
const calendarId = computed(() => `${inputId.value}-calendar`)
const errorId = computed(() => `${inputId.value}-error`)
const validationError = computed(() => props.error || internalError.value)
const isInteractive = computed(() => !props.disabled && !props.readonly)
const {
  closePopover: closeCalendar,
  isOpen,
  openPopover,
  panelStyle,
} = useDatePickerPopover({
  rootElement,
  inputElement,
  calendarId,
})

const syncDisplayValue = (value) => {
  const parsedDate = parseDateOnly(value)
  displayValue.value = parsedDate ? toDisplayDate(parsedDate) : String(value ?? '')
}

const openCalendar = () => {
  if (!isInteractive.value || isOpen.value) return

  const selectedDate = parseDateOnly(props.modelValue)
  setViewDate(selectedDate ?? today)
  openPopover()
}

const toggleCalendar = () => {
  if (isOpen.value) {
    closeCalendar(true)
    return
  }

  openCalendar()
}

const commitDate = (date) => {
  internalError.value = ''
  displayValue.value = toDisplayDate(date)
  emit('update:modelValue', toIsoDate(date))
  setViewDate(date)
  closeCalendar(true)
}

const clearDate = () => {
  internalError.value = props.required ? 'Tanggal wajib diisi.' : ''
  displayValue.value = ''
  emit('update:modelValue', '')
  closeCalendar(true)
}

const formatTypedValue = (value) => {
  const digits = value.replace(/\D/g, '').slice(0, 8)
  if (digits.length <= 2) return digits
  if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`

  return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`
}

const handleInput = (event) => {
  displayValue.value = formatTypedValue(event.target.value)

  if (!displayValue.value) {
    internalError.value = ''
    emit('update:modelValue', '')
    return
  }

  const parsedDate = parseDateOnly(displayValue.value)
  if (!parsedDate) return

  internalError.value = ''
  emit('update:modelValue', toIsoDate(parsedDate))
  setViewDate(parsedDate)
}

const validateInput = () => {
  if (!displayValue.value) {
    internalError.value = props.required ? 'Tanggal wajib diisi.' : ''
    return
  }

  const parsedDate = parseDateOnly(displayValue.value)
  internalError.value = parsedDate ? '' : 'Gunakan format DD/MM/YYYY yang valid.'
}

watch(
  () => props.modelValue,
  (value) => {
    syncDisplayValue(value)
    if (parseDateOnly(value) || !value) internalError.value = ''
  },
  { immediate: true }
)

watch(
  () => [props.disabled, props.readonly],
  ([disabled, readonly]) => {
    if (disabled || readonly) closeCalendar()
  }
)
</script>

<template>
  <div ref="rootElement" class="">
    <label
      v-if="label"
      :for="inputId"
      class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
    >
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>

    <div data-date-picker-trigger class="relative">
      <input
        v-bind="attrs"
        :id="inputId"
        ref="inputElement"
        type="text"
        inputmode="numeric"
        maxlength="10"
        autocomplete="off"
        :value="displayValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :aria-invalid="Boolean(validationError)"
        :aria-describedby="validationError ? errorId : undefined"
        :aria-expanded="isOpen"
        :aria-controls="calendarId"
        aria-haspopup="dialog"
        :class="[
          'min-h-[46px] w-full rounded-xl border py-3 pl-3.5 pr-11 text-sm text-slate-900 outline-none transition duration-150 placeholder:text-slate-400',
          'focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10',
          'dark:bg-[#0a1b33] dark:text-white dark:placeholder:text-slate-500',
          validationError ? 'border-red-500' : 'border-slate-300 dark:border-[#29476b]',
          disabled ? 'cursor-not-allowed bg-slate-100 opacity-70' : 'bg-white',
          readonly && !disabled ? 'cursor-default bg-slate-50 dark:bg-[#0d2039]' : '',
        ]"
        @click="openCalendar"
        @keydown.down.prevent="openCalendar"
        @input="handleInput"
        @blur="validateInput"
      />

      <button
        type="button"
        class="absolute inset-y-0 right-0 grid w-11 place-items-center rounded-r-xl text-slate-500 transition hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500/30 disabled:cursor-not-allowed disabled:opacity-50 dark:text-slate-400 dark:hover:text-emerald-300"
        :disabled="!isInteractive"
        :aria-label="isOpen ? 'Tutup kalender' : 'Buka kalender'"
        :title="isOpen ? 'Tutup kalender' : 'Buka kalender'"
        @click="toggleCalendar"
      >
        <CalendarDays :size="18" aria-hidden="true" />
      </button>
    </div>

    <p v-if="validationError" :id="errorId" class="mt-1.5 text-sm font-medium text-red-500">
      {{ validationError }}
    </p>

    <Teleport to="body">
      <DatePickerCalendar
        v-if="isOpen"
        :id="calendarId"
        :days="calendarDays"
        :weekdays="weekdays"
        :months="monthNames"
        :years="yearOptions"
        :month="viewMonth"
        :year="viewYear"
        :model-value="modelValue"
        :panel-style="panelStyle"
        :show-clear="showClear"
        :show-today="showToday"
        @select="commitDate"
        @clear="clearDate"
        @today="commitDate(today)"
        @previous="changeMonth(-1)"
        @next="changeMonth(1)"
        @update:month="changeViewMonth"
        @update:year="changeViewYear"
      />
    </Teleport>
  </div>
</template>
