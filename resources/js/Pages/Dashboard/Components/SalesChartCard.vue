<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import {
  CategoryScale,
  Chart as ChartJS,
  Filler,
  Legend,
  LinearScale,
  LineElement,
  PointElement,
  Tooltip,
} from 'chart.js'
import { Line } from 'vue-chartjs'
import { useRoute } from '../../../../../vendor/tightenco/ziggy/src/js'

ChartJS.register(CategoryScale, Filler, Legend, LinearScale, LineElement, PointElement, Tooltip)

const props = defineProps({
  data: { type: Array, required: true },
})

const page = usePage()
const route = useRoute(page.props.ziggy)
const chartPeriod = computed(() => page.props.chartPeriod ?? 'month')

const periodMap = {
  Hari: 'day',
  Minggu: 'week',
  Bulan: 'month',
  Tahun: 'year',
}

const labelToKey = Object.fromEntries(Object.entries(periodMap).map(([label, key]) => [key, label]))

const activeLabel = ref(labelToKey[chartPeriod.value] ?? 'Bulan')

const chartData = computed(() => ({
  labels: props.data.map((item) => item.label),
  datasets: [
    {
      label: 'Penjualan',
      data: props.data.map((item) => item.value),
      borderColor: '#059669',
      backgroundColor: 'rgba(16, 185, 129, 0.14)',
      borderWidth: 3,
      pointBackgroundColor: '#ffffff',
      pointBorderColor: '#059669',
      pointBorderWidth: 3,
      pointHoverRadius: 6,
      pointRadius: 4,
      tension: 0.35,
      fill: true,
    },
  ],
}))

const formatCurrency = (value) =>
  `Rp ${Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 })}`

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    intersect: false,
    mode: 'index',
  },
  plugins: {
    legend: { display: false },
    tooltip: {
      displayColors: false,
      callbacks: {
        label: (context) => formatCurrency(context.parsed.y),
      },
    },
  },
  scales: {
    x: {
      border: { display: false },
      grid: { display: false },
      ticks: { color: '#64748b', maxRotation: 0 },
    },
    y: {
      border: { display: false, dash: [5, 5] },
      grid: { color: 'rgba(148, 163, 184, 0.2)', drawTicks: false },
      ticks: {
        color: '#64748b',
        padding: 10,
        callback: (value) => formatCurrency(value),
      },
      beginAtZero: true,
    },
  },
}

const changePeriod = (label) => {
  activeLabel.value = label
  const params = { chart_period: periodMap[label] }
  if (page.props.dateFrom) params.date_from = page.props.dateFrom
  if (page.props.dateTo) params.date_to = page.props.dateTo
  router.get(route('dashboard'), params, {
    preserveState: true,
    preserveScroll: true,
    only: ['salesChartData', 'chartPeriod'],
  })
}
</script>

<template>
  <section
    class="flex min-h-[420px] w-full flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:min-h-[460px] xl:min-h-0"
  >
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold leading-6 tracking-tight text-slate-900 dark:text-white">
          Grafik Penjualan
        </h2>
        <p class="mt-1 text-sm text-slate-500">Tren omzet penjualan pada periode terpilih.</p>
      </div>
      <div class="flex gap-2">
        <button
          v-for="label in Object.keys(periodMap)"
          :key="label"
          type="button"
          :class="[
            'rounded-lg px-3 py-1.5 text-xs font-medium transition',
            activeLabel === label
              ? 'bg-emerald-600 text-white dark:bg-emerald-400 dark:text-[#071426]'
              : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300',
          ]"
          @click="changePeriod(label)"
        >
          {{ label }}
        </button>
      </div>
    </div>
    <div
      class="relative mt-3 h-[320px] w-full shrink-0 overflow-hidden rounded-xl bg-slate-50 p-4 dark:bg-slate-900/50 sm:h-[360px]"
    >
      <Line
        v-if="props.data.length"
        class="relative block h-full w-full"
        :data="chartData"
        :options="chartOptions"
      />
      <div v-else class="flex h-full items-center justify-center text-sm text-slate-400">
        Tidak ada data.
      </div>
    </div>
  </section>
</template>

<style scoped>
:deep(canvas) {
  display: block;
  height: 100% !important;
  max-height: 100%;
  width: 100% !important;
}
</style>
