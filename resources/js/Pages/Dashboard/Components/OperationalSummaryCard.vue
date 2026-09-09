<script setup>
import { Link } from '@inertiajs/vue3'
import { ShoppingCart, Package, FileText, AlertCircle, AlertTriangle } from '@lucide/vue'

const props = defineProps({
  data: { type: Array, required: true },
})

const getIcon = (label) => {
  if (label.includes('Sales')) return ShoppingCart
  if (label.includes('Purchase')) return Package
  if (label.includes('Dibayar')) return AlertCircle
  if (label.includes('Ditagih')) return FileText
  if (label.includes('Stok')) return AlertTriangle
  return Package
}
</script>

<template>
  <div
    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"
  >
    <div class="mb-5 flex items-center justify-between gap-4">
      <h2 class="text-lg font-semibold leading-6 tracking-tight text-slate-900 dark:text-white">
        Ringkasan Operasional
      </h2>
      <Link
        href="#"
        class="text-sm font-semibold leading-5 text-emerald-600 hover:underline dark:text-emerald-400"
        >Lihat semua aktivitas ?</Link
      >
    </div>

    <div class="space-y-3">
      <div v-for="(item, index) in data" :key="index" class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <component :is="getIcon(item.label)" class="h-4 w-4 text-slate-500 dark:text-slate-400" />
          <span class="text-sm font-medium leading-5 text-slate-700 dark:text-slate-300">{{
            item.label
          }}</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-lg font-bold leading-6 tabular-nums text-slate-900 dark:text-white">{{
            item.value
          }}</span>
          <span
            :class="[
              'rounded-full px-2 py-0.5 text-xs font-medium',
              item.statusColor === 'green'
                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                : '',
              item.statusColor === 'blue'
                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                : '',
              item.statusColor === 'red'
                ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                : '',
              item.statusColor === 'orange'
                ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'
                : '',
            ]"
          >
            {{ item.status }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
