import { router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

export function useReminderCalendar(props, emit) {
  const viewMode = ref('bulan')
  const showMonthPicker = ref(false)
  const showFilter = ref(false)
  const selectedYear = ref(null)
  const filterType = ref('')
  const filterStatus = ref('')
  const isNavigating = ref(false)
  const navigationTimeout = ref(null)

  const selectedDate = ref(null)
  const selectedDateReminders = ref([])
  const showDayModal = ref(false)
  const showQuickForm = ref(false)
  const editingReminder = ref(null)
  const deleteReminder = ref(null)

  const tooltipDate = ref(null)
  const tooltipPosition = ref({ x: 0, y: 0 })
  const tooltipReminders = ref([])

  const isMounted = ref(false)
  const calendarRef = ref(null)

  const currentDate = computed(() => {
    const [year, month] = props.month.split('-').map(Number)
    return new Date(year, month - 1, 1)
  })

  const currentMonthLabel = computed(() => {
    return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(
      currentDate.value
    )
  })

  const navigateMonth = (direction) => {
    if (isNavigating.value) return
    isNavigating.value = true
    const newDate = new Date(currentDate.value)
    newDate.setMonth(newDate.getMonth() + direction)
    const newMonth = `${newDate.getFullYear()}-${String(newDate.getMonth() + 1).padStart(2, '0')}`
    if (navigationTimeout.value) clearTimeout(navigationTimeout.value)
    emit('month-change', newMonth)
    navigationTimeout.value = setTimeout(() => {
      isNavigating.value = false
    }, 300)
  }

  const goToToday = () => {
    const today = new Date()
    const newMonth = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`
    emit('month-change', newMonth)
  }

  const currentYear = computed(() => currentDate.value.getFullYear())
  const currentMonth = computed(() => currentDate.value.getMonth())

  const toggleMonthPicker = () => {
    if (!showMonthPicker.value) selectedYear.value = currentYear.value
    showMonthPicker.value = !showMonthPicker.value
  }

  const months = [
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
  ]

  const selectMonth = (monthIndex) => {
    const newMonth = `${selectedYear.value}-${String(monthIndex + 1).padStart(2, '0')}`
    emit('month-change', newMonth)
    showMonthPicker.value = false
  }

  const collectDayReminders = (calendar, dateStr) => {
    return calendar
      .getEvents()
      .filter((event) => {
        if (!event.startStr) return false
        const eventDateStr = event.startStr.split('T')[0]
        return eventDateStr === dateStr
      })
      .map((event) => {
        const p = event.extendedProps || {}
        return {
          id: event.id,
          title: event.title,
          name: event.title,
          start: event.startStr,
          due_date: event.startStr,
          end: event.endStr,
          type: p.type || 'once',
          status: p.status || 'pending',
          notes: p.notes,
          amount: p.amount ?? 0,
          type_transcation: p.type_transcation || 'out',
          color: p.color,
          all_day: p.all_day,
          branch: p.branch,
          id_reminder: p.id || event.id,
        }
      })
  }

  const handleDateClick = (info) => {
    const dateStr = info.dateStr
    const reminders = collectDayReminders(info.view.calendar, dateStr)
    selectedDate.value = new Date(info.date)
    selectedDateReminders.value = reminders
    showDayModal.value = true
  }

  const handleEventClick = (info) => {
    const event = info.event
    const properties = event.extendedProps || {}
    if (properties.status === 'completed') return

    selectedDate.value = event.start || new Date()
    editingReminder.value = {
      id: event.id,
      title: event.title,
      start: event.startStr,
      occurrence_start: event.startStr,
      end: event.endStr,
      all_day: event.allDay,
      ...properties,
    }
    showQuickForm.value = true
  }

  const handleAddReminder = () => {
    editingReminder.value = null
    showQuickForm.value = true
  }

  const handleEditReminder = (reminder) => {
    editingReminder.value = reminder
    showQuickForm.value = true
  }

  const handleDeleteReminder = (reminder) => {
    deleteReminder.value = reminder
  }

  const handleDeleteScope = ({ reminder, scope, occurrence_date }) => {
    deleteReminder.value = {
      ...reminder,
      deleteScope: scope,
      occurrence_start: reminder.occurrence_start || occurrence_date || reminder.start,
    }
  }

  const handleCompleteReminder = (reminder) => {
    router.post(
      route('reminders.complete', reminder.id_reminder || reminder.id),
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          showDayModal.value = false
          emit('saved')
        },
      }
    )
  }

  const handleSaved = () => {
    showQuickForm.value = false
    showDayModal.value = false
    editingReminder.value = null
    emit('saved')
  }

  const filter = (type, status) => {
    emit('filter', { type, status })
  }

  return {
    viewMode,
    showMonthPicker,
    showFilter,
    selectedYear,
    filterType,
    filterStatus,
    isNavigating,
    navigationTimeout,
    selectedDate,
    selectedDateReminders,
    showDayModal,
    showQuickForm,
    deleteReminder,
    editingReminder,
    tooltipDate,
    tooltipPosition,
    tooltipReminders,
    isMounted,
    calendarRef,
    currentDate,
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
    handleDeleteScope,
    handleCompleteReminder,
    handleSaved,
    filter,
  }
}
