<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { Package } from 'lucide-vue-next'
import { useRoute } from '../../../../../vendor/tightenco/ziggy/src/js'

defineProps({
  products: { type: Array, required: true },
})

const page = usePage()
const route = useRoute(page.props.ziggy)
</script>

<template>
  <div
    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"
  >
    <div class="mb-5 flex items-center justify-between gap-4">
      <h2 class="text-lg font-semibold leading-6 tracking-tight text-slate-900 dark:text-white">
        Produk Stok Menipis
      </h2>
      <Link
        :href="route('inventory.stock')"
        class="text-sm font-semibold leading-5 text-emerald-600 hover:underline dark:text-emerald-400"
        >Lihat semua</Link
      >
    </div>

    <div v-if="products.length" class="space-y-4">
      <div
        v-for="(produk, index) in products"
        :key="produk.id ?? index"
        class="border-b border-slate-200 pb-3 last:border-b-0 last:pb-0 dark:border-slate-700"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
              <Package class="h-4 w-4 text-orange-600 dark:text-orange-400" />
            </div>
            <span class="text-sm font-semibold leading-5 text-slate-900 dark:text-white">{{
              produk.nama
            }}</span>
          </div>
          <span
            class="text-sm font-bold leading-5 tabular-nums text-orange-600 dark:text-orange-400"
          >
            Stok: {{ produk.stok }} / Minimum: {{ produk.minimum }}
          </span>
        </div>
      </div>
    </div>
    <p v-else class="py-6 text-center text-sm text-slate-400">Tidak ada produk menipis.</p>
  </div>
</template>
