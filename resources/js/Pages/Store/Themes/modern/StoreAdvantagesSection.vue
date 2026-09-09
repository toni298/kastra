<script setup>
import { computed } from 'vue'
import { BadgePercent, Headset, PencilRuler, RotateCcw, Ruler, ShieldCheck, Truck, Zap } from 'lucide-vue-next'
import vReveal from '@/Directives/vReveal'

const props = defineProps({
  store: { type: Object, required: true },
  config: { type: Object, default: () => ({}) },
})

const iconMap = {
  ruler: PencilRuler,
  percent: BadgePercent,
  measure: Ruler,
  shield: ShieldCheck,
  truck: Truck,
  refresh: RotateCcw,
  headset: Headset,
  zap: Zap,
}

const eyebrow = computed(() => props.config.eyebrow || 'Kenapa Memilih Kami')
const title = computed(() => props.config.title || 'Keunggulan Toko')
const subtitle = computed(() => props.config.subtitle || 'Kami berkomitmen memberikan produk dan layanan terbaik untuk setiap pelanggan.')
const primaryColor = computed(() => props.store.theme?.primary_color || '#3b82f6')

// Versi lebih terang dari warna primer, agar kontras di atas latar gelap (slate-900).
const accentColor = computed(() => {
  const hex = primaryColor.value.replace('#', '')
  if (hex.length !== 6) return '#60a5fa'
  const r = parseInt(hex.slice(0, 2), 16)
  const g = parseInt(hex.slice(2, 4), 16)
  const b = parseInt(hex.slice(4, 6), 16)
  const lift = (c) => Math.round(c + (255 - c) * 0.45)
  return `rgb(${lift(r)}, ${lift(g)}, ${lift(b)})`
})

const items = computed(() => {
  const list = props.config.items?.length ? props.config.items : [
    { icon: 'ruler', title: 'Custom Desain', desc: 'Desain bebas sesuai keinginan Anda' },
    { icon: 'percent', title: 'Bahan Berkualitas', desc: 'Material premium dan tahan lama' },
    { icon: 'measure', title: 'Presisi Ukuran', desc: 'Akurasi sesuai kebutuhan' },
    { icon: 'zap', title: 'Proses Cepat', desc: 'Pengerjaan efisien dan tepat waktu' },
    { icon: 'headset', title: 'Layanan Responsif', desc: 'Tim siap membantu setiap saat' },
  ]
  return list.map((a) => ({ ...a, iconComp: iconMap[a.icon] || Zap }))
})
</script>

<template>
  <section class="mt-14 bg-slate-900 py-14 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-2xl text-center" v-reveal="'zoom'">
        <span
          class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.25em] ring-1"
          :style="{ backgroundColor: 'color-mix(in srgb, ' + accentColor + ' 16%, transparent)', color: accentColor, '--tw-ring-color': 'color-mix(in srgb, ' + accentColor + ' 35%, transparent)' }"
        >
          {{ eyebrow }}
        </span>
        <h2 class="mt-4 text-2xl font-extrabold tracking-tight text-white sm:text-3xl">{{ title }}</h2>
        <p class="mt-3.5 text-sm leading-relaxed text-slate-300 sm:text-[15px]">{{ subtitle }}</p>
      </div>
      <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-5">
        <div
          v-for="(adv, i) in items"
          :key="adv.title"
          v-reveal="{ type: 'flip', delay: i * 90 }"
          class="group rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur transition-all duration-300 hover:-translate-y-1 hover:border-white/20 hover:bg-white/10 sm:p-6"
        >
          <div
            class="flex h-12 w-12 items-center justify-center rounded-xl text-white shadow-lg transition-transform duration-300 group-hover:scale-110"
            :style="{ backgroundColor: primaryColor }"
          >
            <component :is="adv.iconComp" class="h-5 w-5" :stroke-width="2" />
          </div>
          <h3 class="mt-4 text-sm font-bold text-white">{{ adv.title }}</h3>
          <p class="mt-1.5 text-xs leading-relaxed text-slate-300">{{ adv.desc }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
