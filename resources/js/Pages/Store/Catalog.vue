<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import {
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  ListFilter,
  PackageSearch,
  X,
} from 'lucide-vue-next'
import { resolveTheme } from './Themes/index.js'
import { storeRoute } from '@/Utils/store'

const props = defineProps({
  store: { type: Object, required: true },
  products: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const theme = resolveTheme(props.store.theme?.template)
const { StoreLayout, ProductCard } = theme

function pageLabel(label) {
  const decoded = label.replace(/&laquo;|&raquo;|&amp;/g, '').trim()
  return decoded || label
}

const q = ref(props.filters.q || '')
const category = ref(props.filters.category || '')
const minPrice = ref(props.filters.min_price ?? null)
const maxPrice = ref(props.filters.max_price ?? null)
const sort = ref(props.filters.sort || 'newest')
const mobileFiltersOpen = ref(false)

const sortOptions = [
  { value: 'newest', label: 'Terbaru' },
  { value: 'price_asc', label: 'Harga: Rendah ke Tinggi' },
  { value: 'price_desc', label: 'Harga: Tinggi ke Rendah' },
  { value: 'name_asc', label: 'Nama: A ke Z' },
  { value: 'name_desc', label: 'Nama: Z ke A' },
]

function applyFilters() {
  router.get(
    storeRoute(props.store, '/catalog'),
    {
      q: q.value || undefined,
      category: category.value || undefined,
      min_price: minPrice.value ?? undefined,
      max_price: maxPrice.value ?? undefined,
      sort: sort.value !== 'newest' ? sort.value : undefined,
    },
    { preserveState: true, replace: true }
  )
  mobileFiltersOpen.value = false
}

function selectSort(value) {
  sort.value = value
  applyFilters()
}

function resetFilters() {
  q.value = ''
  category.value = ''
  minPrice.value = null
  maxPrice.value = null
  sort.value = 'newest'
  applyFilters()
}
</script>

<template>
  <StoreLayout :store="store">
    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-1.5 text-xs text-slate-400" aria-label="Breadcrumb">
        <Link :href="storeRoute(store)" class="hover:text-blue-700">Beranda</Link>
        <ChevronRight class="h-3.5 w-3.5" />
        <span class="text-slate-600">Belanja</span>
      </nav>

      <!-- Banner katalog -->
      <div class="relative mt-4 overflow-hidden rounded-sm bg-slate-900">
        <img
          src="/gambar-banner.png"
          alt="Banner katalog"
          loading="lazy"
          decoding="async"
          class="absolute inset-0 h-full w-full object-cover opacity-50"
        />
        <div class="relative flex h-28 flex-col justify-center px-6 sm:h-36 sm:px-10">
          <h1 class="text-2xl font-extrabold text-white sm:text-3xl">Katalog Produk</h1>
          <p class="mt-1 text-xs text-slate-300 sm:text-sm">
            {{ products.total }} produk tersedia di {{ store.branch }}
          </p>
        </div>
      </div>

      <div class="mt-8 grid gap-8 lg:grid-cols-[240px_1fr]">
        <!-- Sidebar filter (desktop) -->
        <aside class="hidden lg:block">
          <div class="rounded-sm border border-slate-200">
            <h2 class="border-b border-slate-200 px-4 py-3 text-sm font-bold text-slate-900">
              Filter
            </h2>
            <form class="space-y-5 p-4" @submit.prevent="applyFilters">
              <div>
                <label for="q" class="text-xs font-bold uppercase tracking-wide text-slate-700">
                  Pencarian
                </label>
                <input
                  id="q"
                  v-model="q"
                  type="search"
                  placeholder="Cari produk..."
                  class="mt-2 w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                />
              </div>
              <div>
                <label
                  for="category"
                  class="text-xs font-bold uppercase tracking-wide text-slate-700"
                >
                  Kategori
                </label>
                <select
                  id="category"
                  v-model="category"
                  class="mt-2 w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                >
                  <option value="">Semua kategori</option>
                  <option v-for="item in categories" :key="item.id" :value="item.id">
                    {{ item.name }}
                  </option>
                </select>
              </div>
              <div>
                <span class="text-xs font-bold uppercase tracking-wide text-slate-700">
                  Rentang Harga
                </span>
                <div class="mt-2 grid grid-cols-2 gap-2">
                  <input
                    v-model.number="minPrice"
                    type="number"
                    min="0"
                    placeholder="Min"
                    class="w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                  />
                  <input
                    v-model.number="maxPrice"
                    type="number"
                    min="0"
                    placeholder="Maks"
                    class="w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                  />
                </div>
              </div>
              <button
                type="submit"
                class="w-full rounded-sm bg-blue-700 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-blue-800"
              >
                Terapkan Filter
              </button>
              <button
                type="button"
                class="w-full text-center text-xs font-medium text-slate-500 hover:text-blue-700"
                @click="resetFilters"
              >
                Reset Filter
              </button>
            </form>
          </div>
        </aside>

        <!-- Konten -->
        <div>
          <!-- Toolbar -->
          <div
            class="flex flex-wrap items-center justify-between gap-3 border-y border-slate-200 py-2.5"
          >
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-sm border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 lg:hidden"
              @click="mobileFiltersOpen = true"
            >
              <ListFilter class="h-4 w-4" />
              Filter
            </button>
            <p class="hidden text-xs text-slate-500 sm:block">
              Menampilkan {{ products.data.length }} dari {{ products.total }} produk
            </p>
            <div class="relative ml-auto">
              <select
                :value="sort"
                aria-label="Urutkan"
                class="appearance-none rounded-sm border-slate-300 py-2 pl-3 pr-8 text-xs font-medium text-slate-700 focus:border-blue-600 focus:ring-blue-600"
                @change="selectSort($event.target.value)"
              >
                <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
              <ChevronDown
                class="pointer-events-none absolute right-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
              />
            </div>
          </div>

          <!-- Grid produk -->
          <div
            v-if="products.data.length"
            class="mt-6 grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-3 xl:grid-cols-4"
          >
            <ProductCard
              v-for="product in products.data"
              :key="product.id"
              :store="store"
              :product="product"
            />
          </div>
          <div
            v-else
            class="mt-6 flex flex-col items-center rounded-sm border border-dashed border-slate-300 py-20 text-center"
          >
            <PackageSearch class="h-14 w-14 text-slate-300" />
            <p class="mt-4 text-lg font-semibold text-slate-700">Tidak ada produk ditemukan</p>
            <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau filter Anda.</p>
            <button
              type="button"
              class="mt-6 rounded-sm bg-blue-700 px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white hover:bg-blue-800"
              @click="resetFilters"
            >
              Reset Filter
            </button>
          </div>

          <!-- Pagination -->
          <nav
            v-if="products.links && products.links.length > 3"
            class="mt-10 flex flex-wrap justify-center gap-1.5"
            aria-label="Pagination"
          >
            <template v-for="link in products.links" :key="link.label">
              <Link
                v-if="link.url"
                :href="link.url"
                preserve-state
                class="flex h-9 min-w-9 items-center justify-center rounded-sm border px-3 text-xs font-semibold transition"
                :class="
                  link.active
                    ? 'border-blue-700 bg-blue-700 text-white'
                    : 'border-slate-300 text-slate-600 hover:border-blue-600 hover:text-blue-700'
                "
              >
                <ChevronLeft v-if="link.label.includes('Previous')" class="h-4 w-4" />
                <ChevronRight v-else-if="link.label.includes('Next')" class="h-4 w-4" />
                <span v-else>{{ pageLabel(link.label) }}</span>
              </Link>
              <span
                v-else
                class="flex h-9 min-w-9 items-center justify-center rounded-sm border border-slate-200 px-3 text-xs text-slate-300"
              >
                <ChevronLeft v-if="link.label.includes('Previous')" class="h-4 w-4" />
                <ChevronRight v-else-if="link.label.includes('Next')" class="h-4 w-4" />
                <span v-else>{{ pageLabel(link.label) }}</span>
              </span>
            </template>
          </nav>
        </div>
      </div>
    </section>

    <!-- Drawer filter mobile -->
    <Teleport to="body">
      <div v-if="mobileFiltersOpen" class="fixed inset-0 z-50 lg:hidden">
        <div class="absolute inset-0 bg-slate-900/50" @click="mobileFiltersOpen = false"></div>
        <div
          class="absolute inset-y-0 left-0 w-80 max-w-[85vw] overflow-y-auto bg-white p-5 shadow-xl"
        >
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Filter</h2>
            <button
              type="button"
              aria-label="Tutup filter"
              class="rounded-sm p-1 text-slate-500 hover:bg-slate-100"
              @click="mobileFiltersOpen = false"
            >
              <X class="h-5 w-5" />
            </button>
          </div>
          <form class="mt-5 space-y-5" @submit.prevent="applyFilters">
            <div>
              <label for="m_q" class="text-xs font-bold uppercase tracking-wide text-slate-700">
                Pencarian
              </label>
              <input
                id="m_q"
                v-model="q"
                type="search"
                placeholder="Cari produk..."
                class="mt-2 w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
              />
            </div>
            <div>
              <label
                for="m_category"
                class="text-xs font-bold uppercase tracking-wide text-slate-700"
              >
                Kategori
              </label>
              <select
                id="m_category"
                v-model="category"
                class="mt-2 w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
              >
                <option value="">Semua kategori</option>
                <option v-for="item in categories" :key="item.id" :value="item.id">
                  {{ item.name }}
                </option>
              </select>
            </div>
            <div>
              <span class="text-xs font-bold uppercase tracking-wide text-slate-700">
                Rentang Harga
              </span>
              <div class="mt-2 grid grid-cols-2 gap-2">
                <input
                  v-model.number="minPrice"
                  type="number"
                  min="0"
                  placeholder="Min"
                  class="w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                />
                <input
                  v-model.number="maxPrice"
                  type="number"
                  min="0"
                  placeholder="Maks"
                  class="w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                />
              </div>
            </div>
            <button
              type="submit"
              class="w-full rounded-sm bg-blue-700 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-blue-800"
            >
              Terapkan Filter
            </button>
            <button
              type="button"
              class="w-full text-center text-xs font-medium text-slate-500 hover:text-blue-700"
              @click="resetFilters"
            >
              Reset Filter
            </button>
          </form>
        </div>
      </div>
    </Teleport>
  </StoreLayout>
</template>
