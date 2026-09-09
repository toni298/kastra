<script setup>
import { BarChart3, Boxes, CheckCircle2, ShieldCheck } from '@lucide/vue'

const highlights = [
  { icon: Boxes, text: 'Stok lintas cabang selalu terpantau' },
  { icon: BarChart3, text: 'Laporan bisnis tersedia real-time' },
  { icon: ShieldCheck, text: 'Akses tim aman dan terkontrol' },
]
</script>

<template>
  <section
    class="relative hidden overflow-hidden bg-emerald-600 p-10 text-white lg:flex lg:flex-col lg:justify-between xl:p-14 dark:bg-[#0d2747]"
  >
    <div
      class="absolute -left-24 -top-24 size-72 rounded-full border-[42px] border-white/10 dark:border-emerald-400/10"
    ></div>
    <div
      class="absolute -bottom-24 -right-16 size-72 rounded-full bg-emerald-400/30 blur-3xl dark:bg-blue-500/20"
    ></div>
    <div
      class="absolute inset-0 bg-[radial-gradient(circle,rgba(255,255,255,0.18)_1px,transparent_1px)] bg-[size:28px_28px] opacity-30"
    ></div>

    <a href="/" class="relative z-10 flex items-center gap-3">
      <span
        class="grid size-11 place-items-center rounded-xl bg-white text-xl font-semibold text-emerald-700"
        >K</span
      >
      <span class="text-2xl font-semibold tracking-tight">Kastra.</span>
    </a>

    <div v-motion-slide-visible-once-left class="relative z-10 max-w-lg">
      <p
        class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-100 dark:text-emerald-300"
      >
        Business operating system
      </p>
      <h2 class="mt-5 text-4xl font-semibold leading-tight tracking-tight xl:text-5xl">
        Operasional lebih rapi. Keputusan lebih cepat.
      </h2>
      <p class="mt-5 text-base leading-7 text-emerald-50 dark:text-slate-200">
        Kelola penjualan, inventory, pembelian, dan akuntansi dalam satu sistem yang saling
        terhubung.
      </p>
      <div class="mt-8 space-y-4">
        <div
          v-for="item in highlights"
          :key="item.text"
          class="flex items-center gap-3 text-sm font-semibold"
        >
          <span class="grid size-9 place-items-center rounded-xl bg-white/15"
            ><component :is="item.icon" :size="18"
          /></span>
          {{ item.text }}
        </div>
      </div>
    </div>

    <div
      class="relative z-10 flex items-center gap-2 text-xs font-medium text-emerald-100 dark:text-slate-300"
    >
      <CheckCircle2 :size="15" /> Dibangun untuk bisnis Indonesia
    </div>
  </section>
</template>
