<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RotateCcw, SlidersHorizontal, X } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'

const props = defineProps({ filters: { type: Object, default: () => ({}) }, options: { type: Object, default: () => ({ warehouses: [], branches: [] }) }, panelStyle: { type: Object, default: () => ({}) }, triggerElement: { type: Object, default: null } })
const emit = defineEmits(['close', 'apply'])
const panelElement = ref(null)
const form = reactive({ source_gudang_id: props.filters.source_gudang_id ?? '', destination_type: props.filters.destination_type ?? '', destination_gudang_id: props.filters.destination_gudang_id ?? '', destination_branch_id: props.filters.destination_branch_id ?? '', status: props.filters.status ?? '', date_from: props.filters.date_from ?? '', date_to: props.filters.date_to ?? '' })
const reset = () => Object.assign(form, { source_gudang_id: '', destination_type: '', destination_gudang_id: '', destination_branch_id: '', status: '', date_from: '', date_to: '' })
watch(() => form.destination_type, () => { form.destination_gudang_id = ''; form.destination_branch_id = '' })
const handleOutsidePointerDown = (event) => {
  if (panelElement.value?.contains(event.target) || props.triggerElement?.contains(event.target) || event.target.closest('[data-date-range-picker-popover]')) return

  emit('close')
}

onMounted(() => document.addEventListener('pointerdown', handleOutsidePointerDown, true))
onBeforeUnmount(() => document.removeEventListener('pointerdown', handleOutsidePointerDown, true))
</script>
<template>
  <Teleport to="body">
  <div ref="panelElement" class="fixed z-[120] w-[min(92vw,520px)] rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-[#29476b] dark:bg-[#102542]" :style="panelStyle">
    <div class="flex items-start justify-between gap-4"><div><h3 class="font-semibold text-slate-950 dark:text-white">Filter Transfer</h3><p class="mt-1 text-xs text-slate-500">Persempit transfer berdasarkan lokasi, status, dan periode.</p></div><button class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="Tutup filter" @click="emit('close')"><X :size="18" /></button></div>
    <div class="mt-5 grid gap-4 sm:grid-cols-2"><label class="text-sm font-medium text-slate-700 dark:text-slate-200">Gudang Asal<select v-model="form.source_gudang_id" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"><option value="">Semua Gudang</option><option v-for="item in options.warehouses" :key="item.id" :value="item.id">{{ item.nama }}</option></select></label><label class="text-sm font-medium text-slate-700 dark:text-slate-200">Tipe Tujuan<select v-model="form.destination_type" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"><option value="">Semua Tujuan</option><option value="gudang">Gudang</option><option value="branch">Cabang</option></select></label><label v-if="form.destination_type === 'gudang'" class="text-sm font-medium text-slate-700 dark:text-slate-200 sm:col-span-2">Gudang Tujuan<select v-model="form.destination_gudang_id" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"><option value="">Semua Gudang Tujuan</option><option v-for="item in options.warehouses" :key="item.id" :value="item.id">{{ item.nama }}</option></select></label><label v-else-if="form.destination_type === 'branch'" class="text-sm font-medium text-slate-700 dark:text-slate-200 sm:col-span-2">Cabang Tujuan<select v-model="form.destination_branch_id" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"><option value="">Semua Cabang Tujuan</option><option v-for="item in options.branches" :key="item.id" :value="item.id">{{ item.name }}</option></select></label><label class="text-sm font-medium text-slate-700 dark:text-slate-200 sm:col-span-2">Status<select v-model="form.status" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"><option value="">Semua Status</option><option value="in_transit">Dalam Perjalanan</option><option value="received">Diterima</option><option value="partially_received">Diterima Sebagian</option><option value="cancelled">Dibatalkan</option></select></label><DateRangePicker v-model:start="form.date_from" v-model:end="form.date_to" label="Periode Transfer" class="col-span-full" /></div>
    <div class="mt-5 flex justify-between border-t border-slate-100 pt-4"><Button variant="ghost" size="sm" @click="reset"><RotateCcw :size="15" class="mr-2" />Reset</Button><Button size="sm" @click="emit('apply', { ...form })"><SlidersHorizontal :size="15" class="mr-2" />Terapkan</Button></div>
  </div>
  </Teleport>
</template>
