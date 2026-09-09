<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useTheme } from '@/Composables/useTheme'

const props = defineProps({
  metric: { type: String, default: 'Pendapatan' },
  data: {
    type: Object,
    default: () => ({ months: [], revenue: [], expense: [], profit: [] }),
  },
})
const chartRef = ref(null)
let chart = null
const { isDark } = useTheme()

const formatCompactCurrency = (value) => {
  const amount = Number(value ?? 0)

  if (!Number.isFinite(amount)) return 'Rp 0'
  if (amount >= 1_000_000_000) {
    return `Rp ${(amount / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} M`
  }
  if (amount >= 1_000_000) {
    return `Rp ${(amount / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`
  }
  if (amount >= 1_000) {
    return `Rp ${(amount / 1_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} rb`
  }

  return `Rp ${amount.toLocaleString('id-ID')}`
}

const formatAxisCurrency = (value) => {
  const amount = Number(value ?? 0)

  if (!Number.isFinite(amount)) return '0'
  if (amount >= 1_000_000_000) {
    return `${(amount / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })}M`
  }
  if (amount >= 1_000_000) {
    return `${(amount / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })}jt`
  }
  if (amount >= 1_000) {
    return `${(amount / 1_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })}rb`
  }

  return amount.toLocaleString('id-ID')
}

const render = async () => {
  if (!chartRef.value) return
  const echarts = await import('echarts')
  chart ??= echarts.init(chartRef.value)

  const d = props.data
  const series =
    props.metric === 'Beban'
      ? d.expense
      : props.metric === 'Laba Bersih'
        ? d.profit
        : props.metric === 'Arus Kas'
          ? d.revenue.map((v, i) => (Number(v) ?? 0) - Number(d.expense[i] ?? 0))
          : d.revenue

  const palette = {
    Pendapatan: {
      color: '#059669',
      gradientStart: 'rgba(5,150,105,.28)',
      gradientEnd: 'rgba(5,150,105,.02)',
    },
    Beban: {
      color: '#ef4444',
      gradientStart: 'rgba(239,68,68,.24)',
      gradientEnd: 'rgba(239,68,68,.02)',
    },
    'Laba Bersih': {
      color: '#0284c7',
      gradientStart: 'rgba(2,132,199,.25)',
      gradientEnd: 'rgba(2,132,199,.02)',
    },
    'Arus Kas': {
      color: '#8b5cf6',
      gradientStart: 'rgba(139,92,246,.22)',
      gradientEnd: 'rgba(139,92,246,.02)',
    },
  }

  const activePalette = palette[props.metric] ?? palette.Pendapatan

  chart.setOption({
    backgroundColor: 'transparent',
    grid: { left: 50, right: 18, top: 24, bottom: 32 },
    tooltip: {
      trigger: 'axis',
      backgroundColor: isDark.value ? '#0f172a' : '#ffffff',
      borderColor: isDark.value ? '#334155' : '#e2e8f0',
      textStyle: { color: isDark.value ? '#e2e8f0' : '#0f172a' },
      valueFormatter: (v) => formatCompactCurrency(v),
    },
    xAxis: {
      type: 'category',
      data: d.months,
      axisLine: { lineStyle: { color: isDark.value ? '#29476b' : '#e2e8f0' } },
      axisLabel: { color: isDark.value ? '#94a3b8' : '#64748b', fontSize: 11 },
    },
    yAxis: {
      type: 'value',
      axisLabel: {
        formatter: formatAxisCurrency,
        color: isDark.value ? '#94a3b8' : '#64748b',
        fontSize: 11,
      },
      splitLine: { lineStyle: { color: isDark.value ? '#29476b' : '#e2e8f0' } },
    },
    series: [
      {
        type: 'line',
        data: series.map((value) => Number(value ?? 0)),
        smooth: true,
        symbol: 'circle',
        symbolSize: 5,
        lineStyle: { color: activePalette.color, width: 3 },
        itemStyle: { color: activePalette.color },
        areaStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: activePalette.gradientStart },
            { offset: 1, color: activePalette.gradientEnd },
          ]),
        },
      },
    ],
  })
  chart.resize()
}

const resize = () => chart?.resize()
onMounted(() => {
  render()
  window.addEventListener('resize', resize)
})
watch(() => [props.metric, isDark.value, props.data], render, { deep: true })
onBeforeUnmount(() => {
  window.removeEventListener('resize', resize)
  chart?.dispose()
})
</script>

<template>
  <div ref="chartRef" class="h-72 w-full" role="img" aria-label="Grafik kinerja keuangan"></div>
</template>
