<script setup>
import { computed, ref, toRef, useId, watch } from 'vue'
import { CalendarDays, ChevronLeft, ChevronRight } from '@lucide/vue'
import { parseDateOnly, toDisplayDate, toIsoDate, useDatePicker } from '@/Composables/useDatePicker'
import { useDatePickerPopover } from '@/Composables/useDatePickerPopover'

const props = defineProps({
  modelValue: { type: Array, default: undefined },
  start: { type: String, default: '' },
  end: { type: String, default: '' },
  label: { type: String, default: 'Rentang Tanggal' },
  placeholder: { type: String, default: 'DD/MM/YYYY - DD/MM/YYYY' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'update:start', 'update:end'])

const useSeparateModels = computed(() => props.modelValue === undefined)

const startISO = computed(() =>
  useSeparateModels.value ? props.start : (props.modelValue?.[0] ?? '')
)
const endISO = computed(() => (useSeparateModels.value ? props.end : (props.modelValue?.[1] ?? '')))

const emitRange = (start, end) => {
  if (useSeparateModels.value) {
    emit('update:start', start)
    emit('update:end', end)
  } else {
    emit('update:modelValue', [start, end])
  }
}

const startDate = toRef(props, 'start')
const {
  calendarDays: leftCalendarDays,
  changeMonth: changeLeftMonth,
  changeViewMonth: changeLeftViewMonth,
  changeViewYear: changeLeftViewYear,
  monthNames,
  setViewDate: setLeftViewDate,
  today,
  weekdays,
  yearOptions,
  viewMonth: leftViewMonth,
  viewYear: leftViewYear,
} = useDatePicker(startDate)

const rightViewDate = ref(new Date(leftViewYear.value, leftViewMonth.value + 1, 1))
const rightViewYear = computed(() => rightViewDate.value.getFullYear())
const rightViewMonth = computed(() => rightViewDate.value.getMonth())

const rootElement = ref(null)
const inputElement = ref(null)
const generatedId = useId()
const inputId = computed(() => `date-range-picker-${generatedId}`)
const calendarId = computed(() => `${inputId.value}-calendar`)
const {
  closePopover: closeCalendar,
  isOpen,
  openPopover,
  panelStyle,
} = useDatePickerPopover({
  rootElement,
  inputElement,
  calendarId,
  maxPanelWidth: 640,
})

const displayValue = computed(() => {
  const start = parseDateOnly(startISO.value)
  const end = parseDateOnly(endISO.value)
  if (!start && !end) return ''
  return `${start ? toDisplayDate(start) : '...'} - ${end ? toDisplayDate(end) : '...'}`
})

const rightCalendarDays = computed(() => {
  const firstDay = new Date(rightViewYear.value, rightViewMonth.value, 1).getDay()
  return Array.from({ length: 42 }, (_, index) => {
    const date = new Date(
      rightViewYear.value,
      rightViewMonth.value,
      index - firstDay + 1,
      12,
      0,
      0,
      0
    )
    const iso = toIsoDate(date)
    return {
      date,
      key: iso,
      label: date.getDate(),
      isCurrentMonth: date.getMonth() === rightViewMonth.value,
      isToday: iso === toIsoDate(today),
      isStart: iso === startISO.value,
      isEnd: iso === endISO.value,
      isInRange: startISO.value && endISO.value && iso > startISO.value && iso < endISO.value,
    }
  })
})

const leftCalendarDaysHighlighted = computed(() =>
  leftCalendarDays.value.map((day) => ({
    ...day,
    isStart: day.key === startISO.value,
    isEnd: day.key === endISO.value,
    isInRange: startISO.value && endISO.value && day.key > startISO.value && day.key < endISO.value,
  }))
)

const selectDate = (date) => {
  const iso = toIsoDate(date)
  if (!startISO.value || (startISO.value && endISO.value)) {
    emitRange(iso, '')
    return
  }
  if (iso < startISO.value) {
    emitRange(iso, startISO.value)
    return
  }
  if (iso === startISO.value) {
    emitRange('', '')
    return
  }
  emitRange(startISO.value, iso)
}

const changeRightMonth = (offset) => {
  rightViewDate.value = new Date(rightViewYear.value, rightViewMonth.value + offset, 1)
}

const changeRightViewMonth = (month) => {
  rightViewDate.value = new Date(rightViewYear.value, Number(month), 1)
}

const changeRightViewYear = (year) => {
  rightViewDate.value = new Date(Number(year), rightViewMonth.value, 1)
}

const changeLeftMonthSynced = (offset) => {
  changeLeftMonth(offset)
  rightViewDate.value = new Date(leftViewYear.value, leftViewMonth.value + 1, 1)
}

const changeLeftViewMonthSynced = (month) => {
  changeLeftViewMonth(month)
  rightViewDate.value = new Date(leftViewYear.value, leftViewMonth.value + 1, 1)
}

const changeLeftViewYearSynced = (year) => {
  changeLeftViewYear(year)
  rightViewDate.value = new Date(leftViewYear.value, leftViewMonth.value + 1, 1)
}

const openCalendar = () => {
  if (props.disabled || isOpen.value) return
  const selectedStart = parseDateOnly(startISO.value)
  setLeftViewDate(selectedStart ?? today)
  rightViewDate.value = new Date(leftViewYear.value, leftViewMonth.value + 1, 1)
  openPopover()
}

const toggleCalendar = () => {
  if (isOpen.value) {
    closeCalendar(true)
    return
  }
  openCalendar()
}

const clearRange = () => {
  emitRange('', '')
}

watch(
  () => [props.disabled],
  ([disabled]) => {
    if (disabled) closeCalendar()
  }
)
</script>

<template>
  <div ref="rootElement">
    <label
      v-if="label"
      :for="inputId"
      class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
    >
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>

    <div data-date-picker-trigger class="relative">
      <input
        :id="inputId"
        ref="inputElement"
        type="text"
        readonly
        autocomplete="off"
        :value="displayValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :aria-expanded="isOpen"
        :aria-controls="calendarId"
        aria-haspopup="dialog"
        :class="[
          'min-h-[46px] w-full cursor-pointer rounded-xl border py-3 pl-3.5 pr-11 text-sm text-slate-900 outline-none transition duration-150 placeholder:text-slate-400',
          'focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10',
          'dark:bg-[#0a1b33] dark:text-white dark:placeholder:text-slate-500',
          'border-slate-300 dark:border-[#29476b]',
          disabled ? 'cursor-not-allowed bg-slate-100 opacity-70' : 'bg-white',
        ]"
        @click="openCalendar"
        @keydown.down.prevent="openCalendar"
      />

      <button
        type="button"
        class="absolute inset-y-0 right-0 grid w-11 place-items-center rounded-r-xl text-slate-500 transition hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500/30 disabled:cursor-not-allowed disabled:opacity-50 dark:text-slate-400 dark:hover:text-emerald-300"
        :disabled="disabled"
        :aria-label="isOpen ? 'Tutup kalender' : 'Buka kalender'"
        :title="isOpen ? 'Tutup kalender' : 'Buka kalender'"
        @click="toggleCalendar"
      >
        <CalendarDays :size="18" aria-hidden="true" />
      </button>
    </div>

    <Teleport to="body">
      <section
        v-if="isOpen"
        :id="calendarId"
        data-date-range-picker-popover
        :style="panelStyle"
        class="fixed z-[130] w-[min(40rem,calc(100vw-1.5rem))] rounded-2xl border border-slate-200 bg-white p-3 shadow-2xl shadow-slate-900/20 dark:border-[#29476b] dark:bg-[#102542] dark:shadow-black/40"
        role="dialog"
        aria-label="Pilih rentang tanggal"
        @click.stop
      >
        <div class="grid grid-cols-2 gap-3">
          <!-- Left Calendar -->
          <div>
            <div class="mb-2 flex items-center gap-1">
              <button
                type="button"
                class="grid size-7 shrink-0 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-emerald-700 dark:text-slate-300 dark:hover:bg-[#163354] dark:hover:text-emerald-300"
                aria-label="Bulan sebelumnya"
                @click="changeLeftMonthSynced(-1)"
              >
                <ChevronLeft :size="16" aria-hidden="true" />
              </button>
              <select
                :value="leftViewMonth"
                class="min-w-0 flex-1 rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-2 pr-6 text-xs font-medium text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
                @change="changeLeftViewMonthSynced(Number($event.target.value))"
              >
                <option v-for="(monthName, index) in monthNames" :key="monthName" :value="index">
                  {{ monthName }}
                </option>
              </select>
              <select
                :value="leftViewYear"
                class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-2 pr-6 text-xs font-medium text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
                @change="changeLeftViewYearSynced(Number($event.target.value))"
              >
                <option v-for="yearOption in yearOptions" :key="yearOption" :value="yearOption">
                  {{ yearOption }}
                </option>
              </select>
            </div>
            <div class="grid grid-cols-7" aria-hidden="true">
              <span
                v-for="weekday in weekdays"
                :key="weekday"
                class="py-1 text-center text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500"
              >
                {{ weekday }}
              </span>
            </div>
            <div class="grid grid-cols-7 gap-0.5">
              <button
                v-for="day in leftCalendarDaysHighlighted"
                :key="day.key"
                type="button"
                :class="[
                  'grid aspect-square place-items-center rounded-lg text-xs font-medium outline-none transition',
                  'focus:ring-2 focus:ring-emerald-500/20',
                  day.isStart || day.isEnd
                    ? 'bg-emerald-600 text-white shadow-sm'
                    : day.isInRange
                      ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200'
                      : day.isCurrentMonth
                        ? 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 dark:text-slate-200 dark:hover:bg-emerald-500/15'
                        : 'text-slate-300 dark:text-slate-600',
                  day.isToday && !day.isStart && !day.isEnd
                    ? 'ring-1 ring-inset ring-emerald-500'
                    : '',
                ]"
                @click="selectDate(day.date)"
              >
                {{ day.label }}
              </button>
            </div>
          </div>

          <!-- Right Calendar -->
          <div>
            <div class="mb-2 flex items-center gap-1">
              <select
                :value="rightViewMonth"
                class="min-w-0 flex-1 rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-2 pr-6 text-xs font-medium text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
                @change="changeRightViewMonth(Number($event.target.value))"
              >
                <option v-for="(monthName, index) in monthNames" :key="monthName" :value="index">
                  {{ monthName }}
                </option>
              </select>
              <select
                :value="rightViewYear"
                class="rounded-lg border-slate-200 bg-slate-50 py-1.5 pl-2 pr-6 text-xs font-medium text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
                @change="changeRightViewYear(Number($event.target.value))"
              >
                <option v-for="yearOption in yearOptions" :key="yearOption" :value="yearOption">
                  {{ yearOption }}
                </option>
              </select>
              <button
                type="button"
                class="grid size-7 shrink-0 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-emerald-700 dark:text-slate-300 dark:hover:bg-[#163354] dark:hover:text-emerald-300"
                aria-label="Bulan berikutnya"
                @click="changeRightMonth(1)"
              >
                <ChevronRight :size="16" aria-hidden="true" />
              </button>
            </div>
            <div class="grid grid-cols-7" aria-hidden="true">
              <span
                v-for="weekday in weekdays"
                :key="weekday"
                class="py-1 text-center text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500"
              >
                {{ weekday }}
              </span>
            </div>
            <div class="grid grid-cols-7 gap-0.5">
              <button
                v-for="day in rightCalendarDays"
                :key="day.key"
                type="button"
                :class="[
                  'grid aspect-square place-items-center rounded-lg text-xs font-medium outline-none transition',
                  'focus:ring-2 focus:ring-emerald-500/20',
                  day.isStart || day.isEnd
                    ? 'bg-emerald-600 text-white shadow-sm'
                    : day.isInRange
                      ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-200'
                      : day.isCurrentMonth
                        ? 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 dark:text-slate-200 dark:hover:bg-emerald-500/15'
                        : 'text-slate-300 dark:text-slate-600',
                  day.isToday && !day.isStart && !day.isEnd
                    ? 'ring-1 ring-inset ring-emerald-500'
                    : '',
                ]"
                @click="selectDate(day.date)"
              >
                {{ day.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div
          class="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-[#29476b]"
        >
          <button
            type="button"
            class="rounded-lg px-2 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-red-600 focus:outline-none focus:ring-4 focus:ring-red-500/10 disabled:cursor-not-allowed disabled:opacity-40 dark:text-slate-400 dark:hover:bg-[#163354] dark:hover:text-red-300"
            :disabled="!startISO && !endISO"
            @click="clearRange"
          >
            Hapus
          </button>
          <span class="text-sm font-medium text-slate-600 dark:text-slate-300">
            {{ displayValue || 'Pilih rentang tanggal' }}
          </span>
          <button
            type="button"
            class="rounded-lg bg-emerald-600 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/20"
            :disabled="!startISO || !endISO"
            @click="closeCalendar(true)"
          >
            Terapkan
          </button>
        </div>
      </section>
    </Teleport>
  </div>
</template>
