<script setup>
import { ArrowRight, Check, Layers3, Play, ShieldCheck, Sparkles, Zap } from '@lucide/vue'
import { revealOnEnter } from '@/Utils/landingMotion'
import DashboardPreview from './DashboardPreview.vue'

defineProps({
  loginUrl: { type: String, default: '' },
  registerUrl: { type: String, default: '' },
})

const highlights = ['Tanpa kartu kredit', 'Setup cepat', 'Dukungan tim lokal']
const heroContentMotion = revealOnEnter()
const heroPreviewMotion = revealOnEnter(120, 24)
const benefitsMotion = revealOnEnter(220, 16)

const benefits = [
  {
    icon: Layers3,
    title: 'Semua terhubung',
    description: 'Transaksi, stok, dan keuangan bergerak dalam satu alur data.',
  },
  {
    icon: Zap,
    title: 'Kerja lebih cepat',
    description: 'Kurangi input berulang dan fokus pada keputusan yang penting.',
  },
  {
    icon: ShieldCheck,
    title: 'Kontrol menyeluruh',
    description: 'Pantau seluruh cabang dan outlet dengan akses yang aman.',
  },
]
</script>

<template>
  <section
    class="relative isolate overflow-hidden px-5 pb-14 pt-14 sm:px-6 sm:pb-20 sm:pt-20 lg:px-8 lg:pb-24 lg:pt-24"
  >
    <div
      class="pointer-events-none absolute inset-x-0 top-0 -z-20 h-[760px] bg-[radial-gradient(circle_at_78%_20%,rgba(191,219,254,0.72),transparent_34%),radial-gradient(circle_at_12%_8%,rgba(224,231,255,0.7),transparent_28%)] dark:bg-[radial-gradient(circle_at_78%_20%,rgba(37,99,235,0.16),transparent_34%),radial-gradient(circle_at_12%_8%,rgba(79,70,229,0.12),transparent_28%)]"
      aria-hidden="true"
    ></div>
    <div
      class="decor-dot-grid pointer-events-none absolute -left-12 top-20 -z-10 size-52 opacity-50"
      aria-hidden="true"
    ></div>
    <div
      class="pointer-events-none absolute right-[4%] top-20 -z-10 hidden size-40 rounded-full border border-blue-200/70 dark:border-blue-500/15 lg:block"
      aria-hidden="true"
    ></div>

    <div
      class="mx-auto grid max-w-7xl items-center gap-14 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16 xl:gap-24"
    >
      <div v-motion="heroContentMotion" data-landing-motion class="text-center lg:text-left">
        <div
          class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50/90 px-3.5 py-2 text-xs font-medium text-blue-700 shadow-sm dark:border-blue-500/25 dark:bg-blue-500/10 dark:text-blue-300"
        >
          <Sparkles :size="14" aria-hidden="true" />
          ERP modern untuk bisnis Indonesia
        </div>

        <h1
          class="mx-auto mt-6 max-w-2xl text-4xl font-semibold leading-[1.08] tracking-[-0.045em] text-slate-950 sm:text-5xl lg:mx-0 lg:text-[59px] dark:text-white"
        >
          Kendalikan bisnis dalam
          <span class="text-blue-600 dark:text-blue-400">satu sistem.</span>
        </h1>

        <p
          class="mx-auto mt-6 max-w-xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8 lg:mx-0 dark:text-slate-300"
        >
          Kastra menyatukan penjualan, persediaan, pembelian, kas, dan akuntansi agar bisnis Anda
          bergerak lebih cepat dengan data yang selalu terhubung.
        </p>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row lg:justify-start">
          <a
            v-if="registerUrl"
            :href="registerUrl"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3.5 text-sm font-medium text-white shadow-xl shadow-blue-600/25 transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-blue-600/35"
          >
            Mulai Gratis
            <ArrowRight :size="17" aria-hidden="true" />
          </a>
          <a
            href="#cara-kerja"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white/80 px-5 py-3.5 text-sm font-medium text-slate-700 backdrop-blur transition hover:border-blue-300 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:border-blue-500 dark:hover:text-blue-400"
          >
            <Play :size="16" fill="currentColor" aria-hidden="true" />
            Lihat Cara Kerja
          </a>
        </div>

        <div
          class="mt-6 flex flex-wrap justify-center gap-x-5 gap-y-2 text-xs font-medium text-slate-500 lg:justify-start dark:text-slate-400"
        >
          <span v-for="item in highlights" :key="item" class="inline-flex items-center gap-1.5">
            <Check :size="14" class="text-blue-600 dark:text-blue-400" aria-hidden="true" />
            {{ item }}
          </span>
        </div>
      </div>

      <div v-motion="heroPreviewMotion" data-landing-motion class="min-w-0">
        <DashboardPreview />
      </div>
    </div>

    <div
      v-motion="benefitsMotion"
      data-landing-motion
      class="mx-auto mt-16 grid max-w-7xl overflow-hidden rounded-2xl border border-slate-200 bg-white/80 shadow-sm backdrop-blur sm:grid-cols-3 lg:mt-20 dark:border-slate-800 dark:bg-slate-900/70"
    >
      <article
        v-for="(benefit, index) in benefits"
        :key="benefit.title"
        :class="[
          'flex gap-3.5 p-5 sm:p-6',
          index > 0
            ? 'border-t border-slate-200 sm:border-l sm:border-t-0 dark:border-slate-800'
            : '',
        ]"
      >
        <span
          class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"
        >
          <component :is="benefit.icon" :size="19" aria-hidden="true" />
        </span>
        <div>
          <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ benefit.title }}</h2>
          <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
            {{ benefit.description }}
          </p>
        </div>
      </article>
    </div>
  </section>
</template>
