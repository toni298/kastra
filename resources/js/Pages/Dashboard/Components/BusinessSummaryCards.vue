<script setup>
import { TrendingUp, WalletCards, Landmark, Package } from 'lucide-vue-next'

const props = defineProps({
  data: { type: Array, required: true },
})

const icons = { TrendingUp, WalletCards, Landmark, Package }

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value)
</script>

<template>
  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <article
      v-for="item in data"
      :key="item.label"
      class="min-w-0 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800"
    >
      <div class="flex items-start gap-3">
        <div :class="['rounded-lg p-2.5', item.iconBackground]">
          <component :is="icons[item.icon]" :class="['h-5 w-5', item.iconColor]" />
        </div>
        <div class="min-w-0">
          <p class="truncate text-xs font-semibold leading-4 text-slate-600 dark:text-slate-400">
            {{ item.label }}
          </p>
          <p
            class="mt-1 truncate text-xl font-bold leading-7 tracking-tight text-slate-950 tabular-nums dark:text-white"
          >
            {{ item.currency ? formatCurrency(item.value) : `${item.value} ${item.suffix}` }}
          </p>
        </div>
      </div>
      <p :class="['mt-4 text-xs font-medium leading-4', item.changeColor]">
        {{ item.direction === 'down' ? '▼' : '▲' }} {{ item.change }}
      </p>
      <p class="mt-2 text-xs font-medium leading-4 text-slate-500 dark:text-slate-400">
        {{ item.caption }}
      </p>
      <p
        v-if="item.projection !== undefined"
        class="mt-1.5 truncate text-xs font-semibold leading-4 text-slate-700 tabular-nums dark:text-slate-200"
      >
        Proyeksi: {{ formatCurrency(item.projection) }}
      </p>
    </article>
  </div>
</template>
