<script setup>
import { computed, ref, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Bell, Plus } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CalendarDashboard from './Components/CalendarDashboard.vue'
import ReminderDeleteModal from './Components/ReminderDeleteModal.vue'
import ReminderQuickForm from './Components/ReminderQuickForm.vue'
import ReminderStatsCards from './Components/ReminderStatsCards.vue'
import ReminderTable from './Components/ReminderTable.vue'
import ReminderTableEditModal from './Components/ReminderTableEditModal.vue'
import CashBankTransactionForm from '../CashBank/Components/CashBankTransactionForm.vue'

const props = defineProps({
  reminders: { type: Object, default: () => ({ data: [] }) },
  calendarData: { type: Object, default: () => ({ month: '', events: [] }) },
  stats: { type: Object, required: true },
  filters: { type: Object, required: true },
  cashBankAccounts: { type: Object, default: () => ({ data: [] }) },
})

const page = usePage()
const search = ref(props.filters.search ?? '')
const activeTab = ref(props.filters.tab ?? 'semua')
const filterType = ref(props.filters.type ?? props.filters.transaction_type ?? '')
const filterStatus = ref(props.filters.filter_status ?? '')
const selected = ref(null)
const modal = ref(null)
const loading = ref(false)
const loadingMore = ref(false)
const paymentReminder = ref(null)
const accounts = computed(() =>
  Array.isArray(props.cashBankAccounts)
    ? props.cashBankAccounts
    : (props.cashBankAccounts?.data ?? [])
)

const can = (action) => page.props.auth.permissions?.includes(`reminders.${action}`)

const tabs = [
  { id: 'semua', label: 'Semua' },
  { id: 'jatuh_tempo', label: 'Jatuh Tempo' },
  { id: 'terjadwal', label: 'Terjadwal' },
  { id: 'selesai', label: 'Selesai' },
]

const open = (kind, reminder = null) => {
  selected.value = reminder
  modal.value = kind
}

const openQuickForm = (reminder = null) => {
  selected.value = reminder
  modal.value = 'quick-form'
}

const openPaymentForm = (reminder) => {
  paymentReminder.value = {
    ...reminder,
    type: reminder.transaction_type,
    amount_value: reminder.amount,
    isoDate: reminder.due_date?.slice(0, 10),
  }
  modal.value = 'payment'
}

const closePaymentForm = () => {
  paymentReminder.value = null
  modal.value = null
}

const completePaidReminder = () => {
  router.post(
    route('reminders.complete', paymentReminder.value.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        closePaymentForm()
        handleCalendarSaved()
      },
    }
  )
}

const visit = (url, data = {}) => {
  if (!url) return
  const isLoadMore = data.cursor !== undefined
  if (isLoadMore) {
    loadingMore.value = true
  } else {
    loading.value = true
  }

  router.visit(url, {
    preserveState: true,
    preserveScroll: true,
    data,
    replace: true,
    onFinish: () => {
      loading.value = false
      loadingMore.value = false
    },
  })
}

const handleRequest = ({ url, data }) => {
  visit(url, {
    tab: activeTab.value,
    search: search.value || undefined,
    ...data,
  })
}

const handleLoadMore = ({ url, data }) => {
  visit(url, { tab: activeTab.value, ...data })
}

const handleAction = ({ action, item }) => {
  if (action === 'pay') {
    openPaymentForm(item)
  } else if (action === 'edit' || action === 'detail') {
    open('table-edit', item)
  } else if (action === 'delete') {
    open('delete', item)
  }
}

const handleTableEditDelete = (reminder) => {
  open('delete', {
    ...reminder,
    deleteScope: 'this',
    occurrence_start: reminder.occurrence_start || reminder.due_date || reminder.start,
  })
}

const setTab = (tabId) => {
  activeTab.value = tabId
  visit(route('reminders.index'), {
    tab: tabId,
    search: search.value || undefined,
  })
}

