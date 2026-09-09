<script setup>
import { ArrowRight, ShieldCheck, Sparkles } from '@lucide/vue'
import FeatureGrid from './FeatureGrid.vue'
import SectionDecorations from './SectionDecorations.vue'

defineProps({
  registerUrl: { type: String, default: '' },
})

const workflowSteps = [
  'Buat perusahaan dan struktur cabang',
  'Tambahkan produk, pelanggan, dan supplier',
  'Mulai transaksi dan lihat bisnis bergerak',
]
</script>

<template>
  <section
    id="keunggulan"
    class="relative isolate overflow-hidden border-y border-slate-200 bg-white px-6 py-8 transition-colors duration-300 [contain-intrinsic-size:auto_280px] [content-visibility:auto] dark:border-[#1d3859] dark:bg-[#0a1b33] lg:px-8"
  >
    <SectionDecorations variant="dots" />
    <div class="relative z-10 mx-auto grid max-w-7xl gap-8 sm:grid-cols-3">
      <div
        class="group flex gap-4 rounded-2xl border border-transparent p-4 transition-colors duration-300 hover:border-emerald-200 hover:bg-emerald-50/60 dark:hover:border-emerald-400/30 dark:hover:bg-[#102542]"
      >
        <ShieldCheck class="shrink-0 text-emerald-600 dark:text-emerald-400" :size="25" />
        <div>
          <h2 class="text-lg font-semibold tracking-tight text-slate-950 dark:text-white">
            Kontrol penuh
          </h2>
          <p class="mt-1.5 text-sm font-medium leading-6 text-slate-700 dark:text-slate-200">
            Visibilitas operasional dari perusahaan hingga outlet.
          </p>
        </div>
      </div>
      <div
        class="group flex gap-4 rounded-2xl border border-transparent p-4 transition-colors duration-300 hover:border-emerald-200 hover:bg-emerald-50/60 dark:hover:border-emerald-400/30 dark:hover:bg-[#102542]"
      >
        <Sparkles class="shrink-0 text-emerald-600 dark:text-emerald-400" :size="25" />
        <div>
          <h2 class="text-lg font-semibold tracking-tight text-slate-950 dark:text-white">
            Tanpa input ganda
          </h2>
          <p class="mt-1.5 text-sm font-medium leading-6 text-slate-700 dark:text-slate-200">
            Transaksi mengalir otomatis ke stok dan akuntansi.
          </p>
        </div>
      </div>
      <div
        class="group flex gap-4 rounded-2xl border border-transparent p-4 transition-colors duration-300 hover:border-emerald-200 hover:bg-emerald-50/60 dark:hover:border-emerald-400/30 dark:hover:bg-[#102542]"
      >
        <ArrowRight class="shrink-0 text-emerald-600 dark:text-emerald-400" :size="25" />
        <div>
          <h2 class="text-lg font-semibold tracking-tight text-slate-950 dark:text-white">
            Tumbuh terarah
          </h2>
          <p class="mt-1.5 text-sm font-medium leading-6 text-slate-700 dark:text-slate-200">
            Data real-time untuk keputusan bisnis yang lebih tajam.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section
    id="fitur"
    class="relative isolate overflow-hidden px-6 py-20 [contain-intrinsic-size:auto_980px] [content-visibility:auto] lg:px-8 lg:py-28"
  >
    <SectionDecorations variant="circuit" />
    <div class="relative z-10 mx-auto max-w-7xl">
      <div class="max-w-2xl">
        <p class="text-sm font-medium uppercase tracking-[0.18em] text-emerald-700">
          Business operating system
        </p>
        <h2
          class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-5xl"
        >
          Semua yang bisnis Anda butuhkan, di satu tempat.
        </h2>
        <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">
          Dari transaksi pertama sampai laporan keuangan, Kastra menjaga setiap bagian bisnis tetap
          terhubung.
        </p>
      </div>
      <div class="mt-12"><FeatureGrid /></div>
    </div>
  </section>

  <section
    id="cara-kerja"
    class="relative isolate overflow-hidden bg-slate-950 px-6 py-20 text-white [contain-intrinsic-size:auto_620px] [content-visibility:auto] lg:px-8 lg:py-24"
  >
    <SectionDecorations variant="workflow" />
    <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-2">
      <div>
        <p class="text-sm font-medium uppercase tracking-[0.18em] text-emerald-400">
          Mulai dalam hitungan menit
        </p>
        <h2 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">
          Dibuat untuk bekerja, bukan menambah pekerjaan.
        </h2>
        <p class="mt-5 max-w-xl text-lg leading-8 text-slate-300">
          Kastra menghubungkan alur kerja utama sehingga tim Anda dapat fokus melayani pelanggan dan
          mengembangkan bisnis.
        </p>
      </div>
      <div class="space-y-4">
        <div
          v-for="(step, index) in workflowSteps"
          :key="step"
          class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-5"
        >
          <span
            class="grid size-10 shrink-0 place-items-center rounded-xl bg-emerald-500 font-semibold text-slate-950"
          >
            0{{ index + 1 }}
          </span>
          <span class="font-semibold text-slate-100">{{ step }}</span>
        </div>
      </div>
    </div>
  </section>

  <section
    class="relative isolate overflow-hidden px-6 py-20 [contain-intrinsic-size:auto_520px] [content-visibility:auto] lg:px-8"
  >
    <SectionDecorations variant="cta" />
    <div
      class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_15%_50%,rgba(16,185,129,0.14),transparent_28%),radial-gradient(circle_at_85%_50%,rgba(59,130,246,0.10),transparent_28%)] dark:bg-[radial-gradient(circle_at_15%_50%,rgba(16,185,129,0.12),transparent_30%),radial-gradient(circle_at_85%_50%,rgba(37,99,235,0.14),transparent_30%)]"
    ></div>
    <div
      class="relative mx-auto max-w-5xl overflow-hidden rounded-3xl bg-emerald-600 px-6 py-12 text-center text-white shadow-2xl shadow-emerald-600/20 transition-colors duration-300 dark:bg-[#0d2747] dark:shadow-black/30 sm:px-12"
    >
      <div
        class="pointer-events-none absolute -left-20 -top-24 size-64 rounded-full border-[34px] border-white/10 dark:border-emerald-400/10"
      ></div>
      <div
        class="pointer-events-none absolute -bottom-28 -right-16 size-72 rounded-full bg-emerald-400/30 blur-2xl dark:bg-blue-500/20"
      ></div>
      <div
        class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.06)_1px,transparent_1px)] bg-[size:32px_32px] opacity-60 dark:opacity-30"
      ></div>
      <div class="relative z-10">
        <p
          class="text-sm font-medium uppercase tracking-[0.18em] text-emerald-100 dark:text-emerald-300"
        >
          Saatnya naik level
        </p>
        <h2 class="mx-auto mt-4 max-w-2xl text-4xl font-semibold tracking-tight sm:text-5xl">
          Kelola bisnis. Tumbuh bersama.
        </h2>
        <p class="mx-auto mt-4 max-w-xl text-emerald-50">
          Bangun fondasi operasional yang rapi untuk langkah bisnis Anda berikutnya.
        </p>
        <a
          v-if="registerUrl"
          :href="registerUrl"
          class="mt-8 inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3.5 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50"
        >
          Mulai sekarang <ArrowRight :size="17" />
        </a>
      </div>
    </div>
  </section>

  <footer
    class="border-t border-slate-200 bg-white px-6 py-8 transition-colors duration-300 dark:border-[#1d3859] dark:bg-[#0a1b33] lg:px-8"
  >
    <div
      class="mx-auto flex max-w-7xl flex-col justify-between gap-4 text-sm text-slate-500 sm:flex-row"
    >
      <span class="text-lg font-semibold tracking-tight text-slate-950 dark:text-white">
        Kastra<span class="text-emerald-600">.</span>
      </span>
      <span>? {{ new Date().getFullYear() }} Kastra ERP. Kelola Bisnis. Tumbuh Bersama.</span>
    </div>
  </footer>
</template>
