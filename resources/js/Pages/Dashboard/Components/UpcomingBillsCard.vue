<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { ArrowDownLeft, ArrowUpRight, CalendarCheck2, Check, ArrowRight } from 'lucide-vue-next'
import { useRoute } from '../../../../../vendor/tightenco/ziggy/src/js'

defineProps({
  reminders: { type: Array, default: () => [] },
})

const page = usePage()
const route = useRoute(page.props.ziggy)

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value ?? 0)

const formatDueDate = (value) => {
  if (!value) return '-'

  const due = new Date(value)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const target = new Date(due)
  target.setHours(0, 0, 0, 0)

  const diffDays = Math.round((target - today) / 86400000)
  const label = new Intl.DateTimeFormat('id-ID', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
  }).format(due)

  if (diffDays === 0) return `Hari ini · ${label}`
  if (diffDays === 1) return `Besok · ${label}`
  return label
}

const markAsDone = (reminder) => {
  router.post(
    route('reminders.complete', reminder.id),
    {},
    { preserveScroll: true, preserveState: true }
  )
}
</script>

<template>
  <section
    class="flex flex-col rounded-xl border border-slate-100 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"
  >
    <div class="mb-4 flex items-center justify-between gap-3">
      <div>
        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">
          Tagihan & Agenda Jatuh Tempo
        </h3>
        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">7 hari ke depan</p>
      </div>
      <span
        class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
      >
        <CalendarCheck2 class="h-4.5 w-4.5" />
      </span>
    </div>

    <ul v-if="reminders.length" class="divide-y divide-slate-100 dark:divide-slate-700/60">
      <li v-for="reminder in reminders" :key="reminder.id" class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
        <span
          class="grid h-10 w-10 shrink-0 place-items-center rounded-xl"
          :class="
            reminder.type_transcation === 'in'
              ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400'
              : 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400'
          "
        >
          <ArrowDownLeft v-if="reminder.type_transcation === 'in'" class="h-5 w-5" />
          <ArrowUpRight v-else class="h-5 w-5" />
        </span>

        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">
            {{ reminder.title }}
          </p>
          <p class="mt-0.5 truncate text-xs text-slate-400 dark:text-slate-500">
            <span :class="reminder.type_transcation === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400'">
              {{ reminder.type_transcation === 'in' ? 'Pemasukan' : 'Pengeluaran' }}
            </span>
            · {{ formatDueDate(reminder.start) }}
          </p>
        </div>

        <div class="flex shrink-0 items-center gap-2">
          <p
            class="text-sm font-semibold tabular-nums"
            :class="
              reminder.type_transcation === 'in'
                ? 'text-emerald-600 dark:text-emerald-400'
                : 'text-rose-600 dark:text-rose-400'
            "
          >
            {{ reminder.type_transcation === 'in' ? '+' : '−' }} {{ formatCurrency(reminder.amount) }}
          </p>
          <button
            type="button"
            title="Tandai Lunas"
            aria-label="Tandai Lunas"
            class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400"
            @click="markAsDone(reminder)"
          >
            <Check class="h-4 w-4" />
          </button>
        </div>
      </li>
    </ul>

    <div v-else class="flex flex-1 flex-col items-center justify-center py-8 text-center">
      <span
        class="mb-3 grid h-11 w-11 place-items-center rounded-full bg-emerald-50 text-emerald-500 dark:bg-emerald-900/30 dark:text-emerald-400"
      >
        <Check class="h-5 w-5" />
      </span>
      <p class="text-sm font-medium text-slate-600 dark:text-slate-300">
        Tidak ada tagihan jatuh tempo
      </p>
      <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
        Semua agenda 7 hari ke depan sudah beres.
      </p>
    </div>

    <footer class="mt-4 border-t border-slate-100 pt-3 dark:border-slate-700/60">
      <Link
        :href="route('reminders.index')"
        class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
      >
        Lihat Semua Kalender <ArrowRight class="h-4 w-4" />
      </Link>
    </footer>
  </section>
</template>
