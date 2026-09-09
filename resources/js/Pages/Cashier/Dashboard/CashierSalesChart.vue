<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  CategoryScale,
  Chart,
  Filler,
  LinearScale,
  LineController,
  LineElement,
  PointElement,
  Tooltip,
} from 'chart.js'

Chart.register(CategoryScale, Filler, LinearScale, LineController, LineElement, PointElement, Tooltip)

const props = defineProps({
  data: { type: Array, default: () => [] },
  period: { type: String, default: 'today' },
})
const periods = [
  { id: 'today', label: 'Hari Ini' },
  { id: '7d', label: '7 Hari' },
  { id: '30d', label: '30 Hari' },
  { id: 'month', label: '1 Bulan' },
]
const canvasRef = ref(null)
let chart = null
let renderRequest = 0
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
const total = computed(() => props.data.reduce((sum, item) => sum + item.value, 0))
const selectPeriod = (period) => {
  if (period === props.period) return
  router.get(
    route('cashier.dashboard'),
    { chart_period: period },
    {
      only: ['salesChart', 'chartPeriod'],
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}
const destroyChart = () => {
  chart?.destroy()
  chart = null
}
const renderChart = async () => {
  const request = ++renderRequest
  await nextTick()
  if (request !== renderRequest) return
  if (!canvasRef.value || !props.data.length) return

  const labels = props.data.map((item) => item.label)
  const values = props.data.map((item) => item.value)

  if (chart) {
    chart.data.labels = labels
    chart.data.datasets[0].data = values
    chart.update('none')
    return
  }

  chart = new Chart(canvasRef.value, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Penjualan',
          data: values,
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.14)',
          borderWidth: 3,
          pointBackgroundColor: '#ffffff',
          pointBorderColor: '#10b981',
          pointBorderWidth: 3,
          pointHoverRadius: 6,
          pointRadius: 4,
          tension: 0.35,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      interaction: { intersect: false, mode: 'index' },
      plugins: {
        legend: { display: false },
        tooltip: {
          displayColors: false,
          callbacks: { label: (context) => money(context.parsed.y) },
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
          ticks: { color: '#64748b', padding: 10, callback: (value) => money(value) },
          beginAtZero: true,
        },
      },
    },
  })
}

onMounted(renderChart)
watch(() => props.data, renderChart, { deep: true })
onBeforeUnmount(destroyChart)
</script>
<template>
  <section
    class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
  >
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
      <div>
        <h2 class="font-bold text-slate-950 dark:text-white">Grafik Penjualan</h2>
        <p class="mt-1 text-sm text-slate-500">Performa penjualan berdasarkan periode.</p>
      </div>
      <div class="flex gap-1 rounded-xl bg-slate-100 p-1 dark:bg-[#0d2039]">
        <button
          v-for="item in periods"
          :key="item.id"
          type="button"
          :class="
            item.id === period
              ? 'bg-white text-emerald-700 shadow-sm dark:bg-[#102542] dark:text-emerald-300'
              : 'text-slate-500 hover:text-slate-800'
          "
          class="rounded-lg px-2.5 py-1.5 text-[11px] font-semibold transition"
          @click="selectPeriod(item.id)"
        >
          {{ item.label }}
        </button>
      </div>
    </div>
    <div class="relative mt-8 min-h-[220px] w-full min-w-0 flex-1 overflow-hidden">
      <canvas
        ref="canvasRef"
        :class="['block h-full w-full', { hidden: !data.length }]"
        role="img"
        aria-label="Grafik garis penjualan"
      ></canvas>
      <p v-if="!data.length" class="py-12 text-center text-sm text-slate-500">
        Belum ada penjualan pada periode ini.
      </p>
    </div>
    <div class="mt-5 flex items-center justify-between">
      <div>
        <p class="text-xs text-slate-500">Total penjualan</p>
        <p class="mt-1 text-xl font-bold text-slate-950 dark:text-white">{{ money(total) }}</p>
      </div>
      <div class="text-right">
        <p class="text-xs text-slate-500">Jam aktif</p>
        <p class="mt-1 font-bold text-emerald-600">{{ data.length }} titik data</p>
      </div>
    </div>
  </section>
</template>
