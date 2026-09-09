<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { BarChart3 } from 'lucide-vue-next'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from './AuthenticatedLayout.vue'
import CashierLayout from '@/Components/Cashier/CashierLayout.vue'

const props = defineProps({
  activeTab: { type: String, default: 'sales' },
  cashierLayout: { type: Boolean, default: false },
})

const tabs = [
  { id: 'sales', label: 'Penjualan', route: 'reports.sales', cashierRoute: 'cashier.reports' },
  {
    id: 'purchases',
    label: 'Pembelian',
    route: 'reports.purchases',
    cashierRoute: 'cashier.reports.purchases',
  },
  { id: 'stock', label: 'Stok', route: 'reports.stock', cashierRoute: 'cashier.reports.stock' },
  { id: 'finance', label: 'Keuangan', route: 'reports.finance', cashierRoute: 'cashier.reports.finance' },
]
</script>

<template>
  <Head title="Laporan & Analitik" />
  <component
    :is="props.cashierLayout ? CashierLayout : AuthenticatedLayout"
    :full-width="props.cashierLayout"
  >
    <template v-if="!props.cashierLayout" #header>Laporan & Analitik</template>
    <div class="space-y-5">
      <PageHeader
        title="Laporan & Analitik"
        description="Pantau performa penjualan dan aktivitas bisnis berdasarkan periode yang dipilih."
      >
        <template #icon><BarChart3 :size="22" /></template>
      </PageHeader>
      <nav
        class="border-b border-slate-200 dark:border-[#29476b]"
        aria-label="Navigasi laporan"
      >
        <div class="flex flex-wrap gap-x-7 gap-y-3 px-1" role="tablist">
          <Link
            v-for="tab in tabs"
            :key="tab.id"
            :href="route(props.cashierLayout ? tab.cashierRoute : tab.route)"
            :class="[
              'relative pb-3 text-sm font-medium transition',
              props.activeTab === tab.id
                ? 'text-emerald-700 dark:text-emerald-300'
                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400',
            ]"
            :aria-current="props.activeTab === tab.id ? 'page' : undefined"
          >
            {{ tab.label }}
            <span
              v-if="props.activeTab === tab.id"
              class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-emerald-600"
            ></span>
          </Link>
        </div>
      </nav>
      <slot></slot>
    </div>
  </component>
</template>
