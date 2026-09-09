<script setup>
import { computed, nextTick, ref } from 'vue'
import { ChevronLeft, ChevronRight } from '@lucide/vue'

const props = defineProps({
  id: { type: String, required: true },
  days: { type: Array, default: () => [] },
  weekdays: { type: Array, default: () => [] },
  months: { type: Array, default: () => [] },
  years: { type: Array, default: () => [] },
  month: { type: Number, default: 0 },
  year: { type: Number, default: new Date().getFullYear() },
  modelValue: { type: String, default: '' },
  panelStyle: { type: Object, default: () => ({}) },
  showClear: { type: Boolean, default: true },
  showToday: { type: Boolean, default: true },
})

const emit = defineEmits([
  'select',
  'clear',
  'today',
  'previous',
  'next',
  'update:month',
  'update:year',
])

const dayButtons = ref([])
const monthSelectId = computed(() => `${props.id}-month`)
const yearSelectId = computed(() => `${props.id}-year`)
const focusableDayKey = computed(() => {
  const selectedDay = props.days.find((day) => day.isSelected)
  const today = props.days.find((day) => day.isToday)
  const firstCurrentMonthDay = props.days.find((day) => day.isCurrentMonth)

  return (selectedDay ?? today ?? firstCurrentMonthDay)?.key ?? ''
})

const formatAriaDate = (date) =>
  new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(date)

const setDayButton = (element, index) => {
  if (element) dayButtons.value[index] = element
}

const focusDay = async (index) => {
  await nextTick()
  dayButtons.value[Math.max(0, Math.min(index, props.days.length - 1))]?.focus()
}

const handleDayKeydown = (event, index) => {
  const offsets = {
    ArrowLeft: -1,
    ArrowRight: 1,
    ArrowUp: -7,
    ArrowDown: 7,
  }

  let targetIndex = offsets[event.key] === undefined ? null : index + offsets[event.key]
  if (event.key === 'Home') targetIndex = index - (index % 7)
  if (event.key === 'End') targetIndex = index + (6 - (index % 7))
  if (targetIndex === null) return

  event.preventDefault()
  focusDay(targetIndex)
}
</script>

<template>
  <section
    :id="id"
    :style="panelStyle"
    class="fixed z-[110] w-[min(20rem,calc(100vw-1.5rem))] rounded-2xl border border-slate-200 bg-white p-3 shadow-2xl shadow-slate-900/20 dark:border-[#29476b] dark:bg-[#102542] dark:shadow-black/40"
    role="dialog"
    aria-label="Pilih tanggal"
    @click.stop
  >
    <div class="flex items-center gap-2">
      <button
        type="button"
        class="grid size-9 shrink-0 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/15 dark:text-slate-300 dark:hover:bg-[#163354] dark:hover:text-emerald-300"
        aria-label="Bulan sebelumnya"
        title="Bulan sebelumnya"
        @click="emit('previous')"
      >
        <ChevronLeft :size="18" aria-hidden="true" />
      </button>

      <div class="grid min-w-0 flex-1 grid-cols-[1fr_5.5rem] gap-2">
        <label class="sr-only" :for="monthSelectId">Pilih bulan</label>
        <select
          :id="monthSelectId"
          :value="month"
          class="min-w-0 rounded-xl border-slate-200 bg-slate-50 py-2 pl-3 pr-8 text-sm font-medium text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          @change="emit('update:month', Number($event.target.value))"
        >
          <option v-for="(monthName, index) in months" :key="monthName" :value="index">
            {{ monthName }}
          </option>
        </select>

        <label class="sr-only" :for="yearSelectId">Pilih tahun</label>
        <select
          :id="yearSelectId"
          :value="year"
          class="rounded-xl border-slate-200 bg-slate-50 py-2 pl-3 pr-8 text-sm font-medium text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          @change="emit('update:year', Number($event.target.value))"
        >
          <option v-for="yearOption in years" :key="yearOption" :value="yearOption">
            {{ yearOption }}
          </option>
        </select>
      </div>

      <button
        type="button"
        class="grid size-9 shrink-0 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/15 dark:text-slate-300 dark:hover:bg-[#163354] dark:hover:text-emerald-300"
        aria-label="Bulan berikutnya"
        title="Bulan berikutnya"
        @click="emit('next')"
      >
        <ChevronRight :size="18" aria-hidden="true" />
      </button>
    </div>

    <div class="mt-3 grid grid-cols-7" aria-hidden="true">
      <span
        v-for="weekday in weekdays"
        :key="weekday"
        class="py-1.5 text-center text-[12px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500"
      >
        {{ weekday }}
      </span>
    </div>

    <div class="grid grid-cols-7 gap-0.5" role="grid" aria-label="Kalender">
      <button
        v-for="(day, index) in days"
        :key="day.key"
        :ref="(element) => setDayButton(element, index)"
        type="button"
        :tabindex="day.key === focusableDayKey ? 0 : -1"
        :aria-label="formatAriaDate(day.date)"
        :aria-pressed="day.isSelected"
        :class="[
          'relative grid aspect-square place-items-center rounded-xl text-sm font-semibold outline-none transition',
          'focus:ring-4 focus:ring-emerald-500/20',
          day.isSelected
            ? 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700'
            : day.isCurrentMonth
              ? 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 dark:text-slate-200 dark:hover:bg-emerald-500/15 dark:hover:text-emerald-200'
              : 'text-slate-300 hover:bg-slate-50 dark:text-slate-600 dark:hover:bg-[#163354]',
          day.isToday && !day.isSelected
            ? 'ring-1 ring-inset ring-emerald-500 font-semibold text-emerald-700 dark:text-emerald-300'
            : '',
        ]"
        role="gridcell"
        @click="emit('select', day.date)"
        @keydown="handleDayKeydown($event, index)"
      >
        {{ day.label }}
      </button>
    </div>

    <div
      v-if="showToday || showClear"
      class="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-[#29476b]"
    >
      <button
        v-if="showClear"
        type="button"
        class="rounded-lg px-2 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-red-600 focus:outline-none focus:ring-4 focus:ring-red-500/10 disabled:cursor-not-allowed disabled:opacity-40 dark:text-slate-400 dark:hover:bg-[#163354] dark:hover:text-red-300"
        :disabled="!modelValue"
        @click="emit('clear')"
      >
        Hapus
      </button>
      <span v-else></span>
      <button
        v-if="showToday"
        type="button"
        class="rounded-lg px-2 py-1.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/15"
        @click="emit('today')"
      >
        Hari ini
      </button>
    </div>
  </section>
</template>
