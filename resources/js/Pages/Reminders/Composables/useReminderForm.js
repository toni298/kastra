import { useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const formatDateOnly = (date) => {
  const value = String(date)
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value

  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export function handleUpdateThisAndFuture(originalEvent, selectedDate, formInput) {
  const selected = new Date(selectedDate)
  const cutOff = new Date(selected)
  cutOff.setHours(0, 0, 0, 0)
  cutOff.setDate(cutOff.getDate() - 1)
  cutOff.setHours(23, 59, 59, 999)

  return {
    cutOffDate: cutOff.toISOString(),
    updatePayload: {
      scope: 'future',
      occurrence_start: selected.toISOString(),
      until: cutOff.toISOString(),
      rrule: originalEvent?.rrule ?? originalEvent?.extendedProps?.rrule ?? null,
    },
    createPayload: {
      ...formInput,
      start: formInput.start,
      end: formInput.end || null,
      type: formInput.type || 'once',
    },
  }
}

export function useReminderForm(props, emit) {
  const editing = computed(() => Boolean(props.reminder?.id))
  const isRecurring = computed(() => ['daily', 'weekly', 'monthly', 'yearly'].includes(form.type))

  const initialDueDate = computed(() => {
    if (props.reminder?.occurrence_start) {
      return formatDateOnly(props.reminder.occurrence_start)
    }
    if (props.reminder?.start) return formatDateOnly(props.reminder.start)
    if (props.reminder?.due_date) return formatDateOnly(props.reminder.due_date)
    return formatDateOnly(props.date)
  })

  const form = useForm({
    title: props.reminder?.title ?? props.reminder?.name ?? '',
    start: initialDueDate.value,
    end: props.reminder?.end ? formatDateOnly(props.reminder.end) : '',
    all_day: props.reminder?.all_day ?? true,
    type: props.reminder?.type ?? 'once',
    type_transcation: props.reminder?.type_transcation ?? 'out',
    amount: props.reminder?.amount ?? 0,
    recurrence_end_mode: 'forever',
    recurrence_until: '',
    recurrence_count: null,
    status: props.reminder?.status ?? 'pending',
    color: props.reminder?.color ?? '#3b82f6',
    notes: props.reminder?.notes ?? '',
  })

  const showRecurrenceEnd = ref(false)
  const scope = ref('all')

  const transactionTypes = [
    { value: 'in', label: 'Penerimaan' },
    { value: 'out', label: 'Pengeluaran' },
  ]

  const frequencyTypes = [
    { value: 'once', label: 'Satu Kali', description: 'Hanya pada tanggal yang dipilih' },
    { value: 'daily', label: 'Setiap Hari', description: 'Berulang setiap hari' },
    {
      value: 'weekly',
      label: 'Setiap Minggu',
      description: 'Berulang setiap minggu pada hari yang sama',
    },
    {
      value: 'monthly',
      label: 'Setiap Bulan',
      description: 'Berulang setiap bulan pada tanggal yang sama',
    },
    {
      value: 'yearly',
      label: 'Setiap Tahun',
      description: 'Berulang setiap tahun pada tanggal yang sama',
    },
  ]

  const frequencyLabel = computed(() => {
    const freq = frequencyTypes.find((f) => f.value === props.reminder?.type)
    return freq?.label ?? props.reminder?.type
  })

  const previewDates = computed(() => {
    if (editing.value || form.type === 'once') return []
    const dates = []
    const startDate = new Date(form.start)
    if (isNaN(startDate.getTime())) return []
    let current = new Date(startDate)
    for (let i = 0; i < 3; i++) {
      dates.push(new Date(current))
      switch (form.type) {
        case 'daily':
          current.setDate(current.getDate() + 1)
          break
        case 'weekly':
          current.setDate(current.getDate() + 7)
          break
        case 'monthly':
          current.setMonth(current.getMonth() + 1)
          break
        case 'yearly':
          current.setFullYear(current.getFullYear() + 1)
          break
      }
    }
    return dates
  })

  const formatPreviewDate = (date) =>
    new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(
      date
    )

  const submit = () => {
    const options = {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
        emit('close')
      },
    }

    const payload = {
      title: form.title,
      start: form.start,
      end: form.end || null,
      all_day: form.all_day,
      type: form.type,
      type_transcation: form.type_transcation,
      amount: form.amount,
      recurrence_end_mode: form.recurrence_end_mode,
      recurrence_until: form.recurrence_until || null,
      recurrence_count: form.recurrence_count || null,
      status: form.status,
      color: form.color,
      notes: form.notes,
    }

    if (editing.value) {
      const splitPayload =
        scope.value === 'future'
          ? handleUpdateThisAndFuture(
              props.reminder,
              props.reminder.occurrence_start || props.reminder.start,
              payload
            )
          : null
      const updatePayload = splitPayload
        ? { ...payload, ...splitPayload.updatePayload, create: splitPayload.createPayload }
        : {
            ...payload,
            scope: scope.value,
            occurrence_start: props.reminder.occurrence_start || props.reminder.start,
          }

      form.transform(() => updatePayload).put(route('reminders.update', props.reminder.id), options)
      return
    }

    form.transform(() => payload).post(route('reminders.store'), options)
  }

  watch(
    () => form.type,
    (newType) => {
      if (newType === 'once') {
        form.end = ''
        form.recurrence_until = ''
        form.recurrence_count = null
        form.recurrence_end_mode = 'forever'
        showRecurrenceEnd.value = false
      }
    }
  )

  const modalTitle = computed(() => {
    if (editing.value) return isRecurring.value ? 'Edit Event Berulang' : 'Edit Event'
    return 'Tambah Reminder'
  })

  const modalDescription = computed(() => {
    if (editing.value) {
      return 'Perbarui informasi event'
    }
    return `Reminder untuk ${new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(props.date)}`
  })

  return {
    form,
    editing,
    isRecurring,
    showRecurrenceEnd,
    scope,
    frequencyTypes,
    transactionTypes,
    frequencyLabel,
    previewDates,
    formatPreviewDate,
    submit,
    modalTitle,
    modalDescription,
  }
}
