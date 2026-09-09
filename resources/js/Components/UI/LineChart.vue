<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  data: { type: Array, required: true },
  height: { type: Number, default: 300 },
  color: { type: String, default: '#10b981' },
  formatValue: { type: Function, default: (v) => `Rp ${Math.round(v / 1000000)}jt` },
  formatTooltip: { type: Function, default: (v) => `Rp ${Number(v).toLocaleString('id-ID')}` },
})

const chartWidth = 1000
const chartPadding = { top: 16, right: 20, bottom: 56, left: 72 }
const plotWidth = chartWidth - chartPadding.left - chartPadding.right
const plotHeight = computed(() => props.height - chartPadding.top - chartPadding.bottom)

const maxValue = computed(() => {
  const max = Math.max(...props.data.map((p) => p.value || 0), 0)
  if (max === 0) return 100
  const magnitude = Math.pow(10, Math.floor(Math.log10(max)))
  return Math.ceil(max / magnitude) * magnitude
})

const axisMax = computed(() => (maxValue.value === 0 ? 100 : maxValue.value * 1.1))
const axisSteps = 4

const points = computed(() =>
  props.data.map((point, index) => ({
    ...point,
    x: chartPadding.left + (index / Math.max(props.data.length - 1, 1)) * plotWidth,
    y: chartPadding.top + plotHeight.value - (point.value / axisMax.value) * plotHeight.value,
  }))
)

const pathD = computed(() =>
  points.value.map((point, index) => `${index ? 'L' : 'M'} ${point.x} ${point.y}`).join(' ')
)

const areaD = computed(() => {
  if (!points.value.length) return ''
  const first = points.value[0]
  const last = points.value.at(-1)
  const baseY = chartPadding.top + plotHeight.value
  return `${pathD.value} L ${last.x} ${baseY} L ${first.x} ${baseY} Z`
})

const gridLines = computed(() =>
  Array.from({ length: axisSteps + 1 }, (_, index) => {
    const value = axisMax.value - (index / axisSteps) * axisMax.value
    return {
      y: chartPadding.top + (index / axisSteps) * plotHeight.value,
      label: value === 0 ? 'Rp 0' : props.formatValue(value),
    }
  })
)

const labelCount = computed(() => Math.min(points.value.length, 8))
const labelStep = computed(() =>
  points.value.length > 1 ? Math.ceil(points.value.length / labelCount.value) : 1
)
const visibleLabels = computed(() =>
  points.value.filter(
    (_, index) => index % labelStep.value === 0 || index === points.value.length - 1
  )
)

const hoveredIndex = ref(null)
const svgRef = ref(null)

const onHover = (index) => {
  hoveredIndex.value = index
}
const onLeave = () => {
  hoveredIndex.value = null
}

const tooltip = computed(() => {
  if (hoveredIndex.value === null) return null
  const point = points.value[hoveredIndex.value]
  if (!point) return null
  const tooltipWidth = 180
  const tooltipHeight = 72
  let tx = point.x - tooltipWidth / 2
  tx = Math.max(chartPadding.left, Math.min(tx, chartWidth - chartPadding.right - tooltipWidth))
  const ty = Math.max(chartPadding.top, point.y - tooltipHeight - 12)
  return { ...point, tx, ty, tooltipWidth, tooltipHeight }
})
</script>

<template>
  <div class="relative h-full w-full">
    <svg
      ref="svgRef"
      :viewBox="`0 0 ${chartWidth} ${height}`"
      class="block h-full w-full"
      :style="{ minHeight: '245px' }"
      preserveAspectRatio="none"
      role="img"
      aria-label="Grafik penjualan"
      @mouseleave="onLeave"
    >
      <g v-for="(line, index) in gridLines" :key="`grid-${index}`">
        <line
          :x1="chartPadding.left"
          :x2="chartWidth - chartPadding.right"
          :y1="line.y"
          :y2="line.y"
          class="stroke-slate-200 dark:stroke-slate-700"
          stroke-width="1"
          vector-effect="non-scaling-stroke"
        />
        <text
          :x="chartPadding.left - 12"
          :y="line.y + 4"
          text-anchor="end"
          class="fill-slate-500 text-[13px] font-medium dark:fill-slate-400"
        >
          {{ line.label }}
        </text>
      </g>

      <path :d="areaD" :fill="color" fill-opacity="0.1" />
      <path
        :d="pathD"
        :stroke="color"
        stroke-width="2.5"
        fill="none"
        stroke-linecap="round"
        stroke-linejoin="round"
        vector-effect="non-scaling-stroke"
      />

      <line
        v-if="tooltip"
        :x1="tooltip.x"
        :x2="tooltip.x"
        :y1="chartPadding.top"
        :y2="chartPadding.top + plotHeight"
        :stroke="color"
        stroke-width="1"
        stroke-dasharray="4 4"
        opacity="0.5"
        vector-effect="non-scaling-stroke"
      />

      <circle
        v-for="(point, index) in points"
        :key="index"
        :cx="point.x"
        :cy="point.y"
        :r="hoveredIndex === index ? 6 : 3.5"
        :fill="color"
        stroke="white"
        :stroke-width="hoveredIndex === index ? 2.5 : 1.5"
        vector-effect="non-scaling-stroke"
        class="cursor-pointer transition-all"
        @mouseenter="onHover(index)"
      />

      <rect
        v-for="(point, index) in points"
        :key="`hit-${index}`"
        :x="point.x - plotWidth / Math.max(points.length, 1) / 2"
        :y="chartPadding.top"
        :width="plotWidth / Math.max(points.length, 1)"
        :height="plotHeight"
        fill="transparent"
        class="cursor-pointer"
        @mouseenter="onHover(index)"
      />

      <text
        v-for="(point, index) in visibleLabels"
        :key="`label-${index}`"
        :x="point.x"
        :y="chartPadding.top + plotHeight + 18"
        :text-anchor="
          point.x === chartPadding.left ? 'start' : point.x > chartWidth - 80 ? 'end' : 'middle'
        "
        :transform="
          visibleLabels.length > 6
            ? `rotate(-45 ${point.x} ${chartPadding.top + plotHeight + 18})`
            : undefined
        "
        class="fill-slate-500 text-[13px] font-medium dark:fill-slate-400"
      >
        {{ point.label }}
      </text>
    </svg>

    <div
      v-if="tooltip"
      class="pointer-events-none absolute z-10 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-lg dark:border-[#29476b] dark:bg-[#102542]"
      :style="{
        left: `${(tooltip.tx / chartWidth) * 100}%`,
        top: `${(tooltip.ty / height) * 100}%`,
        width: `${(tooltip.tooltipWidth / chartWidth) * 100}%`,
      }"
    >
      <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
        {{ tooltip.date ?? tooltip.label }}
      </p>
      <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white">
        {{ formatTooltip(tooltip.value) }}
      </p>
      <p class="text-xs text-slate-500 dark:text-slate-400">{{ tooltip.count }} transaksi</p>
    </div>
  </div>
</template>
