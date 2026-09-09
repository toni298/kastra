<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ChevronRight, Heart, ImageOff, Minus, Plus, ShoppingCart, Star } from 'lucide-vue-next'
import { resolveTheme } from './Themes/index.js'
import { formatRupiah, storeRoute } from '@/Utils/store'
import { useStoreCart } from '@/Composables/useStoreCart'

const props = defineProps({
  store: { type: Object, required: true },
  product: { type: Object, required: true },
  related: { type: Array, default: () => [] },
})

const { StoreLayout, ProductCard } = resolveTheme(props.store.theme?.template)

const cart = useStoreCart(props.store)
const quantity = ref(1)
const activeImage = ref(props.product.images[0] || null)
const added = ref(false)
const wished = ref(false)
const showLightbox = ref(false)

const maxQty = computed(() => Math.max(1, props.product.stock))
const outOfStock = computed(() => props.product.stock <= 0)
const hasDiscount = computed(() => (props.product.discount ?? 0) > 0)
const finalPrice = computed(() => props.product.final_price ?? props.product.price)

function openLightbox() {
  if (!activeImage.value) return
  showLightbox.value = true
}

function closeLightbox() {
  showLightbox.value = false
}

function increment() {
  if (quantity.value < maxQty.value) quantity.value++
}

function decrement() {
  if (quantity.value > 1) quantity.value--
}

function addToCart() {
  if (outOfStock.value) return
  cart.add(
    {
      id: props.product.id,
      name: props.product.name,
      slug: props.product.slug,
      price: finalPrice.value,
      stock: props.product.stock,
      image: props.product.images[0] || null,
    },
    quantity.value
  )
  added.value = true
  setTimeout(() => (added.value = false), 2000)
}
</script>

