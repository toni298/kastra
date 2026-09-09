<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight } from 'lucide-vue-next'
import { storeRoute } from '@/Utils/store'

const props = defineProps({
  store: { type: Object, required: true },
  config: { type: Object, default: () => ({}) },
})

const eyebrow = computed(() => props.config.eyebrow || 'Penawaran Terbatas')
const title = computed(() => props.config.title || 'Hemat Besar')
const highlight = computed(() => props.config.highlight || 'Minggu Ini')
const subtitle = computed(
  () =>
    props.config.subtitle ||
    'Dapatkan produk pilihan dengan potongan harga spesial. Hanya sampai akhir minggu.'
)
const buttonLabel = computed(() => props.config.button_label || 'Shop Now')
const darkTitle = computed(
  () => props.config.dark_title || 'Diskon hingga 50% untuk produk pilihan'
)
const darkSubtitle = computed(
  () =>
    props.config.dark_subtitle ||
    'Cek halaman katalog untuk melihat seluruh produk promo yang tersedia di toko kami.'
)

const marqueeItems = computed(() => {
  const raw = props.config.marquee
  if (typeof raw === 'string' && raw.trim()) {
    return raw
      .split('\n')
      .map((s) => s.trim())
      .filter(Boolean)
  }
  if (Array.isArray(raw) && raw.length) return raw
  return [
    'Gratis ongkir pengambilan di toko',
    'Pembayaran aman',
    'Garansi produk 30 hari',
    'Promo spesial minggu ini',
  ]
})
</script>

<template>
  <section class="w-full">
    <div
      v-motion
      :initial="{ opacity: 0, y: 40 }"
      :visible-once="{ opacity: 1, y: 0, transition: { duration: 600 } }"
      class="relative w-full overflow-hidden bg-slate-900"
    >
      <!-- Decorative elements (CSS only) -->
      <div
        class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-blue-700/20 blur-2xl"
      ></div>
      <div
        class="pointer-events-none absolute -bottom-28 right-1/3 h-64 w-64 rounded-full bg-sky-500/10 blur-2xl"
      ></div>
      <div
        class="pointer-events-none absolute right-10 top-8 hidden h-24 w-24 rotate-12 border-2 border-white/10 md:block"
      ></div>
      <div
        class="pointer-events-none absolute bottom-16 right-1/4 hidden h-12 w-12 rounded-full border-2 border-blue-500/30 md:block"
      ></div>

      <!-- Background full-bleed, isi tetap sejajar dengan section max-w-7xl. -->
      <div class="relative w-full">
        <div
          class="promo-light-panel pointer-events-none absolute inset-y-0 left-0 hidden md:block"
        ></div>
        <div class="relative mx-auto grid max-w-7xl md:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
          <!-- Light panel kiri dengan diagonal separator -->
          <div
            class="promo-panel relative flex flex-col justify-center bg-slate-100 px-6 py-12 text-center sm:px-10 md:bg-transparent md:py-16 md:text-left"
          >
            <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-blue-700">
              {{ eyebrow }}
            </p>
            <h2
              class="mt-3 text-3xl font-black leading-[1.05] tracking-tight text-slate-900 sm:text-4xl lg:text-5xl"
            >
              {{ title }}
              <span class="block text-blue-700">{{ highlight }}</span>
            </h2>
            <p class="mx-auto mt-4 max-w-xs text-sm leading-relaxed text-slate-600 md:mx-0">
              {{ subtitle }}
            </p>
            <Link
              :href="storeRoute(store, '/catalog')"
              class="promo-button mx-auto mt-6 inline-flex w-fit items-center gap-2 bg-slate-900 px-6 py-3 pr-8 text-xs font-bold uppercase tracking-widest text-white transition hover:bg-blue-700 md:mx-0"
            >
              {{ buttonLabel }}
              <ArrowRight class="h-4 w-4" />
            </Link>
          </div>

          <!-- Area dark dengan konten pendukung (rata tengah vertikal & horizontal) -->
          <div
            class="relative flex items-center justify-center px-6 py-12 text-center sm:px-10 md:py-16"
          >
            <div class="max-w-sm">
              <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-blue-300">
                {{ store.company }} — {{ store.branch }}
              </p>
              <p class="mt-3 text-2xl font-extrabold leading-snug text-white sm:text-3xl">
                {{ darkTitle }}
              </p>
              <p class="mt-3 text-sm leading-relaxed text-slate-300">
                {{ darkSubtitle }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Running text / marquee (CSS animation) -->
      <div class="relative border-t border-white/10 bg-slate-950 py-2.5">
        <div class="marquee flex w-max items-center gap-8 whitespace-nowrap">
          <template v-for="copy in 2" :key="copy">
            <span
              v-for="item in marqueeItems"
              :key="`${copy}-${item}`"
              class="flex items-center gap-8 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-300"
            >
              {{ item }}
              <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
            </span>
          </template>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Diagonal separator antara light panel dan dark background (desktop) */
@media (min-width: 768px) {
  .promo-light-panel {
    width: calc(30% + max((100vw - 80rem) / 2, 0px));
    background: rgb(241 245 249);
    clip-path: polygon(0 0, 100% 0, 88% 100%, 0 100%);
  }

  .promo-button {
    clip-path: polygon(0 0, 100% 0, 88% 100%, 0 100%);
  }
}

@keyframes promo-marquee {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-50%);
  }
}

.marquee {
  animation: promo-marquee 28s linear infinite;
}

.marquee:hover {
  animation-play-state: paused;
}
</style>
