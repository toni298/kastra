<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'
import axios from 'axios'
import { FileText, Search, X } from 'lucide-vue-next'
import Spinner from '@/Components/UI/Spinner.vue'
import { formatCurrency, formatQty } from '@/Utils/helpers'

const props = defineProps({
  employee: { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['close'])
const items = ref([])
const loading = ref(false)
const error = ref('')
const search = ref('')
let requestId = 0

const load = async () => {
  if (!props.employee?.id) return
  const current = ++requestId
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get(
      route('api.hr.commissions.details', { employee: props.employee.id }),
      { params: { from: props.filters.from, to: props.filters.to } }
    )
    if (current === requestId) items.value = response.data?.data ?? []
  } catch (requestError) {
    if (current === requestId) error.value = requestError.response?.data?.message || 'Audit komisi gagal dimuat.'
  } finally {
    if (current === requestId) loading.value = false
  }
}
watch(() => props.employee?.id, () => { search.value = ''; items.value = []; load() }, { immediate: true })
onBeforeUnmount(() => { requestId += 1 })
const filteredItems = () => items.value.filter((item) => !search.value || item.invoice?.toLowerCase().includes(search.value.toLowerCase()))
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside class="fixed inset-y-0 right-0 z-50 flex w-full max-w-xl flex-col border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]">
      <header class="flex items-start justify-between border-b border-slate-100 p-5 dark:border-[#29476b]">
        <div class="flex items-center gap-3"><span class="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><FileText :size="20" /></span><div><p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Audit Komisi</p><h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ employee?.name }}</h2><p class="text-sm text-slate-500">{{ employee?.commission_type_label }}</p></div></div>
        <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100" aria-label="Tutup audit komisi" @click="emit('close')"><X :size="20" /></button>
      </header>
      <div class="flex-1 overflow-y-auto p-5">
        <div class="relative mb-5"><Search :size="17" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /><input v-model="search" type="search" placeholder="Cari nomor invoice..." class="w-full rounded-xl border-slate-200 py-2.5 pl-10 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" /></div>
        <div v-if="loading" class="flex min-h-56 items-center justify-center"><Spinner label="Memuat audit komisi..." /></div>
        <div v-else-if="error" class="rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ error }}</div>
        <div v-else-if="!filteredItems().length" class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">Tidak ada transaksi komisi.</div>
        <div v-else class="divide-y divide-slate-100 rounded-xl border border-slate-200 dark:divide-[#29476b] dark:border-[#29476b]">
          <div v-for="item in filteredItems()" :key="item.id" class="space-y-2 p-4"><div class="flex items-center justify-between gap-3"><span class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ item.invoice }}</span><span class="text-xs text-slate-500">{{ item.transaction_at || '-' }}</span></div><div class="grid grid-cols-3 gap-3 text-xs"><span class="text-slate-500">Qty<strong class="mt-1 block text-slate-900 dark:text-white">{{ formatQty(item.qty) }}</strong></span><span class="text-slate-500">Omzet<strong class="mt-1 block text-slate-900 dark:text-white">{{ formatCurrency(item.total) }}</strong></span><span class="text-slate-500">Komisi<strong class="mt-1 block text-emerald-700">{{ formatCurrency(item.commission) }}</strong></span></div></div>
        </div>
      </div>
    </aside>
  </Teleport>
</template>
