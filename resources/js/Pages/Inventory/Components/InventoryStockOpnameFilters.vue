<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RotateCcw, SlidersHorizontal, X } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'

const props = defineProps({ filters: { type: Object, default: () => ({}) }, options: { type: Object, default: () => ({ warehouses: [], branches: [] }) }, panelStyle: { type: Object, default: () => ({}) }, triggerElement: { type: Object, default: null } })
const emit = defineEmits(['close', 'apply'])
const panelElement = ref(null)
const form = reactive({ location_type: props.filters.gudang_id ? 'gudang' : props.filters.branch_id ? 'branch' : '', gudang_id: props.filters.gudang_id ?? '', branch_id: props.filters.branch_id ?? '', status: props.filters.status ?? '', date_from: props.filters.date_from ?? '', date_to: props.filters.date_to ?? '' })
const reset = () => Object.assign(form, { location_type: '', gudang_id: '', branch_id: '', status: '', date_from: '', date_to: '' })
watch(() => form.location_type, () => { form.gudang_id = ''; form.branch_id = '' })
const apply = () => emit('apply', { gudang_id: form.gudang_id, branch_id: form.branch_id, status: form.status, date_from: form.date_from, date_to: form.date_to })
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
      <div class="flex items-start justify-between gap-4"><div><h3 class="font-semibold text-slate-950 dark:text-white">Filter Stock Opname</h3><p class="mt-1 text-xs text-slate-500">Filter lokasi, status, dan periode stock opname.</p></div><button class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="Tutup filter" @click="emit('close')"><X :size="18" /></button></div>
      <div class="mt-5 grid gap-4 sm:grid-cols-2"><label class="text-sm font-medium text-slate-700 dark:text-slate-200">Lokasi Stock Opname<select v-model="form.location_type" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"><option value="">Semua Lokasi</option><option value="gudang">Gudang</option><option value="branch">Cabang</option></select></label><label class="text-sm font-medium text-slate-700 dark:text-slate-200">Status<select v-model="form.status" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"><option value="">Semua Status</option><option value="draft">Draft</option><option value="in_progress">Proses</option><option value="completed">Selesai</option><option value="cancelled">Dibatalkan</option></select></label><label v-if="form.location_type === 'gudang'" class="text-sm font-medium text-slate-700 dark:text-slate-200 sm:col-span-2">Gudang<select v-model="form.gudang_id" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"><option value="">Semua Gudang</option><option v-for="item in options.warehouses" :key="item.id" :value="item.id">{{ item.nama }}</option></select></label><label v-else-if="form.location_type === 'branch'" class="text-sm font-medium text-slate-700 dark:text-slate-200 sm:col-span-2">Cabang<select v-model="form.branch_id" class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"><option value="">Semua Cabang</option><option v-for="item in options.branches" :key="item.id" :value="item.id">{{ item.name }}</option></select></label><DateRangePicker v-model:start="form.date_from" v-model:end="form.date_to" label="Periode Stock Opname" class="col-span-full" /></div>
      <div class="mt-5 flex justify-between border-t border-slate-100 pt-4"><Button variant="ghost" size="sm" @click="reset"><RotateCcw :size="15" class="mr-2" />Reset</Button><Button size="sm" @click="apply"><SlidersHorizontal :size="15" class="mr-2" />Terapkan</Button></div>
    </div>
  </Teleport>
</template>
