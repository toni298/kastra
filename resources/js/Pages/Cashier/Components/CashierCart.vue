<script setup>
import {
  ArrowRightLeft,
  CreditCard,
  Package,
  Receipt,
  ShoppingCart,
  Trash2,
  WalletCards,
  X,
} from 'lucide-vue-next'

const props = defineProps({
  cart: { type: Array, default: () => [] },
  branches: { type: Array, default: () => [] },
  selectedBranch: { type: [String, Number], default: '' },
  note: { type: String, default: '' },
  discount: { type: Number, default: 0 },
  subtotal: { type: Number, default: 0 },
  tax: { type: Number, default: 0 },
  taxRate: { type: Number, default: 10 },
  taxName: { type: String, default: 'Pajak' },
  taxMode: { type: String, default: 'exclusive' },
  total: { type: Number, default: 0 },
  isSubmitting: { type: Boolean, default: false },
})
const emit = defineEmits([
  'clear',
  'change-quantity',
  'set-quantity',
  'remove',
  'update:selectedBranch',
  'update:note',
  'update:discount',
  'checkout',
])
const money = (value) => `Rp${Number(value || 0).toLocaleString('id-ID')}`
const imageUrl = (path) => (path ? (path.startsWith('/') ? path : `/storage/${path}`) : null)
const setQuantity = (item, event) => {
  const quantity = Math.max(1, Math.min(Number(event.target.value) || 1, item.stock))
  event.target.value = quantity
  emit('set-quantity', item, quantity)
}
</script>

<template>
  <aside
    class="flex min-h-[620px] flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm xl:sticky xl:top-[96px] xl:h-[calc(100vh-120px)]"
  >
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
      <div>
        <h1 class="text-lg font-bold">Keranjang</h1>
        <p class="mt-1 text-xs text-slate-500">{{ cart.length }} jenis produk</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-1 rounded-lg border border-red-100 px-3 py-2 text-xs font-semibold text-red-500 hover:bg-red-50"
        @click="emit('clear')"
      >
        <Trash2 :size="14" />Kosongkan
      </button>
    </div>
    <div class="min-h-0 flex-1 space-y-3 overflow-y-auto py-4">
      <div v-for="item in cart" :key="item.id" class="rounded-xl border border-slate-200 p-3">
        <div class="flex items-start gap-3">
          <div
            class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100"
          >
            <img
              v-if="imageUrl(item.image)"
              :src="imageUrl(item.image)"
              :alt="item.name"
              class="h-full w-full object-cover"
            />
            <Package v-else :size="22" class="text-slate-300" />
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold">{{ item.name }}</p>
            <div class="mt-1 flex items-baseline gap-2">
              <p v-if="item.discount > 0" class="text-xs text-slate-400 line-through">
                {{ money(item.original_price) }}
              </p>
              <p class="text-xs text-slate-500">{{ money(item.price) }}</p>
            </div>
            <p v-if="item.discount > 0" class="mt-1 text-xs font-semibold text-emerald-600">
              Price Discount {{ item.discount }}%
            </p>
            <div class="mt-2 flex items-center justify-between">
              <div class="flex items-center rounded-lg border border-slate-200 text-sm">
                <button
                  type="button"
                  class="px-2 py-1 text-slate-500"
                  @click="emit('change-quantity', item, -1)"
                >
                  −
                </button>
                <input
                  :value="item.quantity"
                  type="number"
                  min="1"
                  :max="item.stock"
                  class="w-16 border-x border-slate-200 px-2 py-1 text-center text-sm outline-none"
                  aria-label="Jumlah produk"
                  @input="setQuantity(item, $event)"
                />
                <button
                  type="button"
                  class="px-2 py-1 text-slate-500"
                  @click="emit('change-quantity', item, 1)"
                >
                  +
                </button>
              </div>
              <span class="text-sm font-bold">{{ money(item.price * item.quantity) }}</span>
            </div>
          </div>
          <button
            type="button"
            aria-label="Hapus produk"
            class="text-slate-400 hover:text-red-500"
            @click="emit('remove', item.id)"
          >
            <X :size="16" />
          </button>
        </div>
      </div>
      <div
        v-if="!cart.length"
        class="flex h-full min-h-52 flex-col items-center justify-center text-center text-slate-400"
      >
        <ShoppingCart :size="34" stroke-width="1.5" />
        <p class="mt-3 text-sm font-semibold">Keranjang masih kosong</p>
        <p class="mt-1 text-xs">Pilih produk untuk mulai transaksi.</p>
      </div>
    </div>
    <div class="border-t border-slate-100 pt-4">
      <div class="mb-3 flex items-center gap-2">
        <select
          :value="selectedBranch"
          class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium outline-none focus:border-blue-500"
          @change="emit('update:selectedBranch', $event.target.value)"
        >
          <option value="" disabled>Pilih cabang</option>
          <option v-for="branch in branches" :key="branch.id" :value="branch.id">
            {{ branch.name }}
          </option>
        </select>
        <button
          type="button"
          class="rounded-lg border border-slate-200 p-2 text-slate-500"
          title="Catatan transaksi"
          @click="emit('update:note', note ? '' : 'Transaksi kasir')"
        >
          <Receipt :size="16" />
        </button>
      </div>
      <textarea
        v-if="note"
        :value="note"
        rows="2"
        class="mb-3 w-full resize-none rounded-lg border border-slate-200 p-2 text-xs outline-none focus:border-blue-500"
        placeholder="Catatan transaksi"
        @input="emit('update:note', $event.target.value)"
      ></textarea>
      <div class="space-y-2 text-xs">
        <div class="flex justify-between">
          <span class="text-slate-500">Subtotal</span
          ><span class="font-medium">{{ money(subtotal) }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Diskon</span
          ><input
            :value="discount"
            type="number"
            min="0"
            class="w-24 rounded border border-slate-200 px-2 py-1 text-right text-xs outline-none"
            @input="emit('update:discount', Number($event.target.value))"
          />
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">{{ taxName }} ({{ taxRate }}%)</span
          ><span class="font-medium">{{ money(tax) }}</span>
        </div>
        <div class="mt-3 flex items-end justify-between border-t border-slate-100 pt-3">
          <span class="text-base font-bold">TOTAL</span
          ><span class="text-2xl font-bold text-blue-600">{{ money(total) }}</span>
        </div>
      </div>
      <div class="mt-4 grid grid-cols-2 gap-2">
        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 py-3 text-sm font-semibold text-blue-600 hover:bg-blue-50"
          @click="emit('update:note', 'Transaksi disimpan sebagai draft')"
        >
          <WalletCards :size="16" />Simpan Draft
        </button>
        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50"
        >
          <ArrowRightLeft :size="16" />Hold
        </button>
      </div>
      <button
        type="button"
        :disabled="!cart.length || isSubmitting"
        class="mt-2 flex w-full items-center justify-center gap-3 rounded-xl bg-blue-600 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
        @click="emit('checkout')"
      >
        <CreditCard :size="18" />BAYAR <span>{{ money(total) }}</span
        ><kbd class="rounded bg-blue-500 px-1.5 py-0.5 text-[10px]">F4</kbd>
      </button>
    </div>
  </aside>
</template>
