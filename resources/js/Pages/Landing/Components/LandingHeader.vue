<script setup>
import { Menu, Moon, Sun, X } from '@lucide/vue'

defineProps({
  loginUrl: { type: String, default: '' },
  registerUrl: { type: String, default: '' },
})

const navigation = [
  { label: 'Solusi', href: '#solusi' },
  { label: 'Cara Kerja', href: '#cara-kerja' },
  { label: 'Modul', href: '#modul' },
  { label: 'Keunggulan', href: '#keunggulan' },
]
</script>

<template>
  <header
    class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl transition-colors duration-300 dark:border-slate-800 dark:bg-[#081426]/90"
  >
    <nav
      class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-5 sm:px-6 lg:px-8"
      aria-label="Navigasi utama"
    >
      <a href="#top" class="group flex items-center gap-2.5" aria-label="Kastra, kembali ke atas">
        <span
          class="grid size-9 place-items-center rounded-xl bg-blue-600 text-base font-semibold text-white shadow-lg shadow-blue-600/20 transition-transform group-hover:-rotate-3 group-hover:scale-105"
        >
          K
        </span>
        <span class="text-xl font-semibold tracking-[-0.03em] text-slate-950 dark:text-white">
          Kastra<span class="text-blue-600 dark:text-blue-400">.</span>
        </span>
      </a>

      <div class="hidden items-center gap-7 lg:flex">
        <a
          v-for="item in navigation"
          :key="item.label"
          :href="item.href"
          class="text-sm font-semibold text-slate-600 transition-colors hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400"
        >
          {{ item.label }}
        </a>
      </div>

      <div class="hidden items-center gap-2 lg:flex">
        <button
          type="button"
          data-theme-toggle
          class="grid size-10 place-items-center rounded-xl text-slate-600 transition hover:bg-slate-100 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-blue-400"
          aria-label="Aktifkan tema gelap"
          title="Ganti tema tampilan"
        >
          <Sun :size="18" class="hidden dark:block" aria-hidden="true" />
          <Moon :size="18" class="dark:hidden" aria-hidden="true" />
        </button>
        <a
          v-if="loginUrl"
          :href="loginUrl"
          class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-blue-400"
        >
          Masuk
        </a>
        <a
          v-if="registerUrl"
          :href="registerUrl"
          class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-blue-600/30"
        >
          Mulai Gratis
        </a>
      </div>

      <button
        type="button"
        data-menu-toggle
        class="grid size-10 place-items-center rounded-xl text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 lg:hidden"
        aria-label="Buka menu"
        aria-expanded="false"
        aria-controls="landing-mobile-menu"
      >
        <X :size="22" class="hidden" data-menu-close-icon aria-hidden="true" />
        <Menu :size="22" data-menu-open-icon aria-hidden="true" />
      </button>
    </nav>

    <div
      id="landing-mobile-menu"
      class="hidden border-t border-slate-200/80 bg-white px-5 pb-6 pt-4 shadow-xl shadow-slate-950/5 dark:border-slate-800 dark:bg-[#081426] lg:hidden"
    >
      <div class="mx-auto flex max-w-7xl flex-col">
        <a
          v-for="item in navigation"
          :key="item.label"
          :href="item.href"
          data-menu-link
          class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700 dark:text-slate-200 dark:hover:bg-blue-500/10 dark:hover:text-blue-300"
        >
          {{ item.label }}
        </a>

        <div
          class="mt-3 grid grid-cols-[auto_1fr_1fr] gap-2 border-t border-slate-200 pt-4 dark:border-slate-800"
        >
          <button
            type="button"
            data-theme-toggle
            class="grid size-11 place-items-center rounded-xl border border-slate-200 text-slate-700 transition hover:border-blue-300 hover:text-blue-600 dark:border-slate-700 dark:text-slate-200 dark:hover:border-blue-500 dark:hover:text-blue-400"
            aria-label="Aktifkan tema gelap"
            title="Ganti tema tampilan"
          >
            <Sun :size="18" class="hidden dark:block" aria-hidden="true" />
            <Moon :size="18" class="dark:hidden" aria-hidden="true" />
          </button>
          <a
            v-if="loginUrl"
            :href="loginUrl"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-white"
          >
            Masuk
          </a>
          <a
            v-if="registerUrl"
            :href="registerUrl"
            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white"
          >
            Mulai Gratis
          </a>
        </div>
      </div>
    </div>
  </header>
</template>
