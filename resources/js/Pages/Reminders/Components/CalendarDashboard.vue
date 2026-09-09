<script setup>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import multimonthPlugin from '@fullcalendar/multimonth'
import interactionPlugin from '@fullcalendar/interaction'
import rrulePlugin from '@fullcalendar/rrule'
import { router } from '@inertiajs/vue3'
import {
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Filter,
  LayoutList,
  Loader2,
} from 'lucide-vue-next'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import Button from '@/Components/UI/Button.vue'
import DayRemindersModal from './DayRemindersModal.vue'
import ReminderDeleteModal from './ReminderDeleteModal.vue'
import RecurringDropConfirmModal from './RecurringDropConfirmModal.vue'
import ReminderQuickForm from './ReminderQuickForm.vue'
import { useReminderCalendar } from '../Composables/useReminderCalendar'

const props = defineProps({
  month: { type: String, required: true },
  events: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['month-change', 'filter', 'view-change', 'saved'])

const {
  viewMode,
  showMonthPicker,
  showFilter,
  selectedYear,
  filterType,
  filterStatus,
  isNavigating,
  selectedDate,
  selectedDateReminders,
  showDayModal,
  showQuickForm,
  editingReminder,
  deleteReminder,
  calendarRef,
  currentMonthLabel,
  navigateMonth,
  goToToday,
  currentYear,
  currentMonth,
  toggleMonthPicker,
  months,
  selectMonth,
  handleDateClick,
  handleEventClick,
  handleAddReminder,
  handleEditReminder,
  handleDeleteReminder,
  handleCompleteReminder,
  handleSaved,
  filter,
} = useReminderCalendar(props, emit)

const formatDateLocal = (date) => {
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const viewOptions = [
  { value: 'bulan', label: 'Bulan' },
  { value: 'minggu', label: 'Minggu' },
  { value: 'hari', label: 'Hari' },
  { value: 'agenda', label: 'Agenda' },
]

const setViewMode = (mode) => {
  viewMode.value = mode
  const calendar = calendarRef.value?.getApi()
  const map = {
    bulan: 'dayGridMonth',
    minggu: 'timeGridWeek',
    hari: 'timeGridDay',
    agenda: 'listMonth',
  }
  calendar?.changeView(map[mode])
  emit('view-change', mode)
}

const todayString = formatDateLocal(new Date())
const pendingDrop = ref(null)

const calendarOptions = computed(() => ({
  plugins: [
    dayGridPlugin,
    timeGridPlugin,
    listPlugin,
    multimonthPlugin,
    interactionPlugin,
    rrulePlugin,
  ],
  initialView: 'dayGridMonth',
  initialDate: `${props.month}-01`,
  locale: 'id',
  firstDay: 1,
  headerToolbar: false,
  events: props.events,
  dateClick: handleDateClick,
  eventClick: handleEventClick,
  dayMaxEvents: 3,
  moreLinkClick: handleDateClick,
  eventDisplay: 'block',
  displayEventTime: false,
  height: 'auto',
  contentHeight: 'auto',
  fixedWeekCount: false,
  showNonCurrentDates: true,
  nowIndicator: true,
  slotMinTime: '07:00:00',
  slotMaxTime: '20:00:00',
  editable: true,
  eventAllow: (_dropInfo, draggedEvent) => draggedEvent?.extendedProps?.status !== 'completed',
  selectable: true,
  selectMirror: true,
  dragScroll: true,
  dayCellDidMount: (info) => {
    const cellDate = formatDateLocal(info.date)
    if (cellDate === todayString) info.el.classList.add('fc-day-today-custom')
  },
  eventDidMount: (info) => {
    const p = info.event.extendedProps || {}
    const el = info.el
    el.classList.add('reminder-event')
    el.classList.add(p.type_transcation === 'in' ? 'reminder-in' : 'reminder-out')
    el.classList.add(p.status === 'completed' ? 'reminder-completed' : 'reminder-pending')
    if (p.status === 'completed') el.classList.add('reminder-completed')
    else if (p.status === 'cancelled') el.classList.add('reminder-cancelled')
  },
  eventDrop: (info) => handleEventDrop(info),
  eventResize: (info) => handleEventResize(info),
}))

const handleEventDrop = (info) => {
  const p = info.event.extendedProps || {}
  if (p.status === 'completed') {
    info.revert()
    return
  }

  const isRecurring = Boolean(p.rrule) || ['daily', 'weekly', 'monthly', 'yearly'].includes(p.type)
  const drop = {
    id: p.id || info.event.id,
    title: info.event.title,
    originalStart: info.oldEvent?.start?.toISOString() || info.event.start?.toISOString(),
    targetStart: info.event.start?.toISOString(),
    targetEnd: info.event.end?.toISOString() || null,
    allDay: info.event.allDay,
    status: p.status || 'pending',
    type: p.type || 'once',
    type_transcation: p.type_transcation || 'out',
    amount: p.amount || 0,
    color: p.color || info.event.backgroundColor,
    notes: p.notes || '',
    rrule: p.rrule || null,
  }

  if (isRecurring) {
    info.revert()
    pendingDrop.value = { ...drop, revert: info.revert }
    return
  }

  submitDrop(drop, 'all')
}

const submitDrop = (drop, scope) => {
  router.put(
    route('reminders.update', drop.id),
    {
      title: drop.title,
      start: drop.targetStart,
      end: drop.targetEnd,
      all_day: drop.allDay,
      status: drop.status,
      type: drop.type,
      type_transcation: drop.type_transcation,
      amount: Number.isFinite(Number(drop.amount)) ? Number(drop.amount) : 0,
      color: drop.color,
      notes: drop.notes,
      scope,
      occurrence_start: drop.originalStart,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        pendingDrop.value = null
        emit('saved')
      },
      onError: () => {
        pendingDrop.value = null
      },
    }
  )
}

const cancelPendingDrop = () => {
  pendingDrop.value = null
}

const applyPendingDrop = (scope) => {
  if (!pendingDrop.value) return
  submitDrop(pendingDrop.value, scope)
}

const handleEventResize = (info) => {
  const p = info.event.extendedProps || {}
  if (p.status === 'completed') {
    info.revert()
    return
  }

  router.put(
    route('reminders.update', p.id || info.event.id),
    {
      title: info.event.title,
      start: info.event.start ? info.event.start.toISOString() : null,
      end: info.event.end ? info.event.end.toISOString() : null,
      all_day: info.event.allDay,
      status: p.status || 'pending',
      type: p.type || 'once',
      type_transcation: p.type_transcation || 'out',
      amount: p.amount || 0,
      color: p.color || info.event.backgroundColor,
      notes: p.notes || '',
    },
    {
      preserveScroll: true,
      onSuccess: () => emit('saved'),
      onError: () => info.revert(),
    }
  )
}

const resetFilters = () => {
  filterType.value = ''
  filterStatus.value = ''
  filter('', '')
}

const openAgendaView = () => {
  viewMode.value = 'agenda'
  setViewMode('agenda')
}

const closeDeleteModal = () => {
  deleteReminder.value = null
}

const handleDeleteCompleted = () => {
  showDayModal.value = false
  closeDeleteModal()
  handleSaved()
}

const closeMonthPicker = (e) => {
  if (showMonthPicker.value && !e.target.closest('.month-picker-container'))
    showMonthPicker.value = false
}
onMounted(() => document.addEventListener('click', closeMonthPicker))
onUnmounted(() => document.removeEventListener('click', closeMonthPicker))

watch(
  () => props.month,
  (newMonth, oldMonth) => {
    if (newMonth !== oldMonth) calendarRef.value?.getApi().gotoDate(`${newMonth}-01`)
  }
)
</script>

<template>
  <div class="rounded-2xl border border-slate-200 bg-white dark:border-[#1e3a5f] dark:bg-[#0d2137]">
    <div
      class="flex flex-col gap-4 border-b border-slate-200 p-4 dark:border-[#1e3a5f] sm:flex-row sm:items-center sm:justify-between"
    >
      <div class="flex items-center gap-2">
        <Button variant="secondary" size="sm" :disabled="isNavigating" @click="navigateMonth(-1)">
          <ChevronLeft :size="18" />
        </Button>
        <div class="relative month-picker-container">
          <button
            class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-lg font-semibold text-slate-900 transition hover:bg-slate-100 dark:text-white dark:hover:bg-[#163354]"
            @click.stop="toggleMonthPicker"
          >
            {{ currentMonthLabel }}
            <ChevronDown :size="16" class="text-slate-400" />
          </button>
          <div
            v-if="showMonthPicker"
            class="absolute left-0 top-full z-50 mt-1 w-64 rounded-xl border border-slate-200 bg-white p-3 shadow-lg dark:border-[#29476b] dark:bg-[#0d2137]"
            @click.stop
          >
            <div class="mb-2 flex items-center justify-between">
              <button
                class="rounded p-1 hover:bg-slate-100 dark:hover:bg-[#163354]"
                @click="selectedYear = currentYear - 1"
              >
                <ChevronLeft :size="16" />
              </button>
              <span class="text-sm font-semibold text-slate-900 dark:text-white">{{
                selectedYear ?? currentYear
              }}</span>
              <button
                class="rounded p-1 hover:bg-slate-100 dark:hover:bg-[#163354]"
                @click="selectedYear = currentYear + 1"
              >
                <ChevronRight :size="16" />
              </button>
            </div>
            <div class="grid grid-cols-3 gap-1">
              <button
                v-for="(monthName, index) in months"
                :key="monthName"
                class="rounded-lg px-2 py-1.5 text-xs transition"
                :class="
                  currentMonth === index && currentYear === (selectedYear ?? currentYear)
                    ? 'bg-emerald-500 font-semibold text-white'
                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#163354]'
                "
                @click="selectMonth(index)"
              >
                {{ monthName.slice(0, 3) }}
              </button>
            </div>
          </div>
        </div>
        <Button variant="secondary" size="sm" :disabled="isNavigating" @click="navigateMonth(1)">
          <ChevronRight :size="18" />
        </Button>
        <Button variant="outline" size="sm" @click="goToToday">Hari Ini</Button>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <div
          class="flex rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <button
            v-for="opt in viewOptions"
            :key="opt.value"
            class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
            :class="
              viewMode === opt.value
                ? 'bg-white text-slate-900 shadow-sm dark:bg-[#163354] dark:text-white'
                : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'
            "
            @click="setViewMode(opt.value)"
          >
            {{ opt.label }}
          </button>
        </div>
        <button
          class="relative grid size-9 place-items-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-[#29476b] dark:text-slate-300 dark:hover:bg-[#163354]"
          aria-label="Filter"
          @click="showFilter = !showFilter"
        >
          <Filter :size="17" />
          <span
            v-if="filterType || filterStatus"
            class="absolute -right-1 -top-1 size-2.5 rounded-full bg-emerald-500"
          ></span>
        </button>
      </div>
    </div>

    <div
      v-if="showFilter"
      class="flex flex-col gap-3 border-b border-slate-200 p-4 dark:border-[#1e3a5f] sm:flex-row sm:items-center"
    >
      <select
        v-model="filterType"
        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white sm:w-48"
        @change="filter(filterType, filterStatus)"
      >
        <option value="">Semua Tipe</option>
        <option value="once">Sekali</option>
        <option value="daily">Harian</option>
        <option value="weekly">Mingguan</option>
        <option value="monthly">Bulanan</option>
        <option value="yearly">Tahunan</option>
      </select>
      <select
        v-model="filterStatus"
        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white sm:w-48"
        @change="filter(filterType, filterStatus)"
      >
        <option value="">Semua Status</option>
        <option value="pending">Menunggu</option>
        <option value="completed">Selesai</option>
        <option value="cancelled">Dibatalkan</option>
      </select>
      <Button variant="ghost" size="sm" @click="resetFilters">Reset</Button>
    </div>

    <div class="relative" :class="loading ? 'opacity-60' : ''">
      <div
        v-if="loading"
        class="absolute inset-0 z-20 grid place-items-center bg-white/60 dark:bg-[#0d2137]/60"
      >
        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-300">
          <Loader2 :size="18" class="animate-spin" />Memuat data...
        </div>
      </div>
      <div
        class="grid grid-cols-3 gap-3 border-b border-slate-200 p-3 text-xs text-slate-500 dark:border-[#1e3a5f]"
      >
        <span class="flex items-center gap-1.5"
          ><span class="size-2 rounded-full bg-emerald-500"></span>Penerimaan</span
        >
        <span class="flex items-center gap-1.5"
          ><span class="size-2 rounded-full bg-red-500"></span>Pengeluaran</span
        >
        <span class="flex items-center gap-1.5"
          ><span class="size-2 rounded-full bg-slate-400"></span>Dibatalkan</span
        >
      </div>
      <FullCalendar ref="calendarRef" :options="calendarOptions" />
    </div>

    <div
      class="flex items-center justify-between gap-2 border-t border-slate-200 p-3 text-xs text-slate-500 dark:border-[#1e3a5f]"
    >
      <span
        >Tips: seret acara untuk mengubah tanggal, atau klik tanggal untuk tambah reminder.</span
      >
      <button
        class="flex items-center gap-1 rounded-lg px-2 py-1 font-medium text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-500/10"
        @click="openAgendaView"
      >
        <LayoutList :size="14" />Lihat Agenda
      </button>
    </div>

    <DayRemindersModal
      v-if="showDayModal && selectedDate"
      :date="selectedDate"
      :reminders="selectedDateReminders"
      @close="showDayModal = false"
      @add="handleAddReminder"
      @edit="handleEditReminder"
      @delete="handleDeleteReminder"
      @complete="handleCompleteReminder"
    />
    <ReminderQuickForm
      v-if="showQuickForm && selectedDate"
      :date="selectedDate"
      :reminder="editingReminder"
      @close="showQuickForm = false"
      @saved="handleSaved"
      @delete="handleDeleteReminder"
    />
    <ReminderDeleteModal
      v-if="deleteReminder"
      :reminder="deleteReminder"
      :initial-scope="deleteReminder.deleteScope || 'this'"
      @close="closeDeleteModal"
      @deleted="handleDeleteCompleted"
    />
    <RecurringDropConfirmModal
      v-if="pendingDrop"
      :drop="pendingDrop"
      @cancel="cancelPendingDrop"
      @apply="applyPendingDrop"
    />
  </div>
</template>

<style scoped>
:deep(.fc-daygrid-day-top) {
  justify-content: center;
}
:deep(.fc-daygrid-day-number) {
  float: none;
  padding: 8px 0;
}
.fc .fc-day-today-custom {
  background: rgba(16, 185, 129, 0.08) !important;
}
:deep(.reminder-event) {
  border: 1px solid transparent;
  border-left-width: 4px;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 6px;
  cursor: grab;
}
:deep(.reminder-in) {
  background: #dcfce7 !important;
  border-color: #16a34a !important;
  border-left-color: #059669 !important;
}
:deep(.reminder-out) {
  background: #fee2e2 !important;
  border-color: #ef4444 !important;
  border-left-color: #dc2626 !important;
}
:deep(.reminder-in .fc-event-main),
:deep(.reminder-in .fc-event-title) {
  color: #166534 !important;
}
:deep(.reminder-out .fc-event-main),
:deep(.reminder-out .fc-event-title) {
  color: #991b1b !important;
}
:deep(.reminder-completed) {
  opacity: 0.5;
  text-decoration: line-through;
}
:deep(.reminder-cancelled) {
  opacity: 0.4;
  text-decoration: line-through;
}
:deep(.fc .fc-timegrid-now-indicator-line) {
  border-color: #dc2626;
}
:deep(.fc .fc-timegrid-now-indicator-arrow) {
  border-color: #dc2626;
}
</style>
