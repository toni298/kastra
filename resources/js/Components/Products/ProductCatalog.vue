<script setup>
import { Package, Plus, Search } from 'lucide-vue-next'

const props = defineProps({
  products: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  categoriesHasNext: { type: Boolean, default: false },
  categoriesLoadingMore: { type: Boolean, default: false },
  search: { type: String, default: '' },
  selectedCategory: { type: [String, Number], default: 'all' },
  loading: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
  hasNext: { type: Boolean, default: false },
})
const emit = defineEmits([
  'update:search',
  'select-category',
  'add',
  'load-more',
  'load-more-categories',
])
const money = (value) => `Rp${Number(value || 0).toLocaleString('id-ID')}`
const imageUrl = (path) => (path ? (path.startsWith('/') ? path : `/storage/${path}`) : null)
</script>

<template>
  <section class="min-w-0">
    <label class="relative mb-5 block">
      <Search class="absolute left-4 top-4 h-5 w-5 text-slate-400" />
      <input
        :value="props.search"
        type="search"
        placeholder="Cari produk, jasa, SKU, atau scan barcode..."
        class="w-full rounded-2xl border-slate-200 py-3.5 pl-12 pr-4 text-sm shadow-sm outline-none focus:border-blue-500 focus:ring-blue-500 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
        @input="emit('update:search', $event.target.value)"
      />
    </label>
    <div class="mb-5 flex gap-2 overflow-x-auto pb-1">
      <button
        v-for="category in categories"
        :key="category.id"
        type="button"
        :class="[
          'shrink-0 rounded-full px-5 py-2 text-xs font-semibold transition',
          props.selectedCategory === category.id
            ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20'
            : 'bg-white text-slate-600 hover:bg-slate-100',
        ]"
        @click="emit('select-category', category.id)"
      >
        {{ category.name }}
      </button>
    </div>
    <button
      v-if="categoriesHasNext"
      type="button"
      class="mb-5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-50"
      :disabled="categoriesLoadingMore"
      @click="emit('load-more-categories')"
    >
      {{ categoriesLoadingMore ? 'Memuat kategori...' : 'Muat kategori berikutnya' }}
    </button>
    <div v-if="loading" class="py-4 text-center text-xs font-medium text-slate-500">
      Memuat produk...
    </div>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
      <button
        v-for="product in products"
        :key="product.id"
        type="button"
        class="group overflow-hidden rounded-2xl border border-slate-200 bg-white text-left shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="product.stock < 1"
        @click="emit('add', product)"
      >
        <div class="flex aspect-[1.35] items-center justify-center overflow-hidden bg-slate-100">
          <img
            v-if="imageUrl(product.image)"
            :src="imageUrl(product.image)"
            :alt="product.name"
            class="h-full w-full object-cover transition group-hover:scale-105"
          />
          <Package v-else :size="38" class="text-slate-300" />
        </div>
        <div class="p-3">
          <p class="truncate text-sm font-semibold">{{ product.name }}</p>
          <div class="mt-1 flex items-baseline gap-2">
            <p v-if="product.discount > 0" class="text-xs text-slate-400 line-through">
              {{ money(product.original_price) }}
            </p>
            <p class="text-sm font-bold text-blue-600">{{ money(product.price) }}</p>
          </div>
          <p v-if="product.discount > 0" class="mt-1 text-xs font-semibold text-emerald-600">
            Price Discount {{ product.discount }}%
          </p>
          <div class="mt-3 flex items-center justify-between text-xs">
            <span :class="product.stock > 0 ? 'text-emerald-600' : 'text-red-500'"
              >Stok: {{ product.stock }}</span
            >
            <span
              class="grid h-7 w-7 place-items-center rounded-lg border border-slate-200 text-blue-600"
              ><Plus :size="16"
            /></span>
          </div>
        </div>
      </button>
    </div>
    <button
      v-if="hasNext"
      type="button"
      class="mx-auto mt-5 block rounded-xl border border-blue-200 bg-white px-4 py-2 text-xs font-semibold text-blue-600 hover:bg-blue-50 disabled:opacity-50"
      :disabled="loadingMore"
      @click="emit('load-more')"
    >
      {{ loadingMore ? 'Memuat...' : 'Muat produk berikutnya' }}
    </button>
    <p
      v-if="!products.length && !loading"
      class="rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center text-sm text-slate-500"
    >
      Tidak ada produk yang sesuai.
    </p>
  </section>
</template>
