<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { ChevronLeft, ChevronRight, Quote, Star } from 'lucide-vue-next'
import vReveal from '@/Directives/vReveal'

const props = defineProps({
  store: { type: Object, required: true },
  config: { type: Object, default: () => ({}) },
})

const primaryColor = computed(() => props.store.theme?.primary_color || '#1d4ed8')

const eyebrow = computed(() => props.config.eyebrow || 'Testimoni')
const title = computed(() => props.config.title || 'Apa Kata Pelanggan')
const subtitle = computed(() => props.config.subtitle || 'Cerita nyata dari pelanggan yang telah mempercayakan kebutuhannya kepada kami.')

const items = computed(() => {
  const list = props.config.items?.length ? props.config.items : []
  return list.map((t) => ({
    ...t,
    rating: Math.max(1, Math.min(5, Number(t.rating) || 5)),
    initials: (t.name || '?').split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase(),
  }))
})

// ----- Slider multi-card (1/2/3 per view, maksimal 3) -----
const perView = ref(3)
function calcPerView() {
  const w = window.innerWidth
  perView.value = w >= 1024 ? 3 : w >= 640 ? 2 : 1
}

const cardWidth = computed(() => 100 / perView.value)
const count = computed(() => items.value.length)
// Jumlah langkah geser: geser 1 kartu per klik, maksimal sampai kartu terakhir terlihat.
const maxIndex = computed(() => Math.max(0, count.value - perView.value))

const active = ref(0)
let timer = null

function clamp() {
  if (active.value > maxIndex.value) active.value = maxIndex.value
  if (active.value < 0) active.value = 0
}
function goTo(i) {
  active.value = Math.max(0, Math.min(i, maxIndex.value))
  restart()
}
function next() {
  active.value = active.value >= maxIndex.value ? 0 : active.value + 1
  restart()
}
function prev() {
  active.value = active.value <= 0 ? maxIndex.value : active.value - 1
  restart()
}
function restart() {
  clearInterval(timer)
  if (maxIndex.value > 0) {
    timer = setInterval(next, 5000)
  }
}
function pause() { clearInterval(timer) }
function resume() { restart() }

function onResize() {
  calcPerView()
  clamp()
}

onMounted(() => {
  calcPerView()
  restart()
  window.addEventListener('resize', onResize)
})
onBeforeUnmount(() => {
  clearInterval(timer)
  window.removeEventListener('resize', onResize)
})

const showNav = computed(() => count.value > perView.value)
</script>

<template>
  <section v-if="count" class="bg-slate-50 py-12 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-2xl text-center" v-reveal="'blur'">
        <p class="text-[11px] font-bold uppercase tracking-[0.25em]" :style="{ color: primaryColor }">{{ eyebrow }}</p>
        <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ title }}</h2>
        <p class="mt-3 text-sm leading-relaxed text-slate-500">{{ subtitle }}</p>
      </div>

      <!-- Slider multi-card -->
      <div
        class="relative mt-8"
        v-reveal="{ type: 'fade-up', delay: 150 }"
        @mouseenter="pause"
        @mouseleave="resume"
      >
        <div class="overflow-hidden px-1 pb-2 pt-6">
          <div
            class="flex transition-transform duration-500 ease-in-out"
            :style="{ transform: `translateX(-${active * cardWidth}%)` }"
          >
            <div
              v-for="(t, i) in items"
              :key="i"
              class="shrink-0 px-2 sm:px-3"
              :style="{ width: `${cardWidth}%` }"
            >
              <figure class="relative flex h-full flex-col rounded-xl border border-slate-200 bg-white px-6 pb-6 pt-10 text-center shadow-sm">
                <div
                  class="absolute -top-5 left-1/2 flex h-10 w-10 -translate-x-1/2 items-center justify-center rounded-full text-white"
                  :style="{ backgroundColor: primaryColor }"
                >
                  <Quote class="h-5 w-5" fill="currentColor" />
                </div>

                <div class="flex justify-center gap-1">
                  <Star
                    v-for="n in 5"
                    :key="n"
                    class="h-4 w-4"
                    :class="n <= t.rating ? 'text-amber-400' : 'text-slate-300'"
                    :fill="n <= t.rating ? 'currentColor' : 'none'"
                  />
                </div>

                <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-slate-700">
                  "{{ t.quote }}"
                </blockquote>

                <figcaption class="mt-6 flex items-center justify-center gap-3 border-t border-slate-100 pt-4">
                  <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
                    :style="{ backgroundColor: primaryColor }"
                  >
                    {{ t.initials }}
                  </div>
                  <div class="text-left">
                    <p class="text-sm font-bold text-slate-900">{{ t.name }}</p>
                    <p v-if="t.role" class="text-xs text-slate-500">{{ t.role }}</p>
                  </div>
                </figcaption>
              </figure>
            </div>
          </div>
        </div>

        <!-- Navigasi panah -->
        <template v-if="showNav">
          <button
            type="button"
            aria-label="Testimoni sebelumnya"
            class="absolute -left-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 lg:-left-5"
            @click="prev"
          >
            <ChevronLeft class="h-5 w-5" />
          </button>
          <button
            type="button"
            aria-label="Testimoni berikutnya"
            class="absolute -right-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 lg:-right-5"
            @click="next"
          >
            <ChevronRight class="h-5 w-5" />
          </button>
        </template>

        <!-- Dots (posisi geser) -->
        <div v-if="showNav" class="mt-8 flex justify-center gap-2">
          <button
            v-for="i in maxIndex + 1"
            :key="i"
            type="button"
            :aria-label="`Ke posisi ${i}`"
            class="h-2 rounded-full transition-all"
            :class="active === i - 1 ? 'w-6' : 'w-2 bg-slate-300 hover:bg-slate-400'"
            :style="active === i - 1 ? { backgroundColor: primaryColor } : {}"
            @click="goTo(i - 1)"
          ></button>
        </div>
      </div>
    </div>
  </section>
</template>
