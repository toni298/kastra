<script setup>
import { ref } from 'vue'
import * as icons from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import AccountingLayout from '@/Layouts/AccountingLayout.vue'
import CashierLayout from '@/Components/Cashier/CashierLayout.vue'
import ReportPreviewModal from '@/Pages/AccountingDashboard/Components/ReportPreviewModal.vue'
import { reports } from '@/Pages/AccountingDashboard/Data/accountingData'

const selected = ref(null)
const props = defineProps({ cashierLayout: { type: Boolean, default: false } })

const preview = (report) => {
  selected.value = report
}
const close = () => {
  selected.value = null
}
</script>

<template>
  <component
    :is="props.cashierLayout ? CashierLayout : AccountingLayout"
    active-tab="reports"
    :full-width="props.cashierLayout"
  >
    <div>
      <div class="mb-5">
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Laporan Keuangan</h2>
        <p class="mt-1 text-sm text-slate-500">
          Laporan terbentuk otomatis dari aktivitas bisnis Anda.
        </p>
      </div>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="report in reports"
          :key="report.name"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
        >
          <span
            class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"
            ><component :is="icons[report.icon]" :size="22"
          /></span>
          <h3 class="mt-4 font-semibold text-slate-950 dark:text-white">{{ report.name }}</h3>
          <p class="mt-2 min-h-[44px] text-sm leading-6 text-slate-500">{{ report.description }}</p>
          <Button class="mt-5" variant="secondary" size="sm" @click="preview(report)">
            Lihat Laporan
          </Button>
        </article>
      </div>
    </div>
  </component>

  <ReportPreviewModal v-if="selected" :report="selected" @close="close" />
</template>