<template>
  <StoreLayout :store="store">
    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-1.5 text-xs text-slate-400" aria-label="Breadcrumb">
        <Link :href="storeRoute(store)" class="hover:text-blue-700">Beranda</Link>
        <ChevronRight class="h-3.5 w-3.5" />
        <Link :href="storeRoute(store, '/catalog')" class="hover:text-blue-700">Belanja</Link>
        <template v-if="product.category">
          <ChevronRight class="h-3.5 w-3.5" />
          <Link
            :href="`${storeRoute(store, '/catalog')}?category=${product.category.id}`"
            class="hover:text-blue-700"
          >
            {{ product.category.name }}
          </Link>
        </template>
        <ChevronRight class="h-3.5 w-3.5" />
        <span class="text-slate-600">{{ product.name }}</span>
      </nav>

      <div class="mt-6 grid gap-10 lg:grid-cols-[2.25fr_1fr] lg:gap-14">
        <!-- Galeri -->
        <div>
          <button
            type="button"
            class="relative w-full max-h-[630px] overflow-hidden rounded-sm border border-slate-200 bg-slate-50 focus:outline-none"
            @click="openLightbox"
          >
            <img
              v-if="activeImage"
              :src="activeImage"
              :alt="product.name"
              decoding="async"
              class="w-full max-h-[630px] object-contain"
            />
            <ImageOff v-else class="h-16 w-16 text-slate-300" />
            <span
              v-if="activeImage"
              class="pointer-events-none absolute inset-x-0 bottom-2 mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white"
            >
              🔍
            </span>
          </button>

          <div v-if="product.images.length > 1" class="mt-3 flex gap-3 overflow-x-auto">
            <button
              v-for="(image, index) in product.images"
              :key="index"
              type="button"
              class="h-20 w-20 shrink-0 overflow-hidden rounded-sm border transition"
              :class="
                activeImage === image
                  ? 'border-blue-700'
                  : 'border-slate-200 opacity-70 hover:opacity-100'
              "
              @click="activeImage = image"
            >
              <img
                :src="image"
                :alt="`${product.name} ${index + 1}`"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover"
              />
            </button>
          </div>
        </div>

        <!-- Info produk -->
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900">{{ product.name }}</h1>

          <div class="mt-2.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
              <span class="flex items-center gap-0.5">
                <Star
                  v-for="index in 5"
                  :key="index"
                  class="h-3.5 w-3.5"
                  :class="index <= 4 ? 'fill-amber-400 text-amber-400' : 'text-slate-300'"
                />
              </span>
              <span>(3 Ulasan)</span>
            </span>
            <span v-if="product.sku" class="text-slate-300">|</span>
            <span v-if="product.sku">SKU: {{ product.sku }}</span>
            <template v-if="product.category">
              <span class="text-slate-300">|</span>
              <span>
                Kategori:
                <Link
                  :href="`${storeRoute(store, '/catalog')}?category=${product.category.id}`"
                  class="font-medium text-blue-700 hover:underline"
                >
                  {{ product.category.name }}
                </Link>
              </span>
            </template>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-3 border-y border-slate-100 py-4">
            <template v-if="hasDiscount">
              <div>
                <p class="text-sm font-medium text-slate-400 line-through">
                  {{ formatRupiah(product.price) }}
                </p>
                <p class="text-3xl font-extrabold text-blue-700">
                  {{ formatRupiah(finalPrice) }}
                </p>
              </div>
              <span
                class="rounded-sm bg-red-600 px-2 py-1 text-[11px] font-bold uppercase text-white"
              >
                Hemat {{ product.discount }}%
              </span>
            </template>
            <p v-else class="text-3xl font-extrabold text-blue-700">
              {{ formatRupiah(product.price) }}
            </p>
            <span
              class="rounded-sm px-2 py-1 text-[11px] font-bold uppercase"
              :class="outOfStock ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-700'"
            >
              {{ outOfStock ? 'Stok Habis' : `Stok: ${product.stock}` }}
            </span>
            <span v-if="product.unit" class="text-xs text-slate-500">
              per {{ product.unit.name }}
            </span>
          </div>

          <p
            v-if="product.description"
            class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-600"
          >
            {{ product.description }}
          </p>
          <p v-else class="mt-4 text-sm italic text-slate-400">
            Belum ada deskripsi untuk produk ini.
          </p>

          <!-- Quantity + aksi -->
          <div class="mt-6 flex flex-wrap items-center gap-3">
            <div class="flex items-center rounded-sm border border-slate-300">
              <button
                type="button"
                class="flex h-10 w-10 items-center justify-center text-slate-600 disabled:opacity-40"
                :disabled="quantity <= 1"
                aria-label="Kurangi jumlah"
                @click="decrement"
              >
                <Minus class="h-4 w-4" />
              </button>
              <input
                v-model.number="quantity"
                type="number"
                :min="1"
                :max="maxQty"
                class="h-10 w-14 border-0 text-center text-sm font-semibold focus:ring-0"
                @change="quantity = Math.max(1, Math.min(quantity || 1, maxQty))"
              />
              <button
                type="button"
                class="flex h-10 w-10 items-center justify-center text-slate-600 disabled:opacity-40"
                :disabled="quantity >= maxQty"
                aria-label="Tambah jumlah"
                @click="increment"
              >
                <Plus class="h-4 w-4" />
              </button>
            </div>
            <button
              type="button"
              class="inline-flex flex-1 items-center justify-center gap-2 rounded-sm bg-blue-700 px-6 py-2.5 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:bg-slate-300 sm:flex-none"
              :disabled="outOfStock"
              @click="addToCart"
            >
              <ShoppingCart class="h-4 w-4" />
              {{ added ? 'Ditambahkan!' : 'Tambah ke Keranjang' }}
            </button>
            <button
              type="button"
              class="flex h-10 w-10 items-center justify-center rounded-sm border border-slate-300 transition hover:border-red-300"
              :class="wished ? 'text-red-500' : 'text-slate-400 hover:text-red-500'"
              :aria-label="wished ? 'Hapus dari wishlist' : 'Tambah ke wishlist'"
              @click="wished = !wished"
            >
              <Heart class="h-4 w-4" :class="wished ? 'fill-current' : ''" />
            </button>
          </div>
        </div>
      </div>

      <!-- Produk terkait -->
      <div class="mt-14">
        <h2 class="border-b border-slate-200 pb-3 text-lg font-extrabold text-slate-900">
          Produk Terkait
        </h2>
        <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-3 lg:grid-cols-4">
          <template v-if="related.length">
            <ProductCard v-for="item in related" :key="item.id" :store="store" :product="item" />
          </template>
          <p
            v-else
            class="col-span-full rounded-sm border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500"
          >
            Tidak ada produk terkait untuk ditampilkan.
          </p>
        </div>
      </div>

      <transition name="fade">
        <div
          v-if="showLightbox"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4 py-6"
          @click.self="closeLightbox"
        >
          <div class="relative max-w-4xl w-full overflow-hidden rounded-xl bg-slate-950 shadow-2xl">
            <button
              type="button"
              class="absolute right-4 top-4 z-10 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900/90 text-white transition hover:bg-slate-800"
              @click="closeLightbox"
              aria-label="Tutup tampilan besar"
            >
              ×
            </button>
            <img
              v-if="activeImage"
              :src="activeImage"
              :alt="product.name"
              decoding="async"
              class="h-[80vh] w-full object-contain bg-slate-950"
            />
          </div>
        </div>
      </transition>
    </section>
  </StoreLayout>
</template>
