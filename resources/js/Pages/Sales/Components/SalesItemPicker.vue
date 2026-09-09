<script setup>
import axios from 'axios'
import { computed, ref, watch } from 'vue'
import { Package, Plus, Search, Wrench } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
const props = defineProps({ branchId: { type: String, default: '' } })
const emit = defineEmits(['add'])
const search = ref('')
const items = ref([])
const loading = ref(false)
const error = ref('')
const loadItems = async () => {
  if (!props.branchId) {
    items.value = []
    return
  }
  loading.value = true
  error.value = ''
  try {
    items.value = (
      await axios.get(route('search.branch-products'), {
        params: { branch_id: props.branchId, search: search.value || undefined },
      })
    ).data.results
  } catch {
    items.value = []
    error.value = 'Produk cabang gagal dimuat.'
  } finally {
    loading.value = false
  }
}
const toQuantity = (value) => {
  if (typeof value === 'number') return value
  const normalized = String(value ?? '').replace(/[^0-9-]/g, '')
  return Number(normalized) || 0
}
const normalize = (item) => ({
  ...item,
  name: item.name,
  type: 'Produk',
  available_quantity: toQuantity(item.available_quantity),
  stock: `${toQuantity(item.available_quantity)} ${item.unit ?? ''}`,
  price: Number(item.price ?? 0),
})
const results = computed(() =>
  items.value
    .map(normalize)
    .filter((item) =>
      [item.name, item.sku, item.barcode]
        .join(' ')
        .toLowerCase()
        .includes(search.value.toLowerCase())
    )
)
watch(() => props.branchId, loadItems, { immediate: true })
watch(search, loadItems)
const money = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value)
</script>
<template>
  <section>
    <label class="relative block"
      ><Search class="absolute left-4 top-4 h-5 w-5 text-slate-400" /><input
        v-model="search"
        class="w-full rounded-2xl border-slate-200 py-3.5 pl-12 pr-4 text-sm shadow-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
        placeholder="Cari produk, jasa, SKU, atau scan barcode..."
    /></label>
    <div class="mt-4 grid gap-3 sm:grid-cols-2">
      <article
        v-for="item in results"
        :key="item.id"
        class="flex gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:shadow-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
      >
        <span
          class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-[#163354] dark:text-slate-300"
          ><Package v-if="item.type === 'Produk'" :size="24" /><Wrench v-else :size="24"
        /></span>
        <div class="min-w-0 flex-1">
          <div class="flex justify-between gap-2">
            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
              {{ item.name }}
            </p>
            <Badge :variant="item.type === 'Produk' ? 'info' : 'success'">{{ item.type }}</Badge>
          </div>
          <p class="mt-1 text-xs text-slate-400">{{ item.sku }}</p>
          <div class="mt-3 flex items-end justify-between">
            <div>
              <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                {{ money(item.price) }}
              </p>
              <p v-if="item.stock" class="mt-1 text-xs text-slate-500">Stok: {{ item.stock }}</p>
            </div>
            <Button size="sm" @click="emit('add', item)"
              ><Plus :size="15" class="mr-1" />Tambah</Button
            >
          </div>
        </div>
      </article>
    </div>
    <p v-if="error" class="py-6 text-center text-sm text-red-500">{{ error }}</p>
    <p v-else-if="!results.length" class="py-10 text-center text-sm text-slate-500">
      {{ props.branchId ? 'Tidak ada produk ditemukan.' : 'Pilih cabang terlebih dahulu.' }}
    </p>
  </section>
</template>
