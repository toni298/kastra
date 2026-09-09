import { computed, ref } from 'vue'

const MONTH_NAMES = Object.freeze([
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember',
])

const WEEKDAYS = Object.freeze(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'])

const padNumber = (value) => String(value).padStart(2, '0')

const createLocalDate = (year, month, day) => new Date(year, month, day, 12, 0, 0, 0)

const createStrictLocalDate = (year, month, day) => {
  const date = createLocalDate(year, month, day)

  if (date.getFullYear() !== year || date.getMonth() !== month || date.getDate() !== day) {
    return null
  }

  return date
}

export const parseDateOnly = (value) => {
  if (value instanceof Date && !Number.isNaN(value.getTime())) {
    return createLocalDate(value.getFullYear(), value.getMonth(), value.getDate())
  }

  if (typeof value !== 'string') return null

  const normalizedValue = value.trim()
  const isoMatch = normalizedValue.match(/^(\d{4})-(\d{2})-(\d{2})$/)
  const displayMatch = normalizedValue.match(/^(\d{2})\/(\d{2})\/(\d{4})$/)
  const parts = isoMatch
    ? [Number(isoMatch[1]), Number(isoMatch[2]) - 1, Number(isoMatch[3])]
    : displayMatch
      ? [Number(displayMatch[3]), Number(displayMatch[2]) - 1, Number(displayMatch[1])]
      : null

  return parts ? createStrictLocalDate(...parts) : null
}

export const toIsoDate = (date) => {
  if (!date) return ''

  return `${date.getFullYear()}-${padNumber(date.getMonth() + 1)}-${padNumber(date.getDate())}`
}

export const toDisplayDate = (date) => {
  if (!date) return ''

  return `${padNumber(date.getDate())}/${padNumber(date.getMonth() + 1)}/${date.getFullYear()}`
}

export const useDatePicker = (modelValue) => {
  const today = createLocalDate(
    new Date().getFullYear(),
    new Date().getMonth(),
    new Date().getDate()
  )
  const initialDate = parseDateOnly(modelValue.value)
  const viewDate = ref(initialDate ?? today)

  const viewYear = computed(() => viewDate.value.getFullYear())
  const viewMonth = computed(() => viewDate.value.getMonth())
  const monthLabel = computed(() => MONTH_NAMES[viewMonth.value])
  const yearOptions = computed(() =>
    Array.from({ length: 201 }, (_, index) => viewYear.value - 100 + index)
  )

  const calendarDays = computed(() => {
    const firstDay = new Date(viewYear.value, viewMonth.value, 1).getDay()

    return Array.from({ length: 42 }, (_, index) => {
      const date = createLocalDate(viewYear.value, viewMonth.value, index - firstDay + 1)

      return {
        date,
        key: toIsoDate(date),
        label: date.getDate(),
        isCurrentMonth: date.getMonth() === viewMonth.value,
        isToday: toIsoDate(date) === toIsoDate(today),
        isSelected: toIsoDate(date) === toIsoDate(parseDateOnly(modelValue.value)),
      }
    })
  })

  const setViewDate = (date) => {
    if (date) viewDate.value = createLocalDate(date.getFullYear(), date.getMonth(), 1)
  }

  const changeMonth = (offset) => {
    viewDate.value = createLocalDate(viewYear.value, viewMonth.value + offset, 1)
  }

  const changeViewMonth = (month) => {
    viewDate.value = createLocalDate(viewYear.value, Number(month), 1)
  }

  const changeViewYear = (year) => {
    viewDate.value = createLocalDate(Number(year), viewMonth.value, 1)
  }

  return {
    calendarDays,
    changeMonth,
    changeViewMonth,
    changeViewYear,
    monthLabel,
    monthNames: MONTH_NAMES,
    setViewDate,
    today,
    weekdays: WEEKDAYS,
    yearOptions,
    viewMonth,
    viewYear,
  }
}
