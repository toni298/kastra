<script setup>
import { Link } from '@inertiajs/vue3'
import { ChevronRight, ImageOff, Minus, Plus, ShoppingCart, Trash2 } from 'lucide-vue-next'
import { resolveTheme } from './Themes/index.js'
import { formatRupiah, storeRoute } from '@/Utils/store'
import { useStoreCart } from '@/Composables/useStoreCart'

const props = defineProps({
  store: { type: Object, required: true },
})

const { StoreLayout } = resolveTheme(props.store.theme?.template)
const cart = useStoreCart(props.store)
</script>

<template>
  <StoreLayout :store="store">
    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-1.5 text-xs text-slate-400" aria-label="Breadcrumb">
        <Link :href="storeRoute(store)" class="hover:text-blue-700">Beranda</Link>
        <ChevronRight class="h-3.5 w-3.5" />
        <span class="text-slate-600">Keranjang</span>
      </nav>

      <!-- Step -->
      <div class="mt-4 flex items-center gap-3 border-b border-slate-200 pb-4 text-sm font-bold">
        <span class="flex items-center gap-2 text-blue-700">
          <span
            class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-700 text-xs text-white"
            >1</span
          >
          Keranjang
        </span>
        <span class="h-px w-8 bg-slate-200"></span>
        <Link :href="storeRoute(store, '/checkout')" class="flex items-center gap-2 text-slate-400">
          <span
            class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-xs text-slate-500"
            >2</span
          >
          Checkout
        </Link>
        <span class="h-px w-8 bg-slate-200"></span>
        <span class="flex items-center gap-2 text-slate-400">
          <span
            class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-xs text-slate-500"
            >3</span
          >
          Selesai
        </span>
      </div>

      <div
        v-if="cart.items.value.length"
        class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_320px]"
      >
        <!-- Tabel keranjang -->
        <div>
          <table class="hidden w-full text-sm sm:table">
            <thead>
              <tr
                class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500"
              >
                <th class="pb-3 font-semibold">Produk</th>
                <th class="pb-3 font-semibold">Harga</th>
                <th class="pb-3 font-semibold">Jumlah</th>
                <th class="pb-3 text-right font-semibold">Subtotal</th>
                <th class="pb-3"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in cart.items.value" :key="item.id" class="border-b border-slate-100">
                <td class="py-4 pr-4">
                  <div class="flex items-center gap-3">
                    <Link
                      :href="storeRoute(store, `/product/${item.slug}`)"
                      class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-slate-200 bg-slate-50"
                    >
                      <img
                        v-if="item.image"
                        :src="item.image"
                        :alt="item.name"
                        loading="lazy"
                        decoding="async"
                        class="h-full w-full object-cover"
                      />
                      <ImageOff v-else class="h-6 w-6 text-slate-300" />
                    </Link>
                    <Link
                      :href="storeRoute(store, `/product/${item.slug}`)"
                      class="font-medium text-slate-800 hover:text-blue-700"
                    >
                      {{ item.name }}
                    </Link>
                  </div>
                </td>
                <td class="py-4 pr-4 text-slate-600">{{ formatRupiah(item.price) }}</td>
                <td class="py-4 pr-4">
                  <div class="inline-flex items-center rounded-sm border border-slate-300">
                    <button
                      type="button"
                      class="flex h-8 w-8 items-center justify-center text-slate-600 disabled:opacity-40"
                      :disabled="item.quantity <= 1"
                      aria-label="Kurangi"
                      @click="cart.updateQuantity(item.id, item.quantity - 1)"
                    >
                      <Minus class="h-3.5 w-3.5" />
                    </button>
                    <span class="w-8 text-center text-sm font-semibold">{{ item.quantity }}</span>
                    <button
                      type="button"
                      class="flex h-8 w-8 items-center justify-center text-slate-600 disabled:opacity-40"
                      :disabled="item.stock !== null && item.quantity >= item.stock"
                      aria-label="Tambah"
                      @click="cart.updateQuantity(item.id, item.quantity + 1)"
                    >
                      <Plus class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </td>
                <td class="py-4 text-right font-semibold text-slate-900">
                  {{ formatRupiah(item.price * item.quantity) }}
                </td>
                <td class="py-4 pl-4 text-right">
                  <button
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-sm text-slate-400 transition hover:bg-red-50 hover:text-red-600"
                    aria-label="Hapus item"
                    @click="cart.remove(item.id)"
                  >
                    <Trash2 class="h-4 w-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Versi mobile -->
          <ul class="space-y-4 sm:hidden">
            <li
              v-for="item in cart.items.value"
              :key="item.id"
              class="flex gap-3 border-b border-slate-100 pb-4"
            >
              <Link
                :href="storeRoute(store, `/product/${item.slug}`)"
                class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-slate-200 bg-slate-50"
              >
                <img
                  v-if="item.image"
                  :src="item.image"
                  :alt="item.name"
                  loading="lazy"
                  decoding="async"
                  class="h-full w-full object-cover"
                />
                <ImageOff v-else class="h-6 w-6 text-slate-300" />
              </Link>
              <div class="min-w-0 flex-1">
                <Link
                  :href="storeRoute(store, `/product/${item.slug}`)"
                  class="block truncate text-sm font-medium text-slate-800"
                >
                  {{ item.name }}
                </Link>
                <p class="mt-0.5 text-xs text-slate-500">{{ formatRupiah(item.price) }}</p>
                <div class="mt-2 flex items-center justify-between">
                  <div class="inline-flex items-center rounded-sm border border-slate-300">
                    <button
                      type="button"
                      class="flex h-7 w-7 items-center justify-center text-slate-600 disabled:opacity-40"
                      :disabled="item.quantity <= 1"
                      aria-label="Kurangi"
                      @click="cart.updateQuantity(item.id, item.quantity - 1)"
                    >
                      <Minus class="h-3 w-3" />
                    </button>
                    <span class="w-7 text-center text-xs font-semibold">{{ item.quantity }}</span>
                    <button
                      type="button"
                      class="flex h-7 w-7 items-center justify-center text-slate-600 disabled:opacity-40"
                      :disabled="item.stock !== null && item.quantity >= item.stock"
                      aria-label="Tambah"
                      @click="cart.updateQuantity(item.id, item.quantity + 1)"
                    >
                      <Plus class="h-3 w-3" />
                    </button>
                  </div>
                  <button
                    type="button"
                    class="text-slate-400 hover:text-red-600"
                    aria-label="Hapus item"
                    @click="cart.remove(item.id)"
                  >
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </li>
          </ul>

          <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <Link
              :href="storeRoute(store, '/catalog')"
              class="rounded-sm border border-slate-300 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-slate-700 transition hover:border-blue-700 hover:text-blue-700"
            >
              Lanjut Belanja
            </Link>
            <button
              type="button"
              class="rounded-sm border border-slate-300 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-slate-700 transition hover:border-red-500 hover:text-red-600"
              @click="cart.clear()"
            >
              Kosongkan Keranjang
            </button>
          </div>
        </div>

        <!-- Total keranjang -->
        <aside class="rounded-sm border border-slate-200">
          <h2
            class="border-b border-slate-200 px-5 py-3.5 text-sm font-bold uppercase tracking-wide text-slate-900"
          >
            Total Keranjang
          </h2>
          <div class="space-y-3 p-5 text-sm">
            <div class="flex justify-between">
              <span class="text-slate-500">Subtotal</span>
              <span class="font-semibold text-slate-900">{{
                formatRupiah(cart.subtotal.value)
              }}</span>
            </div>
            <div class="flex justify-between border-t border-slate-100 pt-3">
              <span class="text-slate-500">Pengiriman</span>
              <span class="text-slate-600">Dihitung saat checkout</span>
            </div>
            <div
              class="flex justify-between border-t border-slate-200 pt-3 text-base font-extrabold"
            >
              <span class="text-slate-900">Total</span>
              <span class="text-blue-700">{{ formatRupiah(cart.subtotal.value) }}</span>
            </div>
            <Link
              :href="storeRoute(store, '/checkout')"
              class="mt-2 block rounded-sm bg-blue-700 py-3 text-center text-xs font-bold uppercase tracking-wider text-white transition hover:bg-blue-800"
            >
              Lanjut ke Checkout
            </Link>
          </div>
        </aside>
      </div>

      <!-- Keranjang kosong -->
      <div
        v-else
        class="mt-8 flex flex-col items-center rounded-sm border border-dashed border-slate-300 py-20 text-center"
      >
        <ShoppingCart class="h-14 w-14 text-slate-300" />
        <p class="mt-4 text-lg font-semibold text-slate-700">Keranjang Anda kosong</p>
        <p class="mt-1 text-sm text-slate-500">
          Yuk, mulai belanja dan temukan produk favorit Anda.
        </p>
        <Link
          :href="storeRoute(store, '/catalog')"
          class="mt-6 rounded-sm bg-blue-700 px-6 py-3 text-xs font-bold uppercase tracking-wider text-white hover:bg-blue-800"
        >
          Jelajahi Katalog
        </Link>
      </div>
    </section>
  </StoreLayout>
</template>
