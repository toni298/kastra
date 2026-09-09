<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { storeRoute } from '@/Utils/store'
import vReveal from '@/Directives/vReveal'

const props = defineProps({
  store: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  config: { type: Object, default: () => ({}) },
})

const title = computed(() => props.config.title || 'Jelajahi Kategori')

// ----- Mode slide (geser per halaman, bukan scroll) -----
const track = ref(null)
const perView = ref(4) // jumlah kartu terlihat
const page = ref(0) // index halaman aktif

function calcPerView() {
  const w = window.innerWidth
  perView.value = w >= 1280 ? 4 : w >= 1024 ? 3 : w >= 640 ? 2 : 1
  clampPage()
}

const totalPages = computed(() => Math.max(1, Math.ceil(props.categories.length / perView.value)))
const canPrev = computed(() => page.value > 0)
const canNext = computed(() => page.value < totalPages.value - 1)

function clampPage() {
  if (page.value > totalPages.value - 1) page.value = totalPages.value - 1
  if (page.value < 0) page.value = 0
}
function goTo(p) {
  page.value = Math.max(0, Math.min(p, totalPages.value - 1))
}
function prev() {
  goTo(page.value - 1)
}
function next() {
  goTo(page.value + 1)
}

const offset = computed(() => `translateX(-${page.value * 100}%)`)

function onResize() {
  calcPerView()
}
onMounted(() => {
  calcPerView()
  window.addEventListener('resize', onResize)
})
onBeforeUnmount(() => window.removeEventListener('resize', onResize))
</script>

<template>
  <section v-if="categories.length" class="mt-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-end justify-between" v-reveal="'slide-right'">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-slate-400">Kategori</p>
          <h2 class="mt-1.5 text-2xl font-extrabold tracking-tight text-slate-900">{{ title }}</h2>
          <p class="mt-1 text-sm text-slate-500">Temukan produk berdasarkan kategori pilihan</p>
        </div>
        <div v-if="totalPages > 1" class="hidden gap-2 sm:flex">
          <button
            type="button"
            aria-label="Slide sebelumnya"
            :disabled="!canPrev"
            class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:opacity-30"
            @click="prev"
          >
            <ChevronLeft class="h-4 w-4" />
          </button>
          <button
            type="button"
            aria-label="Slide berikutnya"
            :disabled="!canNext"
            class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:opacity-30"
            @click="next"
          >
            <ChevronRight class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Slider -->
    <div class="mx-auto mt-6 max-w-7xl overflow-hidden px-4 sm:px-6 lg:px-8">
      <div
        ref="track"
        class="flex transition-transform duration-500 ease-in-out"
        :style="{ transform: offset }"
      >
        <div
          v-for="(category, i) in categories"
          :key="category.id"
          v-reveal="{ type: 'fade-up', delay: i * 80 }"
          class="w-full shrink-0 px-2"
          :style="{ width: `${100 / perView}%` }"
        >
          <Link
            :href="`${storeRoute(store, '/catalog')}?category=${category.id}`"
            class="group relative flex h-48 w-full items-end overflow-hidden rounded-2xl bg-slate-800 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
          >
            <img
              src="/gambar-banner.png"
              :alt="category.name"
              loading="lazy"
              decoding="async"
              class="absolute inset-0 h-full w-full object-cover opacity-70 transition duration-700 ease-out group-hover:scale-110 group-hover:opacity-60"
            />
            <div
              class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/30 to-transparent"
            ></div>
            <div class="relative w-full p-5">
              <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/70">
                Mulai Belanja
              </p>
              <h3 class="mt-1 text-xl font-extrabold tracking-tight text-white">
                {{ category.name }}
              </h3>
              <span
                class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white"
              >
                <span
                  class="border-b-2 pb-0.5 transition-colors duration-300"
                  :style="{ borderColor: 'currentColor' }"
                  >Belanja Sekarang</span
                >
                <ArrowRight
                  class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1"
                />
              </span>
            </div>
          </Link>
        </div>
      </div>
    </div>

    <!-- Dots -->
    <div v-if="totalPages > 1" class="mt-6 flex justify-center gap-2">
      <button
        v-for="p in totalPages"
        :key="p"
        type="button"
        :aria-label="`Ke halaman ${p}`"
        class="h-2 rounded-full transition-all duration-300"
        :class="page === p - 1 ? 'w-6' : 'w-2 bg-slate-300 hover:bg-slate-400'"
        :style="page === p - 1 ? { backgroundColor: store.theme?.primary_color || '#1d4ed8' } : {}"
        @click="goTo(p - 1)"
      ></button>
    </div>
  </section>
</template>
