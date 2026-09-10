<script setup>
import { Link } from '@inertiajs/vue3'
import { Moon, Sun } from '@lucide/vue'
import AuthVisual from '@/Layouts/AuthVisual.vue'
import ToastNotifications from '@/Components/Feedback/ToastNotifications.vue'
import { useTheme } from '@/Composables/useTheme'

const { isDark, toggleTheme } = useTheme()
</script>

<template>
  <div
    class="grid min-h-screen bg-slate-50 transition-colors duration-300 dark:bg-[#071426] lg:grid-cols-[1.05fr_0.95fr]"
  >
    <AuthVisual />
    <main class="relative flex items-center justify-center px-6 py-12 sm:px-10 lg:px-14">
      <div class="absolute right-6 top-6 flex items-center gap-2">
        <Link
          href="/"
          class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:text-emerald-700 dark:text-slate-300 dark:hover:text-emerald-300"
          >Beranda</Link
        >
        <button
          type="button"
          class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-800 transition hover:border-emerald-300 dark:border-[#29476b] dark:bg-[#102542] dark:text-white"
          :aria-label="isDark ? 'Gunakan mode terang' : 'Gunakan mode malam'"
          @click="toggleTheme"
        >
          <Sun v-if="isDark" :size="18" />
          <Moon v-else :size="18" />
        </button>
      </div>
      <div v-motion-fade-visible-once class="w-full max-w-md">
        <Link href="/" class="mb-10 flex items-center gap-2.5 lg:hidden">
          <span
            class="grid size-10 place-items-center rounded-xl bg-emerald-600 text-xl font-semibold text-white"
            >K</span
          >
          <span class="text-xl font-semibold text-slate-950 dark:text-white"
            >Kastra<span class="text-emerald-600">.</span></span
          >
        </Link>
        <slot></slot>
      </div>
    </main>
    <ToastNotifications />
  </div>
</template>
