<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import ProductCard from './ProductCard.vue'
import { storeRoute } from '@/Utils/store'
import vReveal from '@/Directives/vReveal'

const props = defineProps({
  store: { type: Object, required: true },
  products: { type: Array, default: () => [] },
  config: { type: Object, default: () => ({}) },
})

const title = computed(() => props.config.title || 'Produk Terbaru')
const linkLabel = computed(() => props.config.link_label || 'Produk Lainnya')
const limit = computed(() => props.config.limit || 10)
const shown = computed(() => props.products.slice(0, limit.value))
</script>

<template>
  <section v-if="shown.length" class="mb-5 mt-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-end justify-between" v-reveal="'fade-down'">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-slate-400">Koleksi</p>
          <h2 class="mt-1.5 text-2xl font-extrabold tracking-tight text-slate-900">{{ title }}</h2>
        </div>
        <Link
          :href="storeRoute(store, '/catalog')"
          class="group flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-4 py-2 text-[12px] font-bold uppercase tracking-wider text-slate-700 shadow-sm transition-all duration-300 hover:gap-2.5 hover:border-slate-300 hover:text-slate-900 hover:shadow"
        >
          {{ linkLabel }}
          <span class="text-sm transition-transform duration-300 group-hover:translate-x-0.5">→</span>
        </Link>
      </div>
      <div class="mt-7 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 md:grid-cols-4 lg:grid-cols-5">
        <div
          v-for="(product, i) in shown"
          :key="product.id"
          v-reveal="{ type: 'zoom', delay: i * 70 }"
        >
          <ProductCard :product="product" :store="store" />
        </div>
      </div>
    </div>
  </section>
</template>
