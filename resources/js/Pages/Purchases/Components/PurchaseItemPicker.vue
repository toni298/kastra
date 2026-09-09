<script setup>
import axios from 'axios'
import { computed, ref, watch } from 'vue'
import { Package, Plus, Search } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
  gudangId: { type: String, default: '' },
  branchId: { type: String, default: '' },
})
const emit = defineEmits(['add'])
const search = ref('')
const items = ref([])
const loading = ref(false)
const nextCursor = ref(null)
const hasMore = ref(false)
const activeId = computed(() => props.branchId || props.gudangId)
const load = async (append = false) => {
  if (!activeId.value) {
    items.value = []
    nextCursor.value = null
    hasMore.value = false
    return
  }
  loading.value = true
  try {
    const response = await axios.get(route('search.purchase-products'), {
      params: {
        branch_id: props.branchId || undefined,
        gudang_id: props.gudangId || undefined,
        search: search.value || undefined,
        cursor: append ? nextCursor.value : undefined,
      },
    })
    const data = response.data
    items.value = append ? [...items.value, ...(data.results ?? [])] : (data.results ?? [])
    nextCursor.value = data.next_cursor ?? null
    hasMore.value = Boolean(data.pagination?.more && nextCursor.value)
  } finally {
    loading.value = false
  }
}
const results = computed(() => items.value)
watch(
  activeId,
  () => {
    search.value = ''
    items.value = []
    nextCursor.value = null
    hasMore.value = false
    load()
  },
  { immediate: true }
)
watch(search, () => load())
const money = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value)
</script>

<template>
  <section>
    <div class="relative">
      <Search class="absolute left-3 top-3 text-slate-400" :size="16" />
      <input
        v-model="search"
        class="w-full rounded-xl border-slate-200 py-2.5 pl-10 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
        :disabled="!activeId"
        placeholder="Cari nama, SKU, atau barcode..."
      />
    </div>
    <p v-if="!activeId" class="py-8 text-center text-sm text-slate-500">
      Pilih gudang terlebih dahulu.
    </p>
    <p v-else-if="loading && !results.length" class="py-8 text-center text-sm text-slate-500">
      Memuat produk...
    </p>
    <div v-else class="mt-3 grid gap-3 sm:grid-cols-2">
      <article
        v-for="item in results"
        :key="item.id"
        class="rounded-xl border border-slate-200 p-3 dark:border-[#29476b]"
      >
        <div class="flex items-start gap-3">
          <span
            class="grid size-9 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10"
          >
            <Package :size="17" />
          </span>
          <div class="min-w-0 flex-1">
            <p class="truncate font-medium dark:text-white">{{ item.name }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ item.sku }} · {{ item.unit || 'Unit' }}</p>
            <p class="mt-2 text-sm font-semibold text-emerald-600">{{ money(item.price) }}</p>
          </div>
          <Button size="sm" @click="emit('add', item)"><Plus :size="14" /></Button>
        </div>
      </article>
    </div>
    <div v-if="hasMore" class="mt-4 flex justify-center">
      <Button variant="secondary" size="sm" :disabled="loading" @click="load(true)">
        {{ loading ? 'Memuat...' : 'Muat 10 Produk Berikutnya' }}
      </Button>
    </div>
    <p
      v-if="activeId && !results.length && !loading"
      class="py-8 text-center text-sm text-slate-500"
    >
      Tidak ada produk ditemukan.
    </p>
  </section>
</template>
