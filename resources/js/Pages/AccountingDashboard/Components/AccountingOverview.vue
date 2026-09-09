<script setup>
import { computed, ref } from 'vue'
import * as icons from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import AccountingPerformanceChart from './AccountingPerformanceChart.vue'

const props = defineProps({
  overview: { type: Object, default: () => ({}) },
})

const period = ref('Bulan')
const metric = ref('Pendapatan')
const periods = ['Bulan', 'Kuartal', 'Tahun']

const colors = {
  blue: 'bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300',
  amber: 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300',
  violet: 'bg-violet-50 text-violet-600 dark:bg-violet-400/10 dark:text-violet-300',
  emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300',
  red: 'bg-red-50 text-red-600 dark:bg-red-400/10 dark:text-red-300',
  cyan: 'bg-cyan-50 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-300',
}

const summary = computed(() => props.overview?.summary ?? [])
const health = computed(() => props.overview?.health ?? [])
const performance = computed(() => props.overview?.performance ?? { months: [], revenue: [], expense: [], profit: [] })
const attention = computed(() => props.overview?.attention ?? [])
const activities = computed(() => props.overview?.activities ?? [])
const insights = computed(() => props.overview?.insights ?? [])
const loading = computed(() => !props.overview || Object.keys(props.overview).length === 0)
</script>