const handleMonthChange = (month) => {
  visit(route('reminders.index'), {
    tab: 'semua',
    month,
    search: search.value || undefined,
    type: filterType.value || undefined,
    filter_status: filterStatus.value || undefined,
  })
}

const handleFilter = ({ type, status }) => {
  filterType.value = type
  filterStatus.value = status
  visit(route('reminders.index'), {
    tab: activeTab.value,
    month: props.filters.month,
    search: search.value || undefined,
    type: type || undefined,
    filter_status: status || undefined,
  })
}

const handleViewChange = (view) => {
  // View change is handled locally in CalendarDashboard
  // But we can track it if needed for backend
  console.log('View changed to:', view)
}

const handleCalendarSaved = () => {
  // Reload calendar data after save
  visit(route('reminders.index'), {
    tab: activeTab.value,
    month: props.filters.month,
    search: search.value || undefined,
    transaction_type: filterType.value || undefined,
    filter_status: filterStatus.value || undefined,
  })
}

watch(
  () => props.filters.search,
  (value) => {
    const nextSearch = value ?? ''
    if (nextSearch !== search.value) search.value = nextSearch
  }
)

watch(
  () => props.filters.tab,
  (value) => {
    if (value && value !== activeTab.value) activeTab.value = value
  }
)
</script>

<template>
  <Head title="Reminder" />
  <AuthenticatedLayout>
    <template #header>Reminder</template>

    <div class="space-y-5">
      <!-- Header -->
      <PageHeader
        title="Reminder"
        description="Kelola dan pantau semua pengingat pembayaran dan aktivitas penting bisnis Anda."
      >
        <template #icon><Bell :size="22" /></template>
        <template #actions>
          <Button v-if="can('create')" @click="openQuickForm()">
            <Plus :size="17" class="mr-2" />Tambah Reminder
          </Button>
        </template>
      </PageHeader>

      <!-- Tabs -->
      <div class="border-b border-slate-200 dark:border-[#29476b]">
        <nav class="-mb-px flex gap-6">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            class="relative border-b-2 px-1 pb-3 text-sm font-medium transition"
            :class="
              activeTab === tab.id
                ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400'
                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'
            "
            @click="setTab(tab.id)"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="space-y-5">
        <!-- Calendar View (Tab: Semua) — full width -->
        <template v-if="activeTab === 'semua'">
          <CalendarDashboard
            :month="calendarData.month || filters.month"
            :events="calendarData.events || []"
            :loading="loading"
            @month-change="handleMonthChange"
            @filter="handleFilter"
            @view-change="handleViewChange"
            @saved="handleCalendarSaved"
          />
        </template>

        <!-- Table View (Tabs: Jatuh Tempo, Terjadwal, Selesai) -->
        <template v-else>
          <ReminderTable
            :items="reminders"
            :loading="loading"
            :loading-more="loadingMore"
            :search="search"
            @request="handleRequest"
            @load-more="handleLoadMore"
            @action="handleAction"
          />
        </template>

        <!-- Stats Cards -->
        <ReminderStatsCards :stats="stats" />
      </div>
    </div>

    <!-- Modals -->
    <ReminderQuickForm
      v-if="modal === 'quick-form'"
      :date="selected?.due_date ? new Date(selected.due_date) : new Date()"
      :reminder="selected"
      @close="modal = null"
      @saved="handleCalendarSaved"
    />
    <ReminderTableEditModal
      v-if="modal === 'table-edit' && selected"
      :reminder="selected"
      @close="modal = null"
      @saved="handleCalendarSaved"
      @delete="handleTableEditDelete"
    />
    <ReminderDeleteModal
      v-if="modal === 'delete'"
      :reminder="selected"
      :initial-scope="selected?.deleteScope || 'this'"
      @close="modal = null"
      @deleted="handleCalendarSaved"
    />
    <CashBankTransactionForm
      v-if="modal === 'payment'"
      :initial-type="paymentReminder.type"
      :item="paymentReminder"
      :accounts="accounts"
      @close="closePaymentForm"
      @saved="completePaidReminder"
    />
  </AuthenticatedLayout>
</template>
