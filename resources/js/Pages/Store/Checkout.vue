<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ChevronRight, ImageOff, ShoppingCart } from 'lucide-vue-next'
import { resolveTheme } from './Themes/index.js'
import { formatRupiah, storeRoute } from '@/Utils/store'
import { useStoreCart } from '@/Composables/useStoreCart'

const props = defineProps({
  store: { type: Object, required: true },
})

const { StoreLayout } = resolveTheme(props.store.theme?.template)
const cart = useStoreCart(props.store)

const shipping = computed(() => props.store.shipping ?? {})
const payment = computed(() => props.store.payment ?? {})

const form = useForm({
  name: '',
  phone: '',
  address: '',
  delivery_method: 'pickup',
  shipping_cost: 0,
  payment_method: 'cod',
  note: '',
  items: [],
})

// Ongkir dihitung dari settings: flat, gratis jika memenuhi minimum (server juga validasi).
const computedShippingCost = computed(() => {
  if (form.delivery_method !== 'delivery') return 0
  const flat = shipping.value.flat_cost ?? 0
  const freeMin = shipping.value.free_min
  if (freeMin !== null && freeMin !== undefined && cart.subtotal.value >= freeMin) return 0
  return Math.max(0, Number(flat) || 0)
})

const shippingCost = computed(() => computedShippingCost.value)
const total = computed(() => cart.subtotal.value + shippingCost.value)

const itemErrors = computed(() => {
  const errors = form.errors.items
  if (!errors) return []
  return Array.isArray(errors) ? errors : [errors]
})

function submit() {
  form.shipping_cost = shippingCost.value
  form.items = cart.items.value.map((item) => ({ product_id: item.id, quantity: item.quantity }))
  form.post(storeRoute(props.store, '/checkout'), {
    onSuccess: () => cart.clear(),
  })
}

// Metode pengiriman dinamis dari settings.
const shippingMethods = computed(() => {
  const methods = []
  if (shipping.value.delivery_enabled ?? true) {
    const flat = shipping.value.flat_cost
    methods.push({
      value: 'delivery',
      cost: computedShippingCost.value,
      label: 'Diantar (Delivery)',
      description:
        freeShippingActive.value
          ? 'Gratis Ongkir'
          : flat !== null && flat !== undefined
            ? formatRupiah(flat)
            : 'Ongkir dikonfirmasi admin',
    })
  }
  if (shipping.value.pickup_enabled ?? true) {
    methods.push({ value: 'pickup', cost: 0, label: 'Ambil di Toko (Pickup)', description: 'Gratis' })
  }
  return methods
})

const freeShippingActive = computed(() => {
  const freeMin = shipping.value.free_min
  return freeMin !== null && freeMin !== undefined && cart.subtotal.value >= freeMin
})

// Metode pembayaran dinamis dari settings.
const paymentMethods = computed(() => {
  const methods = []
  if (payment.value.cod ?? true) methods.push({ value: 'cod', label: 'COD (Bayar di Tempat)', desc: 'Bayar saat pesanan diterima' })
  if (payment.value.transfer ?? false) methods.push({ value: 'transfer', label: 'Transfer Bank', desc: 'Transfer ke rekening toko' })
  if (payment.value.qris ?? false) methods.push({ value: 'qris', label: 'QRIS', desc: 'Scan kode QRIS untuk membayar' })
  return methods
})

// Default delivery_method & payment_method sesuai yang aktif.
if (shippingMethods.value.length && !shippingMethods.value.find((m) => m.value === form.delivery_method)) {
  form.delivery_method = shippingMethods.value[0].value
}
if (paymentMethods.value.length && !paymentMethods.value.find((m) => m.value === form.payment_method)) {
  form.payment_method = paymentMethods.value[0].value
}
</script>

