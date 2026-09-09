<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { storeRoute } from '@/Utils/store'
import vReveal from '@/Directives/vReveal'

const props = defineProps({
  store: { type: Object, required: true },
  config: { type: Object, default: () => ({}) },
})

const theme = computed(() => props.store.theme ?? {})
const primaryColor = computed(() => theme.value.primary_color || '#1d4ed8')

// Hero slider — pakai banner/logo dari settings bila ada.
const slides = computed(() => {
  const t = theme.value
  const base = t.hero_title
    ? {
        eyebrow: t.tagline || 'Selamat Datang',
        title: t.hero_title,
        subtitle: t.hero_subtitle || '',
        image: t.banner_url || '/gambar-banner.png',
      }
    : null

  const defaults = [
    { image: '/gambar-banner.png', eyebrow: 'Promo & Penawaran', title: 'Produk Terbaik untuk Kebutuhan Anda', subtitle: 'Belanja sekarang dan nikmati penawaran spesial.' },
    { image: '/gambar-banner.png', eyebrow: 'Koleksi Terbaru', title: 'Kualitas Premium, Harga Bersahabat', subtitle: 'Gratis konsultasi untuk setiap pesanan.' },
    { image: '/gambar-banner.png', eyebrow: 'Penjual Terbaik Bulanan', title: 'Diskon Hingga Akhir Minggu Ini', subtitle: 'Hanya sampai akhir minggu ini.' },
  ]

  return base ? [base, ...defaults.slice(1)] : defaults
})

const activeSlide = ref(0)
let timer = null

function goTo(index) {
  activeSlide.value = (index + slides.value.length) % slides.value.length
  restart()
}
function next() { goTo(activeSlide.value + 1) }
function prev() { goTo(activeSlide.value - 1) }
function restart() {
  clearInterval(timer)
  timer = setInterval(() => {
    activeSlide.value = (activeSlide.value + 1) % slides.value.length
  }, 6000)
}

onMounted(restart)
onBeforeUnmount(() => clearInterval(timer))
</script>

<template>
  <section class="relative w-full">
    <div class="group relative w-full overflow-hidden bg-slate-900 shadow-xl">
      <div
        class="flex transition-transform duration-700 ease-out"
        :style="{ transform: `translateX(-${activeSlide * 100}%)` }"
      >
        <div
          v-for="(slide, index) in slides"
          :key="index"
          class="relative aspect-[16/10] w-full shrink-0 sm:aspect-[21/9] lg:aspect-[3/1]"
        >
          <img
            :src="slide.image"
            :alt="slide.title"
            :loading="index === 0 ? 'eager' : 'lazy'"
            :fetchpriority="index === 0 ? 'high' : 'auto'"
            decoding="async"
            class="absolute inset-0 h-full w-full scale-105 object-cover transition-transform duration-[2000ms] ease-out group-hover:scale-100"
          />
          <div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 via-slate-900/40 to-transparent"></div>
          <div class="absolute inset-0 bg-gradient-to-t from-slate-900/50 via-transparent to-transparent"></div>
          <div class="absolute inset-0 flex flex-col justify-center px-6 sm:px-14 lg:px-20">
            <div class="mx-auto w-full max-w-7xl">
              <span
                v-reveal="{ type: 'fade-up', delay: 100, once: false }"
                class="inline-flex w-fit items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-white backdrop-blur-sm ring-1 ring-white/20"
              >
                <span class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: primaryColor }"></span>
                {{ slide.eyebrow }}
              </span>
              <h2 class="mt-4 max-w-lg text-2xl font-extrabold leading-[1.1] tracking-tight text-white drop-shadow-sm sm:text-4xl lg:text-5xl">{{ slide.title }}</h2>
              <p class="mt-3 hidden max-w-md text-sm leading-relaxed text-slate-200 sm:block sm:text-base">{{ slide.subtitle }}</p>
              <div class="mt-6 flex items-center gap-3">
                <Link
                  :href="storeRoute(store, '/catalog')"
                  class="group/btn inline-flex items-center gap-2 rounded-full px-6 py-3 text-xs font-bold uppercase tracking-wider text-white shadow-lg transition-all duration-300 hover:gap-3 hover:brightness-110 hover:shadow-xl"
                  :style="{ backgroundColor: primaryColor }"
                >
                  Belanja Sekarang
                  <ArrowRight class="h-4 w-4 transition-transform duration-300 group-hover/btn:translate-x-0.5" />
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <button
        type="button"
        aria-label="Slide sebelumnya"
        class="absolute left-4 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-slate-800 opacity-0 shadow-lg backdrop-blur transition-all duration-300 hover:scale-105 hover:bg-white group-hover:opacity-100"
        @click="prev"
      >
        <ChevronLeft class="h-5 w-5" />
      </button>
      <button
        type="button"
        aria-label="Slide berikutnya"
        class="absolute right-4 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-slate-800 opacity-0 shadow-lg backdrop-blur transition-all duration-300 hover:scale-105 hover:bg-white group-hover:opacity-100"
        @click="next"
      >
        <ChevronRight class="h-5 w-5" />
      </button>
      <div class="absolute bottom-5 left-1/2 flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900/40 px-3 py-2 backdrop-blur-sm">
        <button
          v-for="(slide, index) in slides"
          :key="index"
          type="button"
          :aria-label="`Ke slide ${index + 1}`"
          class="h-1.5 rounded-full transition-all duration-300"
          :class="activeSlide === index ? 'w-7 bg-white' : 'w-1.5 bg-white/50 hover:bg-white/80'"
          @click="goTo(index)"
        ></button>
      </div>
    </div>
  </section>
</template>
