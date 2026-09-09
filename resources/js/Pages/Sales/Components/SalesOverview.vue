<script setup>
import { computed, ref } from 'vue'
import * as icons from 'lucide-vue-next'
import { usePage, router } from '@inertiajs/vue3'
import Badge from '@/Components/UI/Badge.vue'
import LineChart from '@/Components/UI/LineChart.vue'
import Skeleton from '@/Components/UI/Skeleton.vue'

const page = usePage()
const summary = computed(() => page.props.summary)
const chartData = computed(() => page.props.chartData ?? [])
const activities = computed(() => page.props.activities ?? [])
const topCustomers = computed(() => page.props.topCustomers ?? [])
const attentionDocs = computed(() => page.props.attentionDocs ?? [])
const overviewUrl = computed(() =>
  page.url.startsWith('/cashier/sales') ? route('cashier.sales') : route('sales.index')
)

const loading = computed(() => !summary.value)
const period = ref('Bulan')
const periods = ['Hari', 'Minggu', 'Bulan', 'Tahun']

const periodMap = { Hari: 'day', Minggu: 'week', Bulan: 'month', Tahun: 'year' }

const changePeriod = (p) => {
  period.value = p
  router.get(
    overviewUrl.value,
    { period: periodMap[p] },
    { preserveState: true, preserveScroll: true, only: ['chartData'] }
  )
}

const formatCurrency = (value) => {
  const num = Number(value || 0)
  if (num >= 1000000000) return `Rp ${(num / 1000000000).toFixed(1)} M`
  if (num >= 1000000) return `Rp ${(num / 1000000).toFixed(1)} jt`
  return `Rp ${num.toLocaleString('id-ID')}`
}

const formatCompact = (value) => {
  const num = Number(value || 0)
  if (num >= 1000000000) return `${(num / 1000000000).toFixed(1)} M`
  if (num >= 1000000) return `${(num / 1000000).toFixed(1)} jt`
  if (num >= 1000) return `${(num / 1000).toFixed(0)} rb`
  return String(num)
}

const summaryCards = computed(() => {
  const s = summary.value
  if (!s) return []
  return [
    {
      label: 'Total Penjualan Hari Ini',
      value: formatCurrency(s.today_revenue),
      caption: `${s.today_count} transaksi`,
      compare: `${s.today_compare >= 0 ? '+' : ''}${s.today_compare}% dari kemarin`,
      icon: 'CircleDollarSign',
      color: 'emerald',
    },
    {
      label: 'Omzet Bulan Ini',
      value: formatCurrency(s.month_revenue),
      caption: `${s.month_count} dokumen`,
      compare: `${s.month_compare >= 0 ? '+' : ''}${s.month_compare}% dari bulan lalu`,
      icon: 'TrendingUp',
      color: 'blue',
    },
    {
      label: 'Invoice Belum Dibayar',
      value: String(s.unpaid_count),
      caption: formatCurrency(s.unpaid_total),
      compare: 'Perlu ditagih',
      icon: 'Clock3',
      color: 'amber',
    },
    {
      label: 'Pelanggan Baru',
      value: String(s.new_customers),
      caption: 'Bulan ini',
      compare: 'Pelanggan aktif',
      icon: 'UserPlus',
      color: 'violet',
    },
    {
      label: 'Retur Penjualan',
      value: String(s.return_count),
      caption: formatCurrency(s.return_total),
      compare: 'Bulan ini',
      icon: 'Undo2',
      color: 'cyan',
    },
  ]
})

const colors = {
  emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300',
  blue: 'bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300',
  amber: 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300',
  red: 'bg-red-50 text-red-600 dark:bg-red-400/10 dark:text-red-300',
  violet: 'bg-violet-50 text-violet-600 dark:bg-violet-400/10 dark:text-violet-300',
  cyan: 'bg-cyan-50 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-300',
}
</script>

