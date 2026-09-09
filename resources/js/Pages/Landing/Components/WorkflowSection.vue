<script setup>
import { ArrowRight, BarChart3, Building2, PackagePlus, ShoppingCart } from '@lucide/vue'
import { fadeOnScroll, revealOnScroll } from '@/Utils/landingMotion'

const headingMotion = revealOnScroll()
const resultMotion = revealOnScroll(180, 16)

const steps = [
  {
    number: '01',
    icon: Building2,
    title: 'Siapkan struktur bisnis',
    description: 'Tambahkan perusahaan, cabang, outlet, dan tim sesuai cara bisnis Anda berjalan.',
  },
  {
    number: '02',
    icon: PackagePlus,
    title: 'Lengkapi data utama',
    description: 'Masukkan produk, pelanggan, supplier, gudang, serta saldo awal dengan rapi.',
  },
  {
    number: '03',
    icon: ShoppingCart,
    title: 'Mulai bertransaksi',
    description: 'Jalankan operasional harian dan biarkan Kastra memperbarui seluruh data terkait.',
  },
]

const stepMotions = steps.map((_, index) => fadeOnScroll(index * 100))
</script>

<template>
  <section
    id="cara-kerja"
    class="relative overflow-hidden bg-slate-950 px-5 py-20 text-white sm:px-6 lg:px-8 lg:py-28"
  >
    <div
      class="decor-dark-grid pointer-events-none absolute inset-y-0 right-0 w-3/5 opacity-50"
      aria-hidden="true"
    ></div>
    <div
      class="pointer-events-none absolute -left-32 top-1/2 size-80 -translate-y-1/2 rounded-full bg-blue-600/15 blur-3xl"
      aria-hidden="true"
    ></div>
    <div
      class="pointer-events-none absolute right-[8%] top-14 size-32 rounded-full border border-blue-400/15"
      aria-hidden="true"
    ></div>

    <div class="relative mx-auto max-w-7xl">
      <div
        v-motion="headingMotion"
        data-landing-motion
        class="grid items-end gap-8 lg:grid-cols-[1fr_0.72fr]"
      >
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-400">
            Mulai dalam hitungan menit
          </p>
          <h2
            class="mt-4 max-w-3xl text-3xl font-semibold tracking-[-0.035em] sm:text-4xl lg:text-5xl"
          >
            Dari setup hingga laporan, alurnya tetap sederhana.
          </h2>
        </div>
        <p class="max-w-xl text-base leading-7 text-slate-300 lg:justify-self-end">
          Kastra dirancang agar tim dapat mulai bekerja tanpa proses implementasi yang
          berbelit-belit.
        </p>
      </div>

      <div class="relative isolate mt-14 grid gap-5 lg:grid-cols-3">
        <div
          class="pointer-events-none absolute -bottom-4 left-[16%] right-[16%] z-0 hidden border-t border-dashed border-blue-400/30 lg:block"
          aria-hidden="true"
        ></div>

        <div
          v-for="(step, index) in steps"
          :key="step.number"
          v-motion="stepMotions[index]"
          data-landing-motion
          class="relative z-10 h-full"
        >
          <article
            class="group relative h-full transform-gpu overflow-hidden rounded-2xl border border-white/10 bg-white/[0.055] p-6 shadow-[0_8px_24px_-18px_rgba(0,0,0,0.7)] backdrop-blur-sm transition-[transform,box-shadow,border-color,background-color] duration-300 ease-out hover:-translate-y-2 hover:border-blue-400/50 hover:bg-white/[0.09] hover:shadow-[0_26px_52px_-16px_rgba(30,64,175,0.48)] motion-reduce:transform-none sm:p-7"
          >
            <span
              class="pointer-events-none absolute -right-8 -top-8 size-24 rounded-full bg-blue-400/20 opacity-0 blur-2xl transition-opacity duration-300 group-hover:opacity-100"
              aria-hidden="true"
            ></span>
            <div class="relative flex items-center justify-between">
              <span
                class="relative z-10 grid size-12 place-items-center rounded-2xl bg-blue-600 text-sm font-semibold text-white shadow-lg shadow-blue-950/40"
              >
                {{ step.number }}
              </span>
              <ArrowRight
                v-if="index < steps.length - 1"
                :size="19"
                class="text-blue-400 lg:hidden"
                aria-hidden="true"
              />
            </div>

            <span
              class="relative mt-8 grid size-11 place-items-center rounded-xl bg-white/10 text-blue-300 transition duration-300 group-hover:-rotate-3 group-hover:scale-105 group-hover:bg-blue-500/15 group-hover:text-blue-200"
            >
              <component :is="step.icon" :size="20" aria-hidden="true" />
            </span>
            <h3 class="relative mt-5 text-lg font-semibold">{{ step.title }}</h3>
            <p class="relative mt-2 text-sm leading-6 text-slate-400">{{ step.description }}</p>
          </article>
        </div>
      </div>

      <div
        v-motion="resultMotion"
        data-landing-motion
        class="mt-8 flex flex-col items-start justify-between gap-5 rounded-2xl border border-blue-400/20 bg-blue-500/10 p-5 sm:flex-row sm:items-center sm:p-6"
      >
        <div class="flex items-start gap-3">
          <span
            class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-500 text-white shadow-lg shadow-blue-950/30"
          >
            <BarChart3 :size="19" aria-hidden="true" />
          </span>
          <div>
            <p class="text-sm font-semibold text-white">Hasilnya langsung terlihat</p>
            <p class="mt-1 text-xs leading-5 text-blue-100/75 sm:text-sm">
              Dashboard, stok, arus kas, dan laporan selalu diperbarui dari aktivitas terbaru.
            </p>
          </div>
        </div>
        <a
          href="#modul"
          class="inline-flex shrink-0 items-center gap-2 text-sm font-medium text-blue-300 transition hover:text-white"
        >
          Jelajahi modul
          <ArrowRight :size="16" aria-hidden="true" />
        </a>
      </div>
    </div>
  </section>
</template>
