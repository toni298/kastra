<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Heart, MapPin, Phone, Search, ShoppingCart, Store as StoreIcon } from 'lucide-vue-next'
import { storeRoute } from '@/Utils/store'
import { useStoreCart } from '@/Composables/useStoreCart'

const props = defineProps({
  store: { type: Object, required: true },
})

const cart = useStoreCart(props.store)
const page = usePage()

const theme = computed(() => props.store.theme ?? {})
const primaryColor = computed(() => theme.value.primary_color || '#1d4ed8')
const secondaryColor = computed(() => theme.value.secondary_color || '#0f172a')
const tagline = computed(() => theme.value.tagline || '')
const logoUrl = computed(() => theme.value.logo_url || props.store.logo || null)

const isActive = computed(() => (suffix) => {
  const url = page.url.split('?')[0]
  const base = storeRoute(props.store)
  if (!suffix) return url === base
  return url.startsWith(`${base}${suffix}`)
})

const year = new Date().getFullYear()
</script>

<template>
  <div
    class="store-zoom flex min-h-screen flex-col bg-white"
    :style="{ '--store-primary': primaryColor, '--store-secondary': secondaryColor }"
  >
    <!-- Topbar -->
    <div class="border-b border-slate-100 bg-slate-50">
      <div
        class="mx-auto flex max-w-7xl items-center justify-between px-4 py-1.5 text-[11px] text-slate-500 sm:px-6 lg:px-8"
      >
        <p>
          Selamat datang di {{ store.company }} — {{ store.branch }}
          <template v-if="tagline"> · {{ tagline }}</template>
        </p>
        <div class="hidden items-center gap-4 sm:flex">
          <span v-if="store.phone" class="flex items-center gap-1">
            <Phone class="h-3 w-3" />
            {{ store.phone }}
          </span>
          <span v-if="store.city" class="flex items-center gap-1">
            <MapPin class="h-3 w-3" />
            {{ store.city }}
          </span>
        </div>
      </div>
    </div>

    <!-- Header utama -->
    <header
      class="sticky top-0 z-40 border-b border-slate-100 bg-white/90 shadow-sm backdrop-blur-md"
    >
      <div class="mx-auto flex max-w-7xl items-center gap-6 px-4 py-3.5 sm:px-6 lg:px-8">
        <!-- Logo -->
        <Link :href="storeRoute(store)" class="flex shrink-0 items-center gap-2.5">
          <img
            v-if="logoUrl"
            :src="logoUrl"
            :alt="store.company"
            class="h-9 w-auto object-contain"
          />
          <span
            v-else
            class="flex h-10 w-10 items-center justify-center rounded-xl text-white shadow-md"
            :style="{ backgroundColor: primaryColor }"
          >
            <StoreIcon class="h-5 w-5" />
          </span>
          <span class="hidden flex-col leading-tight sm:flex">
            <span class="text-lg font-extrabold tracking-tight text-slate-900">
              {{ store.company }}
            </span>
            <span
              class="text-[10px] font-bold uppercase tracking-[0.2em]"
              :style="{ color: primaryColor }"
            >
              {{ store.branch }}
            </span>
          </span>
        </Link>

        <!-- Search -->
        <form
          :action="storeRoute(store, '/catalog')"
          method="get"
          class="hidden flex-1 items-center md:flex"
        >
          <div
            class="group flex w-full max-w-xl items-center overflow-hidden rounded-full border border-slate-200 bg-slate-50 transition-all duration-300 focus-within:border-slate-300 focus-within:bg-white focus-within:shadow-md"
          >
            <input
              type="search"
              name="q"
              placeholder="Cari produk di toko kami..."
              class="h-11 w-full border-0 bg-transparent px-5 text-sm focus:ring-0"
            />
            <button
              type="submit"
              class="m-1 flex h-9 items-center gap-1.5 rounded-full px-5 text-sm font-bold text-white transition hover:brightness-110"
              :style="{ backgroundColor: primaryColor }"
            >
              <Search class="h-4 w-4" />
              Cari
            </button>
          </div>
        </form>

        <!-- Aksi -->
        <div class="ml-auto flex items-center gap-5 md:ml-0">
          <Link
            :href="storeRoute(store, '/catalog')"
            class="hidden flex-col items-center text-slate-500 transition hover:text-slate-900 sm:flex"
          >
            <Heart class="h-5 w-5" />
            <span class="mt-0.5 text-[10px] font-semibold">Katalog</span>
          </Link>
          <Link
            :href="storeRoute(store, '/cart')"
            class="relative flex flex-col items-center text-slate-500 transition hover:text-slate-900"
          >
            <ShoppingCart class="h-5 w-5" />
            <span class="mt-0.5 text-[10px] font-semibold">Keranjang</span>
            <span
              v-if="cart.count.value"
              class="absolute -right-2.5 -top-1.5 flex h-4.5 min-w-[18px] items-center justify-center rounded-full px-1 text-[10px] font-bold text-white shadow"
              :style="{ backgroundColor: primaryColor }"
            >
              {{ cart.count.value }}
            </span>
          </Link>
        </div>
      </div>

      <!-- Nav kategori -->
      <nav class="border-t border-slate-100">
        <div class="mx-auto flex max-w-7xl items-center gap-5 px-4 sm:px-6 lg:px-8">
          <Link
            :href="storeRoute(store)"
            class="border-b-2 px-1 py-3 text-[13px] font-semibold transition-colors duration-200"
            :class="
              isActive(null)
                ? 'text-slate-900'
                : 'border-transparent text-slate-500 hover:text-slate-900'
            "
            :style="isActive(null) ? { color: primaryColor, borderBottomColor: primaryColor } : {}"
          >
            Beranda
          </Link>
          <Link
            :href="storeRoute(store, '/catalog')"
            class="border-b-2 px-1 py-3 text-[13px] font-semibold transition-colors duration-200"
            :class="
              isActive('/catalog')
                ? 'text-slate-900'
                : 'border-transparent text-slate-500 hover:text-slate-900'
            "
            :style="
              isActive('/catalog') ? { color: primaryColor, borderBottomColor: primaryColor } : {}
            "
          >
            Belanja
          </Link>
          <Link
            :href="storeRoute(store, '/cart')"
            class="border-b-2 px-1 py-3 text-[13px] font-semibold transition-colors duration-200"
            :class="
              isActive('/cart')
                ? 'text-slate-900'
                : 'border-transparent text-slate-500 hover:text-slate-900'
            "
            :style="
              isActive('/cart') ? { color: primaryColor, borderBottomColor: primaryColor } : {}
            "
          >
            Keranjang
          </Link>
        </div>
      </nav>
    </header>

    <!-- Konten -->
    <main class="flex-1">
      <slot></slot>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300">
      <div
        class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8"
      >
        <div>
          <div class="flex items-center gap-2.5">
            <img
              v-if="store.logo"
              :src="store.logo"
              :alt="store.company"
              class="h-9 w-auto object-contain"
            />
            <span v-else class="text-xl font-extrabold tracking-tight text-white">{{
              store.company
            }}</span>
          </div>
          <p class="mt-4 text-sm leading-relaxed text-slate-400">
            {{ store.branch }}<template v-if="store.city">, {{ store.city }}</template>
          </p>
          <p v-if="store.address" class="mt-1.5 text-sm leading-relaxed text-slate-400">
            {{ store.address }}
          </p>
          <p v-if="store.phone" class="mt-4 flex items-center gap-2 text-sm">
            <Phone class="h-4 w-4" :style="{ color: primaryColor }" />
            {{ store.phone }}
          </p>
        </div>
        <div>
          <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-white">Perusahaan</h3>
          <ul class="mt-4 space-y-2.5 text-sm text-slate-400">
            <li>
              <Link :href="storeRoute(store)" class="transition hover:text-white">Beranda</Link>
            </li>
            <li>
              <Link :href="storeRoute(store, '/catalog')" class="transition hover:text-white">
                Katalog Produk
              </Link>
            </li>
            <li>
              <Link :href="storeRoute(store, '/cart')" class="transition hover:text-white">
                Keranjang Belanja
              </Link>
            </li>
          </ul>
        </div>
        <div>
          <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-white">Akun Saya</h3>
          <ul class="mt-4 space-y-2.5 text-sm text-slate-400">
            <li>
              <Link :href="storeRoute(store, '/cart')" class="transition hover:text-white">
                Lihat Keranjang
              </Link>
            </li>
            <li>
              <Link :href="storeRoute(store, '/checkout')" class="transition hover:text-white">
                Checkout
              </Link>
            </li>
          </ul>
        </div>
        <div>
          <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-white">
            Layanan Pelanggan
          </h3>
          <ul class="mt-4 space-y-2.5 text-sm text-slate-400">
            <li>Pembayaran via transfer / WhatsApp</li>
            <li>Pengiriman &amp; pengambilan di toko</li>
            <li v-if="store.address">{{ store.address }}</li>
          </ul>
        </div>
      </div>
      <div class="border-t border-white/10">
        <div
          class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-slate-500 sm:flex-row sm:px-6 lg:px-8"
        >
          <p>Copyright &copy; {{ year }} {{ store.company }}. Seluruh hak cipta dilindungi.</p>
          <p>Pembayaran aman via konfirmasi admin</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Perbesar seluruh konten storefront setara zoom 110%. */
.store-zoom {
  zoom: 1.1;
}

@supports not (zoom: 1) {
  .store-zoom {
    transform: scale(1.1);
    transform-origin: top left;
    width: calc(100% / 1.1);
  }
}
</style>
