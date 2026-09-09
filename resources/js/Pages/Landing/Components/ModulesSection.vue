<script setup>
import { Building2, Gauge, LockKeyhole, RefreshCcw } from '@lucide/vue'
import { fadeOnScroll, revealOnScroll } from '@/Utils/landingMotion'
import FeatureGrid from './FeatureGrid.vue'

const headingMotion = revealOnScroll()
const featureGridMotion = revealOnScroll(100, 20)
const advantagesPanelMotion = revealOnScroll(140, 20)

const advantages = [
  {
    icon: Building2,
    title: 'Siap untuk banyak cabang',
    description: 'Kelola seluruh cabang dan outlet dari satu pusat kendali.',
  },
  {
    icon: RefreshCcw,
    title: 'Alur otomatis',
    description: 'Sekali input, data terkait langsung diperbarui tanpa pekerjaan ganda.',
  },
  {
    icon: Gauge,
    title: 'Informasi real-time',
    description: 'Pantau performa bisnis berdasarkan aktivitas operasional terbaru.',
  },
  {
    icon: LockKeyhole,
    title: 'Akses lebih aman',
    description: 'Atur hak akses tim berdasarkan tanggung jawab dan area kerja.',
  },
]

const advantageMotions = advantages.map((_, index) => fadeOnScroll(140 + index * 80))
</script>

<template>
  <section
    id="modul"
    class="relative overflow-hidden px-5 py-20 sm:px-6 lg:px-8 lg:py-28 dark:bg-[#081426]"
  >
    <div
      class="decor-dot-grid pointer-events-none absolute -right-16 top-24 size-56 opacity-40"
      aria-hidden="true"
    ></div>
    <div
      class="pointer-events-none absolute left-0 top-1/3 h-72 w-72 rounded-full bg-blue-100/60 blur-3xl dark:bg-blue-500/5"
      aria-hidden="true"
    ></div>

    <div class="relative mx-auto max-w-7xl">
      <div v-motion="headingMotion" data-landing-motion class="mx-auto max-w-3xl text-center">
        <p
          class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400"
        >
          Modul yang saling terhubung
        </p>
        <h2
          class="mt-4 text-3xl font-semibold tracking-[-0.035em] text-slate-950 sm:text-4xl lg:text-5xl dark:text-white"
        >
          Semua yang bisnis Anda butuhkan, di satu tempat.
        </h2>
        <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">
          Dari transaksi pertama hingga laporan keuangan, setiap modul berbagi data yang sama dan
          bergerak sebagai satu sistem.
        </p>
      </div>

      <div v-motion="featureGridMotion" data-landing-motion class="mt-12">
        <FeatureGrid />
      </div>

      <div
        id="keunggulan"
        v-motion="advantagesPanelMotion"
        data-landing-motion
        class="mt-20 overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900"
      >
        <div
          class="grid gap-8 border-b border-slate-200 p-7 sm:p-9 lg:grid-cols-[0.8fr_1.2fr] lg:items-end dark:border-slate-700"
        >
          <div>
            <p
              class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400"
            >
              Mengapa Kastra
            </p>
            <h2
              class="mt-3 text-2xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-3xl dark:text-white"
            >
              Fondasi yang rapi untuk pertumbuhan bisnis.
            </h2>
          </div>
          <p
            class="text-sm leading-7 text-slate-600 lg:max-w-xl lg:justify-self-end dark:text-slate-300"
          >
            Sistem dibangun untuk menjaga operasional tetap terkontrol saat tim, transaksi, cabang,
            dan outlet terus bertambah.
          </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4">
          <article
            v-for="(advantage, index) in advantages"
            :key="advantage.title"
            v-motion="advantageMotions[index]"
            data-landing-motion
            :class="[
              'p-6 sm:p-7',
              index > 0 ? 'border-t border-slate-200 sm:border-t-0 dark:border-slate-700' : '',
              index % 2 === 1 ? 'sm:border-l' : '',
              index > 1 ? 'sm:border-t lg:border-t-0' : '',
              index > 0 ? 'lg:border-l' : '',
            ]"
          >
            <span
              class="grid size-10 place-items-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20"
            >
              <component :is="advantage.icon" :size="18" aria-hidden="true" />
            </span>
            <h3 class="mt-5 text-sm font-semibold text-slate-950 dark:text-white">
              {{ advantage.title }}
            </h3>
            <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
              {{ advantage.description }}
            </p>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>