<template>
  <div class="space-y-5">
    <!-- Kesehatan Pembukuan -->
    <section
      class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
    >
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[.14em] text-emerald-600">
            Kesehatan Pembukuan
          </p>
          <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">
            Pembukuan bisnis Anda dalam kondisi baik
          </h2>
          <p class="mt-1 text-sm text-slate-500">
            Kastra mengelola jurnal dan laporan secara otomatis dari aktivitas bisnis.
          </p>
        </div>
        <span
          class="hidden h-14 w-14 place-items-center rounded-2xl bg-emerald-100 text-emerald-700 sm:grid"
          ><icons.HeartPulse :size="27"
        /></span>
      </div>
      <div class="mt-6 grid gap-3 sm:grid-cols-2">
        <template v-if="loading">
          <div
            v-for="i in 4"
            :key="i"
            class="h-[68px] animate-pulse rounded-xl border border-slate-100 bg-slate-50 dark:border-[#29476b] dark:bg-[#0a1b33]"
          />
        </template>
        <div
          v-for="item in health"
          v-else
          :key="item.text"
          class="flex items-center gap-3 rounded-xl border border-slate-100 p-4 dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <span :class="['grid h-9 w-9 place-items-center rounded-xl', colors[item.color]]"
            ><component :is="icons[item.icon]" :size="18"
          /></span>
          <p class="min-w-0 flex-1 text-sm font-medium text-slate-800 dark:text-slate-200">
            {{ item.text }}
          </p>
          <span class="text-xs font-medium text-slate-400">{{ item.status }}</span>
        </div>
      </div>
    </section>

    <!-- Summary Cards -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
      <template v-if="loading">
        <div
          v-for="i in 6"
          :key="i"
          class="h-[140px] animate-pulse rounded-2xl border border-slate-200 bg-slate-50 dark:border-[#29476b] dark:bg-[#102542]"
        />
      </template>
      <article
        v-for="item in summary"
        v-else
        :key="item.label"
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <span :class="['grid h-10 w-10 place-items-center rounded-xl', colors[item.color]]"
          ><component :is="icons[item.icon]" :size="20"
        /></span>
        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-slate-400">
          {{ item.label }}
        </p>
        <p class="mt-1 text-2xl font-semibold text-slate-950 dark:text-white">{{ item.value }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ item.caption }}</p>
      </article>
    </section>

    <!-- Kinerja Keuangan + Membutuhkan Perhatian -->
    <section class="grid gap-5 xl:grid-cols-[minmax(0,1.4fr)_minmax(330px,.7fr)]">
      <article
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <div class="flex flex-col justify-between gap-4 sm:flex-row">
          <div>
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Kinerja Keuangan</h2>
            <p class="mt-1 text-sm text-slate-500">Ringkasan perubahan keuangan perusahaan.</p>
          </div>
          <select
            v-model="metric"
            class="rounded-xl border-slate-200 py-2 text-sm font-semibold dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option>Pendapatan</option>
            <option>Beban</option>
            <option>Laba Bersih</option>
            <option>Arus Kas</option>
          </select>
        </div>
        <div class="mt-4 flex gap-2">
          <button
            v-for="item in periods"
            :key="item"
            :class="[
              'rounded-lg px-3 py-1.5 text-xs font-medium',
              period === item
                ? 'bg-emerald-600 text-white dark:bg-emerald-400 dark:text-[#071426]'
                : 'bg-slate-100 text-slate-500 dark:bg-[#163354] dark:text-slate-300',
            ]"
            @click="period = item"
          >
            {{ item }}
          </button>
        </div>
        <div v-if="loading" class="mt-3 h-72 animate-pulse rounded-xl bg-slate-50 dark:bg-[#0a1b33]" />
        <AccountingPerformanceChart v-else class="mt-3" :metric="metric" :data="performance" />
      </article>
      <article
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Membutuhkan Perhatian</h2>
        <p class="mt-1 text-sm text-slate-500">
          Hal yang perlu ditindaklanjuti agar pembukuan tetap sehat.
        </p>
        <div class="mt-5 space-y-3">
          <template v-if="loading">
            <div
              v-for="i in 3"
              :key="i"
              class="h-[60px] animate-pulse rounded-xl border border-slate-100 bg-slate-50 dark:border-[#29476b] dark:bg-[#0a1b33]"
            />
          </template>
          <div
            v-for="item in attention"
            v-else
            :key="item.name"
            class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 p-4 dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <p class="text-sm font-medium dark:text-white">{{ item.name }}</p>
            <Badge :variant="item.variant">{{ item.count }}</Badge>
          </div>
        </div>
      </article>
    </section>

    <!-- Aktivitas Pembukuan + Insight Akuntansi -->
    <section class="grid gap-5 xl:grid-cols-[minmax(0,1.4fr)_minmax(330px,.7fr)]">
      <article
        class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <div class="border-b border-slate-100 p-5 dark:border-[#29476b]">
          <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Aktivitas Pembukuan</h2>
          <p class="mt-1 text-sm text-slate-500">
            Aktivitas bisnis yang telah diterjemahkan Kastra menjadi pembukuan.
          </p>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-[#29476b]">
          <template v-if="loading">
            <div
              v-for="i in 5"
              :key="i"
              class="flex items-center gap-4 p-4 sm:px-5"
            >
              <div class="h-10 w-10 shrink-0 animate-pulse rounded-xl bg-slate-100 dark:bg-[#0a1b33]" />
              <div class="flex-1 space-y-2">
                <div class="h-4 w-48 animate-pulse rounded bg-slate-100 dark:bg-[#0a1b33]" />
                <div class="h-3 w-32 animate-pulse rounded bg-slate-100 dark:bg-[#0a1b33]" />
              </div>
            </div>
          </template>
          <template v-else>
            <div
              v-for="item in activities"
              :key="item.reference"
              class="flex items-center gap-4 p-4 sm:px-5"
            >
              <span
                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"
                ><component :is="icons[item.icon]" :size="19"
              /></span>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ item.title }}</p>
                <p class="mt-1 text-xs text-slate-500">
                  {{ item.reference }} · {{ item.module }} · {{ item.time }}
                </p>
              </div>
              <Badge :variant="item.variant">{{ item.status }}</Badge>
            </div>
            <p
              v-if="activities.length === 0"
              class="p-5 text-center text-sm text-slate-400"
            >
              Belum ada aktivitas pembukuan.
            </p>
          </template>
        </div>
      </article>
      <article
        class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-400/20 dark:bg-emerald-400/10"
      >
        <h2 class="font-semibold text-emerald-900 dark:text-emerald-200">Insight Akuntansi</h2>
        <ul class="mt-4 space-y-3 text-sm leading-6 text-emerald-800 dark:text-emerald-300">
          <template v-if="loading">
            <li v-for="i in 4" :key="i" class="h-4 animate-pulse rounded bg-emerald-100 dark:bg-emerald-400/10" />
          </template>
          <template v-else>
            <li v-for="(insight, i) in insights" :key="i">{{ insight }}</li>
          </template>
        </ul>
      </article>
    </section>
  </div>
</template>
