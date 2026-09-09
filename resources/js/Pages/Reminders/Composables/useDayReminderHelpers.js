import { computed } from 'vue'
import { Bell, Building2, CircleDollarSign, Droplets, Receipt, Wifi, Zap } from 'lucide-vue-next'

export function useDayReminderHelpers(props, emit) {
  const formattedDate = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    }).format(props.date)
  )

  const shortFormattedDate = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
      weekday: 'short',
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    }).format(props.date)
  )

  const isPastDue = (reminder) => {
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const dueDate = new Date(reminder.due_date)
    dueDate.setHours(0, 0, 0, 0)
    return dueDate < today
  }

  const canEditDelete = (reminder) => reminder.status !== 'completed'

  const canComplete = (reminder) => reminder.status === 'pending'

  const isRecurring = (reminder) => ['daily', 'weekly', 'monthly', 'yearly'].includes(reminder.type)

  const getIcon = (reminder) => {
    const name = (reminder.title || reminder.name || '').toLowerCase()
    if (name.includes('wifi') || name.includes('internet')) return Wifi
    if (name.includes('listrik') || name.includes('pln')) return Zap
    if (name.includes('sewa') || name.includes('gudang') || name.includes('kantor'))
      return Building2
    if (name.includes('pdam') || name.includes('air')) return Droplets
    if (name.includes('pajak') || name.includes('tax')) return Receipt
    if (reminder.type_transcation === 'in') return CircleDollarSign
    return Bell
  }

  const getIconColor = (reminder) => {
    if (reminder.status === 'completed')
      return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
    if (reminder.status === 'cancelled')
      return 'bg-slate-50 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400'
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const dueDate = new Date(reminder.due_date)
    dueDate.setHours(0, 0, 0, 0)
    if (dueDate.getTime() === today.getTime())
      return 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400'
    if (dueDate < today)
      return 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400'
    return 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'
  }

  const getStatusBadge = (reminder) => {
    if (reminder.status === 'completed') return { variant: 'success', label: 'Selesai' }
    if (reminder.status === 'cancelled') return { variant: 'secondary', label: 'Dibatalkan' }
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const dueDate = new Date(reminder.due_date)
    dueDate.setHours(0, 0, 0, 0)
    if (dueDate.getTime() === today.getTime())
      return { variant: 'error', label: 'Jatuh tempo hari ini' }
    if (dueDate < today) return { variant: 'error', label: 'Terlambat' }
    if (dueDate <= new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000))
      return { variant: 'warning', label: 'Akan jatuh tempo' }
    return { variant: 'info', label: 'Terjadwal' }
  }

  const formatCurrency = (amount) => {
    if (!amount) return '-'
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(amount)
  }

  const formatTime = (value) => {
    if (!value) return ''
    return new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' }).format(
      new Date(`1970-01-01T${value}`)
    )
  }

  const getTypeLabel = (type) => {
    const labels = {
      once: 'Satu Kali',
      daily: 'Setiap Hari',
      weekly: 'Setiap Minggu',
      monthly: 'Setiap Bulan',
      yearly: 'Setiap Tahun',
    }
    return labels[type] ?? type
  }

  const getCategoryLabel = (category) => {
    const labels = { keuangan: 'Keuangan', operasional: 'Operasional', pribadi: 'Pribadi' }
    return labels[category] ?? category
  }

  const completeReminder = (reminder) => {
    if (reminder.is_virtual) emit('complete', { ...reminder, mark_completed: true })
    else emit('complete', reminder)
  }

  const handleEdit = (reminder) => {
    emit('edit', {
      ...reminder,
      occurrence_start: reminder.occurrence_start || reminder.start || reminder.due_date,
    })
  }

  const handleDelete = (reminder) => {
    emit('delete', reminder)
  }

  return {
    formattedDate,
    shortFormattedDate,
    isPastDue,
    canEditDelete,
    canComplete,
    isRecurring,
    getIcon,
    getIconColor,
    getStatusBadge,
    formatCurrency,
    formatTime,
    getTypeLabel,
    getCategoryLabel,
    completeReminder,
    handleEdit,
    handleDelete,
  }
}
