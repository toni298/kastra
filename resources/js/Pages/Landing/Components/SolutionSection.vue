<script setup>
import {
  ArrowRight,
  Boxes,
  ChartNoAxesCombined,
  Database,
  FileSpreadsheet,
  RefreshCw,
  ShoppingCart,
  Unplug,
  WalletCards,
} from '@lucide/vue'
import { fadeOnScroll, revealOnScroll } from '@/Utils/landingMotion'

const headingMotion = revealOnScroll()
const solutionPanelMotion = revealOnScroll(120, 20)

const problems = [
  {
    icon: FileSpreadsheet,
    title: 'Data tersebar',
    description: 'Penjualan, stok, dan keuangan dicatat di tempat yang berbeda.',
  },
  {
    icon: RefreshCw,
    title: 'Input berulang',
    description: 'Tim menghabiskan waktu memindahkan data yang sama secara manual.',
  },
  {
    icon: Unplug,
    title: 'Sulit melihat kondisi bisnis',
    description: 'Laporan terlambat dan keputusan dibuat tanpa gambaran yang utuh.',
  },
]

const problemMotions = problems.map((_, index) => fadeOnScroll(index * 90))

const connectedModules = [
  { icon: ShoppingCart, label: 'Penjualan', position: 'left-0 top-3 sm:left-4' },
  { icon: Boxes, label: 'Persediaan', position: 'right-0 top-3 sm:right-4' },
  { icon: WalletCards, label: 'Kas & Bank', position: 'bottom-3 left-0 sm:left-4' },
  {
    icon: ChartNoAxesCombined,
    label: 'Laporan',
    position: 'bottom-3 right-0 sm:right-4',
  },
]
</script>

