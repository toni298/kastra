<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Heart, ImageOff, ShoppingCart, Star } from 'lucide-vue-next'
import { formatRupiah, storeRoute } from '@/Utils/store'
import { useStoreCart } from '@/Composables/useStoreCart'

const props = defineProps({
  store: { type: Object, required: true },
  product: { type: Object, required: true },
})

const cart = useStoreCart(props.store)
const wished = ref(false)
const justAdded = ref(false)

function addToCart() {
  if (props.product.stock <= 0) return
  cart.add(
    {
      id: props.product.id,
      name: props.product.name,
      slug: props.product.slug,
      price: props.product.discount > 0 ? props.product.final_price : props.product.price,
      stock: props.product.stock,
      image: props.product.image,
    },
    1
  )
  justAdded.value = true
  setTimeout(() => (justAdded.value = false), 1500)
}
</script>

<template>
  <div
    class="group flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-slate-200 hover:shadow-xl"
  >
    <!-- Media -->
    <div class="relative overflow-hidden bg-slate-100">
      <Link :href="storeRoute(store, `/product/${product.slug}`)" class="block aspect-square">
        <img
          v-if="product.image"
          :src="product.image"
          :alt="product.name"
          loading="lazy"
          decoding="async"
          class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-110"
        />
        <span v-else class="flex h-full w-full items-center justify-center">
          <ImageOff class="h-10 w-10 text-slate-300" />
        </span>
      </Link>

      <!-- Label -->
      <div class="absolute left-3 top-3 flex flex-col items-start gap-1.5">
        <span
          v-if="product.discount > 0"
          class="rounded-full bg-red-600 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white shadow-md"
        >
          -{{ product.discount }}%
        </span>
        <span
          v-if="product.stock <= 0"
          class="rounded-full bg-slate-700 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white shadow-md"
        >
          Habis
        </span>
      </div>

      <!-- Wishlist -->
      <button
        type="button"
        class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-slate-500 shadow-md backdrop-blur transition-all duration-300 hover:scale-110 hover:text-red-500"
        :aria-label="wished ? 'Hapus dari wishlist' : 'Tambah ke wishlist'"
        @click="wished = !wished"
      >
        <Heart class="h-4 w-4" :class="wished ? 'fill-red-500 text-red-500' : ''" />
      </button>

      <!-- Aksi hover -->
      <div
        class="absolute inset-x-3 bottom-3 translate-y-[120%] transition-transform duration-300 ease-out group-hover:translate-y-0"
      >
        <button
          type="button"
          class="flex h-10 w-full items-center justify-center gap-2 rounded-xl text-[11px] font-bold uppercase tracking-wider text-white shadow-lg backdrop-blur transition-all duration-300 hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
          :style="{ backgroundColor: product.stock > 0 ? 'rgba(15,23,42,0.9)' : 'rgba(100,116,139,0.9)' }"
          :disabled="product.stock <= 0"
          @click="addToCart"
        >
          <ShoppingCart class="h-4 w-4" />
          {{ justAdded ? 'Ditambahkan ✓' : 'Tambah ke Keranjang' }}
        </button>
      </div>
    </div>

    <!-- Detail -->
    <div class="flex flex-1 flex-col px-4 py-4">
      <span class="flex items-center gap-0.5" aria-label="Rating 4 dari 5">
        <Star
          v-for="index in 5"
          :key="index"
          class="h-3.5 w-3.5"
          :class="index <= 4 ? 'fill-amber-400 text-amber-400' : 'fill-slate-200 text-slate-200'"
        />
        <span class="ml-1 text-[11px] font-medium text-slate-400">4.0</span>
      </span>
      <Link
        :href="storeRoute(store, `/product/${product.slug}`)"
        class="mt-2 line-clamp-2 text-[13.5px] font-semibold leading-snug text-slate-800 transition hover:text-slate-950"
      >
        {{ product.name }}
      </Link>
      <p class="mt-2 text-[15px] font-extrabold tracking-tight text-slate-900">
        <template v-if="product.discount > 0">
          <span class="mr-1.5 text-xs font-medium text-slate-400 line-through">
            {{ formatRupiah(product.price) }}
          </span>
          <span class="text-red-600">{{ formatRupiah(product.final_price) }}</span>
        </template>
        <template v-else>{{ formatRupiah(product.price) }}</template>
      </p>
    </div>
  </div>
</template>
