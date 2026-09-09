<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, nextTick } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'
import BusinessSummaryCards from './Dashboard/Components/BusinessSummaryCards.vue'
import SalesChartCard from './Dashboard/Components/SalesChartCard.vue'
import RecentActivitiesCard from './Dashboard/Components/RecentActivitiesCard.vue'
import UpcomingBillsCard from './Dashboard/Components/UpcomingBillsCard.vue'
import LowStockProductsCard from './Dashboard/Components/LowStockProductsCard.vue'

const props = defineProps({
  businessSummary: { type: Array, required: true },
  salesChartData: { type: Array, required: true },
  aktivitasTerbaru: { type: Array, required: true },
  produkStokMenipis: { type: Array, required: true },
  upcomingReminders: { type: Array, default: () => [] },
  dateFrom: { type: String, required: true },
  dateTo: { type: String, required: true },
})

const startDate = ref(props.dateFrom)
const endDate = ref(props.dateTo)

let searchTimeout = null
const performSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(
      route('dashboard'),
      { date_from: startDate.value, date_to: endDate.value },
      { preserveState: true, preserveScroll: true, replace: true }
    )
  }, 500)
}

const updateDate = async (type, value) => {
  if (type === 'from') startDate.value = value
  else endDate.value = value
  await nextTick()
  performSearch()
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Dashboard" />

    <div class="space-y-5">
      <!-- Header -->
      <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            Dashboard Finansial
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Ringkasan keuangan dan aktivitas bisnis Anda
          </p>
        </div>
        <div class="w-full sm:w-80 lg:w-96">
          <DateRangePicker
            :start="startDate"
            :end="endDate"
            @update:start="updateDate('from', $event)"
            @update:end="updateDate('to', $event)"
          />
        </div>
      </div>

      <!-- Metric Cards -->
      <BusinessSummaryCards :data="businessSummary" />

      <!-- Main Grid: 8 kolom kiri / 4 kolom kanan -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Kolom Kiri -->
        <div class="flex flex-col gap-6 lg:col-span-8">
          <SalesChartCard :data="salesChartData" />
          <RecentActivitiesCard :activities="aktivitasTerbaru" />
        </div>

        <!-- Kolom Kanan -->
        <div class="flex flex-col gap-6 lg:col-span-4">
          <UpcomingBillsCard :reminders="upcomingReminders" />
          <LowStockProductsCard :products="produkStokMenipis" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
