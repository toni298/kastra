<script setup>
import { ArrowRight, Check, Mail, MapPin } from '@lucide/vue'
import { revealOnScroll } from '@/Utils/landingMotion'

defineProps({
  loginUrl: { type: String, default: '' },
  registerUrl: { type: String, default: '' },
})

const productLinks = [
  { label: 'Solusi', href: '#solusi' },
  { label: 'Cara Kerja', href: '#cara-kerja' },
  { label: 'Modul', href: '#modul' },
  { label: 'Keunggulan', href: '#keunggulan' },
]

const ctaMotion = revealOnScroll(80, 20)
const assurances = ['Setup cepat', 'Tanpa kartu kredit', 'Dukungan tim lokal']
const currentYear = new Date().getFullYear()
</script>

<template>
  <section
    class="relative overflow-hidden px-5 pb-20 pt-6 sm:px-6 lg:px-8 lg:pb-24 dark:bg-[#081426]"
  >
    <div
      class="pointer-events-none absolute inset-x-0 bottom-0 h-72 bg-gradient-to-t from-blue-50/70 to-transparent dark:from-blue-950/15"
      aria-hidden="true"
    ></div>

    <div
      v-motion="ctaMotion"
      data-landing-motion
      class="relative mx-auto max-w-7xl overflow-hidden rounded-[30px] bg-blue-600 px-6 py-12 text-center text-white shadow-2xl shadow-blue-600/20 sm:px-10 sm:py-14 lg:px-16"
    >
      <div
        class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.07)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.07)_1px,transparent_1px)] bg-[size:32px_32px] opacity-50"
        aria-hidden="true"
      ></div>
      <div
        class="pointer-events-none absolute -left-20 -top-24 size-64 rounded-full border-[34px] border-white/10"
        aria-hidden="true"
      ></div>
      <div
        class="pointer-events-none absolute -bottom-28 -right-16 size-72 rounded-full bg-cyan-300/20 blur-2xl"
        aria-hidden="true"
      ></div>

      <div class="relative mx-auto max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">
          Saatnya bekerja lebih terhubung
        </p>
        <h2 class="mt-4 text-3xl font-semibold tracking-[-0.04em] sm:text-4xl lg:text-5xl">
          Siap membangun operasional bisnis yang lebih rapi?
        </h2>
        <p class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-blue-100 sm:text-base">
          Satukan tim, transaksi, stok, dan keuangan dalam sistem yang tumbuh bersama bisnis Anda.
        </p>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
          <a
            v-if="registerUrl"
            :href="registerUrl"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3.5 text-sm font-semibold text-blue-700 shadow-xl transition hover:-translate-y-0.5 hover:bg-blue-50"
          >
            Mulai Gratis
            <ArrowRight :size="17" aria-hidden="true" />
          </a>
          <a
            v-if="loginUrl"
            :href="loginUrl"
            class="inline-flex items-center justify-center rounded-xl border border-white/30 bg-white/10 px-5 py-3.5 text-sm font-medium text-white backdrop-blur transition hover:bg-white/20"
          >
            Masuk ke Kastra
          </a>
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-x-5 gap-y-2 text-xs text-blue-100">
          <span v-for="item in assurances" :key="item" class="inline-flex items-center gap-1.5">
            <Check :size="14" aria-hidden="true" />
            {{ item }}
          </span>
        </div>
      </div>
    </div>
  </section>

  <footer
    class="border-t border-slate-200 bg-white px-5 py-12 transition-colors duration-300 sm:px-6 lg:px-8 dark:border-slate-800 dark:bg-[#07111f]"
  >
    <div class="mx-auto grid max-w-7xl gap-10 md:grid-cols-[1.4fr_0.7fr_1fr]">
      <div class="max-w-sm">
        <a
          href="#top"
          class="inline-flex items-center gap-2.5"
          aria-label="Kastra, kembali ke atas"
        >
          <span
            class="grid size-9 place-items-center rounded-xl bg-blue-600 font-semibold text-white"
          >
            K
          </span>
          <span class="text-xl font-semibold tracking-[-0.03em] text-slate-950 dark:text-white">
            Kastra<span class="text-blue-600 dark:text-blue-400">.</span>
          </span>
        </a>
        <p class="mt-4 text-sm leading-6 text-slate-500 dark:text-slate-400">
          Sistem operasional terpadu untuk membantu bisnis Indonesia bekerja lebih efisien dan
          bertumbuh dengan data yang dapat dipercaya.
        </p>
      </div>

      <nav aria-label="Navigasi footer">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-900 dark:text-white">
          Produk
        </p>
        <div class="mt-4 flex flex-col items-start gap-3">
          <a
            v-for="link in productLinks"
            :key="link.label"
            :href="link.href"
            class="text-sm font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
          >
            {{ link.label }}
          </a>
        </div>
      </nav>

      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-900 dark:text-white">
          Hubungi kami
        </p>
        <div class="mt-4 space-y-3">
          <p class="flex items-start gap-2.5 text-sm text-slate-500 dark:text-slate-400">
            <Mail
              :size="16"
              class="mt-0.5 shrink-0 text-blue-600 dark:text-blue-400"
              aria-hidden="true"
            />
            halo@kastra.id
          </p>
          <p class="flex items-start gap-2.5 text-sm text-slate-500 dark:text-slate-400">
            <MapPin
              :size="16"
              class="mt-0.5 shrink-0 text-blue-600 dark:text-blue-400"
              aria-hidden="true"
            />
            Indonesia
          </p>
        </div>
      </div>
    </div>

    <div
      class="mx-auto mt-10 flex max-w-7xl flex-col gap-2 border-t border-slate-200 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:text-slate-400"
    >
      <span>© {{ currentYear }} Kastra ERP. Hak cipta dilindungi.</span>
      <span>Kelola Bisnis. Tumbuh Bersama.</span>
    </div>
  </footer>
</template>
