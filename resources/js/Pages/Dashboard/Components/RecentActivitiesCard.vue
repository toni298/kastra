<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { FileText, ShoppingCart, ArrowRight } from 'lucide-vue-next'
import { useRoute } from 'ziggy-js'

defineProps({
  activities: { type: Array, required: true },
})

const page = usePage()
const route = useRoute(page.props.ziggy)

const formatCurrency = (value) =>
  value
    ? new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
      }).format(value)
    : '-'

const iconMap = {
  sale: { icon: FileText, style: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30' },
  purchase: { icon: ShoppingCart, style: 'bg-blue-50 text-blue-600 dark:bg-blue-900/30' },
}
const fallbackIcon = {
  icon: FileText,
  style: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
}

const getIcon = (item) => iconMap[item.type] ?? fallbackIcon
</script>

<template>
  <section
    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
  >
    <header class="px-5 pb-2 pt-4">
      <h2 class="text-lg font-semibold leading-6 tracking-tight text-slate-950 dark:text-white">
        Aktivitas Terbaru
      </h2>
    </header>
    <div class="divide-y divide-slate-200 px-5 dark:divide-slate-700">
      <Link
        v-for="aktivitas in activities.slice(0, 5)"
        :key="aktivitas.id"
        :href="aktivitas.link"
        class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-4 py-3 transition hover:bg-slate-50 dark:hover:bg-slate-700/30 sm:grid-cols-[minmax(0,1fr)_140px_110px_100px]"
      >
        <div class="flex min-w-0 items-center gap-3">
          <div
            :class="[
              'grid h-9 w-9 shrink-0 place-items-center rounded-lg',
              getIcon(aktivitas).style,
            ]"
          >
            <component :is="getIcon(aktivitas).icon" class="h-4 w-4" />
          </div>
          <div class="min-w-0">
            <p class="truncate text-sm font-medium leading-5 text-slate-950 dark:text-white">
              {{ aktivitas.title }}
            </p>
            <p class="truncate text-xs font-medium leading-4 text-slate-500 dark:text-slate-400">
              {{ aktivitas.subtitle }}
            </p>
          </div>
        </div>
        <p
          class="hidden text-right text-sm font-bold tabular-nums text-slate-900 dark:text-white sm:block"
        >
          {{ formatCurrency(aktivitas.amount) }}
        </p>
        <div class="hidden justify-center sm:flex">
          <span
            :class="[
              'rounded-full px-3 py-1 text-xs font-medium',
              aktivitas.statusColor === 'green'
                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                : aktivitas.statusColor === 'blue'
                  ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                  : aktivitas.statusColor === 'orange'
                    ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'
                    : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
            ]"
            >{{ aktivitas.status }}</span
          >
        </div>
        <p class="text-right text-xs font-semibold text-slate-500 dark:text-slate-400">
          {{ aktivitas.time }}
        </p>
      </Link>
    </div>
    <footer class="border-t border-slate-200 px-5 py-3 dark:border-slate-700">
      <Link
        :href="route('sales.transactions.index')"
        class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
        >Lihat semua aktivitas <ArrowRight class="h-4 w-4"
      /></Link>
    </footer>
  </section>
</template>