<template>
  <section
    id="solusi"
    class="relative overflow-hidden border-y border-slate-200 bg-slate-50 px-5 py-20 sm:px-6 lg:px-8 lg:py-28 dark:border-slate-800 dark:bg-[#0b1628]"
  >
    <div
      class="pointer-events-none absolute left-1/2 top-0 h-72 w-[760px] -translate-x-1/2 rounded-full bg-blue-100/60 blur-3xl dark:bg-blue-500/5"
      aria-hidden="true"
    ></div>

    <div class="relative mx-auto max-w-7xl">
      <div v-motion="headingMotion" data-landing-motion class="mx-auto max-w-3xl text-center">
        <p
          class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400"
        >
          Masalah operasional hari ini
        </p>
        <h2
          class="mt-4 text-3xl font-semibold tracking-[-0.035em] text-slate-950 sm:text-4xl lg:text-5xl dark:text-white"
        >
          Banyak alat, tetapi bisnis tetap terasa rumit.
        </h2>
        <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">
          Ketika setiap tim bekerja dengan data berbeda, proses melambat dan risiko kesalahan terus
          bertambah.
        </p>
      </div>

      <div class="mt-12 grid gap-4 md:grid-cols-3">
        <div
          v-for="(problem, index) in problems"
          :key="problem.title"
          v-motion="problemMotions[index]"
          data-landing-motion
          class="h-full"
        >
          <article
            class="group relative h-full transform-gpu overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_8px_24px_-18px_rgba(15,23,42,0.28)] transition-[transform,box-shadow,border-color] duration-300 ease-out hover:-translate-y-2 hover:border-blue-300 hover:shadow-[0_24px_48px_-16px_rgba(15,23,42,0.28)] motion-reduce:transform-none dark:border-slate-700 dark:bg-slate-900 dark:shadow-[0_8px_24px_-18px_rgba(0,0,0,0.8)] dark:hover:border-blue-500/50 dark:hover:shadow-[0_24px_48px_-16px_rgba(37,99,235,0.24)]"
          >
            <span
              class="pointer-events-none absolute -right-8 -top-8 size-24 rounded-full bg-blue-100/70 opacity-0 blur-2xl transition-opacity duration-300 group-hover:opacity-100 dark:bg-blue-500/10"
              aria-hidden="true"
            ></span>
            <span
              class="relative grid size-11 place-items-center rounded-xl bg-rose-50 text-rose-600 transition-transform duration-300 group-hover:-rotate-3 group-hover:scale-105 dark:bg-rose-500/10 dark:text-rose-300"
            >
              <component :is="problem.icon" :size="20" aria-hidden="true" />
            </span>
            <h3 class="relative mt-5 text-base font-semibold text-slate-900 dark:text-white">
              {{ problem.title }}
            </h3>
            <p class="relative mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
              {{ problem.description }}
            </p>
          </article>
        </div>
      </div>

      <div class="my-10 flex items-center justify-center gap-3 text-sm font-medium text-blue-600">
        <span class="h-px w-12 bg-blue-200 dark:bg-blue-500/30"></span>
        <span
          class="grid size-9 place-items-center rounded-full bg-blue-600 text-white shadow-lg shadow-blue-600/20"
        >
          <ArrowRight :size="17" aria-hidden="true" />
        </span>
        <span class="h-px w-12 bg-blue-200 dark:bg-blue-500/30"></span>
      </div>

      <div
        v-motion="solutionPanelMotion"
        data-landing-motion
        class="grid overflow-hidden rounded-3xl border border-blue-200 bg-white shadow-[0_24px_70px_-32px_rgba(37,99,235,0.35)] lg:grid-cols-[0.82fr_1.18fr] dark:border-blue-500/25 dark:bg-slate-900"
      >
        <div class="flex flex-col justify-center p-7 sm:p-10 lg:p-12">
          <span
            class="inline-flex w-fit items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-300"
          >
            <Database :size="14" aria-hidden="true" />
            Satu sumber data
          </span>
          <h3
            class="mt-5 text-2xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-3xl dark:text-white"
          >
            Kastra menghubungkan seluruh alur bisnis Anda.
          </h3>
          <p class="mt-4 text-sm leading-7 text-slate-600 sm:text-base dark:text-slate-300">
            Satu transaksi memperbarui stok, kas, jurnal, dan laporan secara otomatis. Tim bekerja
            dengan data yang sama, kapan pun dibutuhkan.
          </p>
          <div class="mt-6 flex flex-wrap gap-2">
            <span
              v-for="label in ['Real-time', 'Otomatis', 'Terukur']"
              :key="label"
              class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 dark:border-slate-700 dark:text-slate-300"
            >
              {{ label }}
            </span>
          </div>
        </div>

        <div
          class="relative min-h-[330px] overflow-hidden bg-blue-600 p-6 sm:min-h-[390px] sm:p-9 dark:bg-blue-700"
        >
          <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.18),transparent_34%),linear-gradient(rgba(255,255,255,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.06)_1px,transparent_1px)] bg-[size:auto,28px_28px,28px_28px]"
            aria-hidden="true"
          ></div>
          <div
            class="pointer-events-none absolute left-1/2 top-1/2 size-44 -translate-x-1/2 -translate-y-1/2 rounded-full border border-dashed border-white/35"
            aria-hidden="true"
          ></div>
          <span
            class="absolute left-1/2 top-1/2 grid size-24 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-[26px] border border-white/30 bg-white text-center text-blue-700 shadow-2xl"
          >
            <span>
              <Database :size="25" class="mx-auto" aria-hidden="true" />
              <span class="mt-1 block text-xs font-semibold">KASTRA</span>
            </span>
          </span>

          <div
            v-for="module in connectedModules"
            :key="module.label"
            :class="[
              'absolute flex w-[124px] items-center gap-2.5 rounded-xl border border-white/20 bg-white/95 p-3 text-slate-900 shadow-xl backdrop-blur sm:w-[145px]',
              module.position,
            ]"
          >
            <span
              class="grid size-8 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600"
            >
              <component :is="module.icon" :size="15" aria-hidden="true" />
            </span>
            <span class="text-[12px] font-semibold sm:text-xs">{{ module.label }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
