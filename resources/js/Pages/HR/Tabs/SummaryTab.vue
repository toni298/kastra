<script setup>
import { computed } from 'vue'
import { UserCheck, UserRound, UsersRound, WalletCards } from 'lucide-vue-next'
import { formatCurrency } from '@/Utils/helpers'

const props = defineProps({
  summary: { type: Object, default: () => ({}) },
})
const cards = computed(() => [
  {
    label: 'Total Karyawan',
    value: props.summary.total ?? 0,
    icon: UsersRound,
    class: 'bg-blue-50 text-blue-600',
  },
  {
    label: 'Karyawan Aktif',
    value: props.summary.active ?? 0,
    icon: UserCheck,
    class: 'bg-emerald-50 text-emerald-600',
  },
  {
    label: 'Total Kasir',
    value: props.summary.cashiers ?? 0,
    icon: UserRound,
    class: 'bg-amber-50 text-amber-600',
  },
  {
    label: 'Total Estimasi Gaji Pokok',
    value: formatCurrency(props.summary.base_salary),
    icon: WalletCards,
    class: 'bg-violet-50 text-violet-600',
  },
])
</script>

<template>
  <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article
      v-for="card in cards"
      :key="card.label"
      class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
    >
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-medium text-slate-500">{{ card.label }}</p>
          <p class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">{{ card.value }}</p>
        </div>
        <span :class="['grid h-10 w-10 place-items-center rounded-xl', card.class]"
          ><component :is="card.icon" :size="20"
        /></span>
      </div>
    </article>
  </section>
</template>
