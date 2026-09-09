<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/Utils/helpers'

const props = defineProps({ title: { type: String, required: true }, items: { type: Array, default: () => [] }, tone: { type: String, default: 'emerald' }, icon: { type: Object, required: true } })
const toneClass = computed(() => props.tone === 'red' ? { bar: 'bg-red-500', icon: 'bg-red-50 text-red-600 dark:bg-red-400/10 dark:text-red-300' } : { bar: 'bg-emerald-500', icon: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300' })
</script>

<template>
  <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"><div class="flex items-center gap-3"><span :class="['grid size-9 place-items-center rounded-xl', toneClass.icon]"><component :is="icon" :size="18" /></span><div><h2 class="font-semibold text-slate-950 dark:text-white">{{ title }}</h2><p class="mt-0.5 text-xs text-slate-500">Top 5 kategori berdasarkan nominal</p></div></div><div v-if="items.length" class="mt-5 space-y-4"><div v-for="item in items" :key="item.label"><div class="flex items-center justify-between gap-3 text-sm"><span class="truncate font-medium text-slate-700 dark:text-slate-200">{{ item.label }}</span><span class="shrink-0 font-semibold tabular-nums text-slate-950 dark:text-white">{{ formatCurrency(item.total) }} <span class="ml-1 text-xs font-medium text-slate-400">{{ item.percentage }}%</span></span></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-[#0a1b33]"><div :class="['h-full rounded-full transition-all', toneClass.bar]" :style="{ width: `${Math.min(100, item.percentage)}%` }"></div></div></div></div><p v-else class="mt-6 rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500 dark:bg-[#0a1b33]">Belum ada data kategori.</p></section>
</template>
