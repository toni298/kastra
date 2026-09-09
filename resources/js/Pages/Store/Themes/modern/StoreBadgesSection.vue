<script setup>
import { computed } from 'vue'
import { CreditCard, Headset, RotateCcw, ShieldCheck, Truck, Zap } from 'lucide-vue-next'
import vReveal from '@/Directives/vReveal'

const props = defineProps({
  store: { type: Object, required: true },
  config: { type: Object, default: () => ({}) },
})

const iconMap = {
  truck: Truck,
  shield: ShieldCheck,
  card: CreditCard,
  refresh: RotateCcw,
  headset: Headset,
  zap: Zap,
}

const primaryColor = computed(() => props.store.theme?.primary_color || '#1d4ed8')

const items = computed(() => {
  const list = props.config.items?.length ? props.config.items : [
    { icon: 'truck', title: 'Gratis Ongkir & Retur', desc: 'Untuk pengambilan di toko' },
    { icon: 'card', title: 'Pembayaran Aman', desc: 'Konfirmasi via admin' },
    { icon: 'refresh', title: 'Garansi Produk', desc: 'Retur mudah hingga 30 hari' },
    { icon: 'headset', title: 'Dukungan Pelanggan', desc: 'Hubungi kami 24/7' },
  ]
  return list.map((s) => ({ ...s, iconComp: iconMap[s.icon] || Truck }))
})
</script>

<template>
  <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="mt-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
      <div
        v-for="(service, i) in items"
        :key="service.title"
        v-reveal="{ type: 'fade-up', delay: i * 90 }"
        class="group flex items-center gap-3.5 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-transparent hover:shadow-lg sm:p-5"
      >
        <div
          class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110"
          :style="{ backgroundColor: 'color-mix(in srgb, ' + primaryColor + ' 12%, white)', color: primaryColor }"
        >
          <component :is="service.iconComp" class="h-5 w-5" :stroke-width="1.8" />
        </div>
        <div class="min-w-0">
          <p class="truncate text-[13px] font-bold text-slate-900">{{ service.title }}</p>
          <p class="mt-0.5 text-xs leading-snug text-slate-500">{{ service.desc }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
