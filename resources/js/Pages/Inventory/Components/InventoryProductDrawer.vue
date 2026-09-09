<script setup>
import { Package, TrendingUp, X } from 'lucide-vue-next'
import { computed } from 'vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
  item: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
  canDiscount: { type: Boolean, default: false },
})
const emit = defineEmits(['close', 'add-discount'])

const formatCurrency = (value) => {
  if (value == null) return '—'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value))
}

const statusVariant = computed(() => {
  const status = props.item?.stock?.status
  if (status === 'Habis') return 'error'
  if (status === 'Perlu Direstok') return 'warning'
  return 'success'
})

const hasDiscount = computed(() => {
  const discount = props.item?.stock?.discount
  return discount != null && discount > 0
})

const discountedPrice = computed(() => {
  const price = Number(props.item?.product?.selling_price ?? 0)
  const discount = Number(props.item?.stock?.discount ?? 0)
  return Math.round(price * (1 - discount / 100))
})
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
    >
      <header
        class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-100 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
      >
        <div class="flex items-center gap-3">
          <span class="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-600">
            <Package :size="20" />
          </span>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
              Detail Produk
            </p>
            <h2 class="mt-0.5 text-lg font-semibold text-slate-950 dark:text-white">
              {{ item?.product?.name ?? '—' }}
            </h2>
          </div>
        </div>
        <button
          class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-[#163354]"
          @click="emit('close')"
        >
          <X :size="20" />
        </button>
      </header>

      <div class="space-y-6 p-5">
        <div
          v-if="loading"
          class="rounded-xl bg-slate-50 p-8 text-center text-sm text-slate-500 dark:bg-[#0a1b33]"
        >
          Memuat detail produk…
        </div>
        <div v-else-if="error" class="rounded-xl bg-red-50 p-4 text-sm text-red-700">
          {{ error }}
        </div>
        <template v-else-if="item">
          <div class="space-y-1">
            <p class="text-sm text-slate-500 dark:text-slate-400">
              <span class="font-medium text-slate-700 dark:text-slate-200">SKU:</span>
              {{ item.product?.sku ?? '—' }}
            </p>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              <span class="font-medium text-slate-700 dark:text-slate-200">Kategori:</span>
              {{ item.product?.category ?? '—' }}
            </p>
            <p v-if="item.product?.brand" class="text-sm text-slate-500 dark:text-slate-400">
              <span class="font-medium text-slate-700 dark:text-slate-200">Brand:</span>
              {{ item.product?.brand }}
            </p>
          </div>

          <section class="grid grid-cols-3 gap-3">
            <div class="rounded-xl border border-slate-200 p-4 text-center dark:border-[#29476b]">
              <p class="text-2xl font-bold text-slate-950 dark:text-white">
                {{ item.stock?.quantity ?? 0 }}
              </p>
              <p class="mt-1 text-xs text-slate-500">Stok</p>
            </div>
            <div class="rounded-xl border border-slate-200 p-4 text-center dark:border-[#29476b]">
              <p class="text-2xl font-bold text-slate-950 dark:text-white">
                {{ item.performance?.total_sold ?? 0 }}
              </p>
              <p class="mt-1 text-xs text-slate-500">Terjual</p>
            </div>
            <div class="rounded-xl border border-slate-200 p-4 text-center dark:border-[#29476b]">
              <template v-if="hasDiscount">
                <p class="truncate text-xs font-medium text-slate-400 line-through">
                  {{ formatCurrency(item.product?.selling_price) }}
                </p>
                <p class="mt-1 truncate text-lg font-bold text-emerald-600 dark:text-emerald-400">
                  {{ formatCurrency(discountedPrice) }}
                </p>
              </template>
              <p v-else class="truncate text-lg font-bold text-slate-950 dark:text-white">
                {{ formatCurrency(item.product?.selling_price) }}
              </p>
              <p class="mt-1 text-xs text-slate-500">Harga</p>
            </div>
          </section>

          <section>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Stok</h3>
            <div
              class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 dark:divide-[#1d3859] dark:border-[#29476b]"
            >
              <div class="flex items-center justify-between p-4">
                <span class="text-sm text-slate-500">Tersedia</span>
                <span class="text-sm font-semibold text-slate-950 dark:text-white">
                  {{ item.stock?.quantity ?? 0 }} {{ item.product?.unit ?? '' }}
                </span>
              </div>
              <div class="flex items-center justify-between p-4">
                <span class="text-sm text-slate-500">Minimum</span>
                <span class="text-sm font-semibold text-slate-950 dark:text-white">
                  {{ item.stock?.minimum ?? 0 }} {{ item.product?.unit ?? '' }}
                </span>
              </div>
              <div class="flex items-center justify-between p-4">
                <span class="text-sm text-slate-500">Status</span>
                <Badge :variant="statusVariant">{{ item.stock?.status }}</Badge>
              </div>
            </div>
          </section>

          <section>
            <h3
              class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-slate-400"
            >
              <TrendingUp :size="16" /> Performa Penjualan
            </h3>
            <div
              class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 dark:divide-[#1d3859] dark:border-[#29476b]"
            >
              <div class="flex items-center justify-between p-4">
                <span class="text-sm text-slate-500">Terjual bulan ini</span>
                <span class="text-sm font-semibold text-slate-950 dark:text-white">
                  {{ item.performance?.sold_this_month ?? 0 }} {{ item.product?.unit ?? '' }}
                </span>
              </div>
              <div class="flex items-center justify-between p-4">
                <span class="text-sm text-slate-500">Terjual bulan lalu</span>
                <span class="text-sm font-semibold text-slate-950 dark:text-white">
                  {{ item.performance?.sold_last_month ?? 0 }} {{ item.product?.unit ?? '' }}
                </span>
              </div>
              <div class="flex items-center justify-between p-4">
                <span class="text-sm text-slate-500">Pendapatan bulan ini</span>
                <span class="text-sm font-semibold text-slate-950 dark:text-white">
                  {{ formatCurrency(item.performance?.revenue_this_month) }}
                </span>
              </div>
            </div>
          </section>

          <hr class="border-slate-100 dark:border-[#1d3859]" />

          <div class="flex justify-end">
            <Button v-if="canDiscount" variant="secondary" @click="emit('add-discount')">
              Tambahkan Diskon
            </Button>
          </div>
        </template>
      </div>
    </aside>
  </Teleport>
</template>