<template>
  <StoreLayout :store="store">
    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-1.5 text-xs text-slate-400" aria-label="Breadcrumb">
        <Link :href="storeRoute(store)" class="hover:text-blue-700">Beranda</Link>
        <ChevronRight class="h-3.5 w-3.5" />
        <Link :href="storeRoute(store, '/cart')" class="hover:text-blue-700">Keranjang</Link>
        <ChevronRight class="h-3.5 w-3.5" />
        <span class="text-slate-600">Checkout</span>
      </nav>

      <!-- Step -->
      <div class="mt-4 flex items-center gap-3 border-b border-slate-200 pb-4 text-sm font-bold">
        <Link :href="storeRoute(store, '/cart')" class="flex items-center gap-2 text-slate-400">
          <span
            class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-xs text-slate-500"
            >1</span
          >
          Keranjang
        </Link>
        <span class="h-px w-8 bg-slate-200"></span>
        <span class="flex items-center gap-2 text-blue-700">
          <span
            class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-700 text-xs text-white"
            >2</span
          >
          Checkout
        </span>
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
        class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_360px]"
      >
        <!-- Form tagihan -->
        <form @submit.prevent="submit">
          <div v-if="itemErrors.length" class="mb-6 rounded-sm border border-red-200 bg-red-50 p-4">
            <p class="text-sm font-semibold text-red-700">Periksa kembali pesanan Anda:</p>
            <ul class="mt-1 list-disc pl-5 text-sm text-red-600">
              <li v-for="(error, index) in itemErrors" :key="index">{{ error }}</li>
            </ul>
          </div>

          <h2
            class="border-b border-slate-200 pb-3 text-base font-extrabold uppercase tracking-wide text-slate-900"
          >
            Detail Tagihan
          </h2>

          <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
              <label for="name" class="mb-1.5 block text-xs font-semibold text-slate-700">
                Nama Lengkap <span class="text-red-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                maxlength="120"
                class="w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                placeholder="Toni Alfian"
              />
              <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">
                {{ form.errors.name }}
              </p>
            </div>
            <div>
              <label for="phone" class="mb-1.5 block text-xs font-semibold text-slate-700">
                No. WhatsApp <span class="text-red-500">*</span>
              </label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                required
                maxlength="32"
                class="w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
                placeholder="0812-3456-7890"
              />
              <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">
                {{ form.errors.phone }}
              </p>
            </div>
          </div>

          <div class="mt-5">
            <label for="address" class="mb-1.5 block text-xs font-semibold text-slate-700">
              Alamat Lengkap
            </label>
            <textarea
              id="address"
              v-model="form.address"
              rows="3"
              maxlength="1000"
              class="w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
              placeholder="Jl. Contoh No. 123, Kudus"
            ></textarea>
            <p v-if="form.errors.address" class="mt-1 text-xs text-red-600">
              {{ form.errors.address }}
            </p>
          </div>

          <!-- Metode pengiriman -->
          <div class="mt-6">
            <h3 class="text-xs font-semibold text-slate-700">Metode Pengiriman</h3>
            <div class="mt-2.5 space-y-2">
              <label
                v-for="method in shippingMethods"
                :key="method.value"
                class="flex cursor-pointer items-center gap-3 rounded-sm border p-3.5 transition"
                :class="
                  form.delivery_method === method.value
                    ? 'border-blue-600 bg-blue-50/50'
                    : 'border-slate-200 hover:border-slate-300'
                "
              >
                <input
                  v-model="form.delivery_method"
                  type="radio"
                  :value="method.value"
                  class="text-blue-700 focus:ring-blue-600"
                />
                <span class="flex-1">
                  <span class="block text-sm font-medium text-slate-900">{{ method.label }}</span>
                  <span class="block text-xs text-slate-500">{{ method.description }}</span>
                </span>
              </label>
            </div>
            <div v-if="form.delivery_method === 'delivery'" class="mt-3 rounded-sm border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
              <span v-if="freeShippingActive" class="font-semibold text-emerald-600">
                Selamat! Anda mendapatkan Gratis Ongkir.
              </span>
              <span v-else-if="computedShippingCost > 0">
                Ongkir: <span class="font-semibold text-slate-900">{{ formatRupiah(computedShippingCost) }}</span>
              </span>
              <span v-else>Ongkir akan dikonfirmasi oleh admin.</span>
            </div>
          </div>

          <!-- Metode pembayaran -->
          <div class="mt-6">
            <h3 class="text-xs font-semibold text-slate-700">Metode Pembayaran</h3>
            <div class="mt-2.5 space-y-2">
              <label
                v-for="pm in paymentMethods"
                :key="pm.value"
                class="flex cursor-pointer items-center gap-3 rounded-sm border p-3.5 transition"
                :class="
                  form.payment_method === pm.value
                    ? 'border-blue-600 bg-blue-50/50'
                    : 'border-slate-200 hover:border-slate-300'
                "
              >
                <input
                  v-model="form.payment_method"
                  type="radio"
                  :value="pm.value"
                  class="text-blue-700 focus:ring-blue-600"
                />
                <span class="flex-1">
                  <span class="block text-sm font-medium text-slate-900">{{ pm.label }}</span>
                  <span class="block text-xs text-slate-500">{{ pm.desc }}</span>
                </span>
              </label>
            </div>

            <!-- Detail transfer -->
            <div v-if="form.payment_method === 'transfer' && payment.bank_accounts?.length" class="mt-3 space-y-2 rounded-sm border border-slate-200 bg-slate-50 p-3">
              <p class="text-xs font-semibold text-slate-700">Rekening Tujuan:</p>
              <div v-for="(acc, i) in payment.bank_accounts" :key="i" class="flex items-center justify-between text-xs">
                <span class="font-medium text-slate-900">{{ acc.bank_name }} — {{ acc.account_number }}</span>
                <span class="text-slate-500">{{ acc.account_name }}</span>
              </div>
            </div>

            <!-- QRIS -->
            <div v-if="form.payment_method === 'qris' && payment.qris_image_url" class="mt-3 rounded-sm border border-slate-200 bg-slate-50 p-3 text-center">
              <p class="mb-2 text-xs font-semibold text-slate-700">Scan QRIS berikut:</p>
              <img :src="payment.qris_image_url" alt="QRIS" class="mx-auto h-44 w-44 rounded object-contain" />
            </div>

            <p v-if="payment.notes" class="mt-3 rounded-sm border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
              {{ payment.notes }}
            </p>
          </div>

          <div class="mt-6">
            <label for="note" class="mb-1.5 block text-xs font-semibold text-slate-700">
              Catatan Pesanan (opsional)
            </label>
            <textarea
              id="note"
              v-model="form.note"
              rows="3"
              maxlength="500"
              class="w-full rounded-sm border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600"
              placeholder="Tulis catatan untuk pesanan Anda..."
            ></textarea>
            <p class="mt-1 text-right text-[11px] text-slate-400">{{ form.note.length }}/500</p>
            <p v-if="form.errors.note" class="mt-1 text-xs text-red-600">{{ form.errors.note }}</p>
          </div>
        </form>

        <!-- Pesanan Anda -->
        <aside class="rounded-sm border border-slate-200">
          <h2
            class="border-b border-slate-200 px-5 py-3.5 text-sm font-bold uppercase tracking-wide text-slate-900"
          >
            Pesanan Anda
          </h2>
          <div class="p-5">
            <div
              class="flex justify-between border-b border-slate-100 pb-2 text-xs font-bold uppercase tracking-wide text-slate-500"
            >
              <span>Produk</span>
              <span>Subtotal</span>
            </div>
            <ul class="divide-y divide-slate-100">
              <li
                v-for="item in cart.items.value"
                :key="item.id"
                class="flex items-center gap-3 py-3"
              >
                <span
                  class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-slate-200 bg-slate-50"
                >
                  <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="item.name"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover"
                  />
                  <ImageOff v-else class="h-5 w-5 text-slate-300" />
                </span>
                <span class="min-w-0 flex-1">
                  <span class="block truncate text-xs font-medium text-slate-800">
                    {{ item.name }}
                  </span>
                  <span class="text-[11px] text-slate-500">
                    {{ item.quantity }} x {{ formatRupiah(item.price) }}
                  </span>
                </span>
                <span class="text-xs font-semibold text-slate-900">
                  {{ formatRupiah(item.price * item.quantity) }}
                </span>
              </li>
            </ul>
            <dl class="space-y-2.5 border-t border-slate-200 pt-4 text-sm">
              <div class="flex justify-between">
                <dt class="text-slate-500">Subtotal</dt>
                <dd class="font-semibold text-slate-900">
                  {{ formatRupiah(cart.subtotal.value) }}
                </dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-slate-500">Pengiriman</dt>
                <dd class="font-medium text-slate-900">
                  {{ form.delivery_method === 'pickup' ? 'Gratis' : formatRupiah(shippingCost) }}
                </dd>
              </div>
              <div
                class="flex justify-between border-t border-slate-200 pt-3 text-base font-extrabold"
              >
                <dt class="text-slate-900">Total</dt>
                <dd class="text-blue-700">{{ formatRupiah(total) }}</dd>
              </div>
            </dl>

            <div class="mt-5 rounded-sm bg-slate-50 p-4 text-xs leading-relaxed text-slate-500">
              <p class="font-semibold text-slate-700">Konfirmasi via WhatsApp</p>
              <p class="mt-1">
                Pesanan Anda akan dikonfirmasi admin melalui WhatsApp untuk pembayaran dan
                pengiriman.
              </p>
            </div>

            <button
              type="submit"
              class="mt-5 w-full rounded-sm bg-blue-700 py-3 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="form.processing"
              @click="submit"
            >
              {{ form.processing ? 'Memproses...' : 'Buat Pesanan' }}
            </button>
          </div>
        </aside>
      </div>

      <!-- Keranjang kosong -->
      <div
        v-else
        class="mt-8 flex flex-col items-center rounded-sm border border-dashed border-slate-300 py-20 text-center"
      >
        <ShoppingCart class="h-14 w-14 text-slate-300" />
        <p class="mt-4 text-lg font-semibold text-slate-700">Tidak ada produk untuk di-checkout</p>
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