<template>
  <div class="space-y-5">
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
      <template v-if="loading">
        <div
          v-for="i in 5"
          :key="i"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
        >
          <Skeleton width="10" height="10" />
          <Skeleton width="full" height="3" class="mt-4" />
          <Skeleton width="16" height="6" class="mt-2" />
          <Skeleton width="12" height="3" class="mt-2" />
        </div>
      </template>
      <article
        v-for="item in summaryCards"
        v-else
        :key="item.label"
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ item.label }}</p>
            <p
              class="mt-2 truncate text-lg font-semibold tracking-tight text-slate-950 dark:text-white sm:text-xl"
            >
              {{ item.value ?? 0 }}
            </p>
          </div>

          <span :class="['grid h-10 w-10 place-items-center rounded-xl', colors[item.color]]">
            <component :is="icons[item.icon]" :size="20" />
          </span>
        </div>

        <p class="mt-1 text-xs text-slate-500">{{ item.caption }}</p>
        <p class="mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-300">
          {{ item.compare }}
        </p>
      </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,.8fr)]">
      <article
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <div class="flex flex-col justify-between gap-4 sm:flex-row">
          <div>
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Grafik Penjualan</h2>
            <p class="mt-1 text-sm text-slate-500">Tren omzet penjualan selama periode terpilih.</p>
          </div>
        </div>
        <div class="mt-5 flex gap-2">
          <button
            v-for="item in periods"
            :key="item"
            :class="[
              'rounded-lg px-3 py-1.5 text-xs font-medium',
              period === item
                ? 'bg-emerald-600 text-white dark:bg-emerald-400 dark:text-[#071426]'
                : 'bg-slate-100 text-slate-500 dark:bg-[#163354] dark:text-slate-300',
            ]"
            @click="changePeriod(item)"
          >
            {{ item }}
          </button>
        </div>
        <div class="mt-3 h-64 rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
          <LineChart
            v-if="chartData.length"
            :data="chartData"
            :height="300"
            :format-tooltip="(v) => `Rp ${Number(v).toLocaleString('id-ID')}`"
          />
          <div v-else class="flex h-full items-center justify-center text-sm text-slate-400">
            Tidak ada data.
          </div>
        </div>
      </article>

      <article
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">
          Dokumen Perlu Perhatian
        </h2>
        <p class="mt-1 text-sm text-slate-500">Tindakan yang perlu diprioritaskan.</p>
        <div class="mt-5 space-y-3">
          <template v-if="loading">
            <Skeleton v-for="i in 2" :key="i" width="full" height="16" />
          </template>
          <div
            v-for="(item, index) in attentionDocs"
            v-else
            :key="index"
            class="rounded-xl border border-slate-100 p-4 dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <div class="flex justify-between gap-2">
              <p class="text-sm font-medium text-slate-900 dark:text-white">{{ item.name }}</p>
              <Badge :variant="item.variant">{{ item.count }}</Badge>
            </div>
            <p class="mt-2 text-xs font-medium text-slate-500">{{ formatCurrency(item.value) }}</p>
          </div>
          <p v-if="!loading && !attentionDocs.length" class="text-sm text-slate-400">
            Tidak ada dokumen tertunda.
          </p>
        </div>
      </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1.4fr)_minmax(300px,.7fr)]">
      <article
        class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <div class="border-b border-slate-100 p-5 dark:border-[#29476b]">
          <h2 class="text-lg font-semibold text-slate-950 dark:text-white">
            Aktivitas Penjualan Terbaru
          </h2>
          <p class="mt-1 text-sm text-slate-500">5 transaksi terbaru.</p>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-[#29476b]">
          <template v-if="loading">
            <div v-for="i in 5" :key="i" class="flex items-center gap-4 p-4 sm:px-5">
              <Skeleton width="10" height="10" />
              <div class="flex-1 space-y-2">
                <Skeleton width="32" height="4" /><Skeleton width="20" height="3" />
              </div>
            </div>
          </template>
          <div
            v-for="item in activities"
            v-else
            :key="item.id"
            class="flex items-center gap-4 p-4 sm:px-5"
          >
            <span
              class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"
            >
              <component :is="icons[item.icon]" :size="19" />
            </span>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-slate-900 dark:text-white">
                {{ item.type }} · {{ item.number }}
              </p>
              <p class="mt-1 text-xs text-slate-500">{{ item.customer }} · {{ item.date }}</p>
            </div>
            <div class="hidden text-right sm:block">
              <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                {{ formatCurrency(item.amount) }}
              </p>
              <Badge :variant="item.variant">{{ item.status }}</Badge>
            </div>
          </div>
          <p v-if="!loading && !activities.length" class="p-5 text-sm text-slate-400">
            Belum ada aktivitas.
          </p>
        </div>
      </article>

      <div class="space-y-5">
        <article
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
        >
          <h2 class="font-semibold text-slate-950 dark:text-white">Top Customer</h2>
          <div class="mt-4 space-y-4">
            <template v-if="loading">
              <div v-for="i in 5" :key="i" class="flex gap-3">
                <Skeleton width="8" height="8" />
                <div class="flex-1 space-y-1">
                  <Skeleton width="24" height="4" /><Skeleton width="16" height="3" />
                </div>
              </div>
            </template>
            <div v-for="(item, index) in topCustomers" v-else :key="item.id" class="flex gap-3">
              <span
                class="grid h-8 w-8 place-items-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700"
                >{{ index + 1 }}</span
              >
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium dark:text-white">{{ item.name }}</p>
                <p class="text-xs text-slate-500">
                  {{ formatCompact(item.total_spent) }} · {{ item.transaction_count }} transaksi
                </p>
              </div>
            </div>
            <p v-if="!loading && !topCustomers.length" class="text-sm text-slate-400">
              Belum ada data pelanggan.
            </p>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>
