<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RotateCcw, SlidersHorizontal, X } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'
const props = defineProps({
  filters: { type: Object, default: () => ({}) },
  panelStyle: { type: Object, default: () => ({}) },
  triggerElement: { type: Object, default: null },
})
const emit = defineEmits(['apply', 'close'])
const panelElement = ref(null)
const form = reactive({
  type: props.filters.type ?? '',
  category: props.filters.category ?? '',
  date_from: props.filters.date_from ?? '',
  date_to: props.filters.date_to ?? '',
})
const categories = computed(() =>
  form.type === 'in'
    ? ['Penjualan Tunai', 'Pendapatan Lain', 'Setoran Modal']
    : form.type === 'out'
      ? ['Operasional', 'Utilitas', 'Biaya Lainnya']
      : [
          'Penjualan Tunai',
          'Pendapatan Lain',
          'Setoran Modal',
          'Operasional',
          'Utilitas',
          'Biaya Lainnya',
        ]
)
watch(
  () => form.type,
  () => {
    if (!categories.value.includes(form.category)) form.category = ''
  }
)
const reset = () => Object.assign(form, { type: '', category: '', date_from: '', date_to: '' })
const handleOutsidePointerDown = (event) => {
  if (
    panelElement.value?.contains(event.target) ||
    props.triggerElement?.contains(event.target) ||
    event.target.closest('[data-date-range-picker-popover]')
  )
    return
  emit('close')
}
onMounted(() => document.addEventListener('pointerdown', handleOutsidePointerDown, true))
onBeforeUnmount(() => document.removeEventListener('pointerdown', handleOutsidePointerDown, true))
</script>
<template>
  <Teleport to="body"
    ><div
      ref="panelElement"
      class="fixed z-[120] w-[min(92vw,520px)] rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      :style="panelStyle"
    >
      <div class="flex justify-between">
        <div>
          <h3 class="font-semibold dark:text-white">Filter Transaksi</h3>
          <p class="mt-1 text-xs text-slate-500">Tipe transaksi, kategori, dan periode.</p>
        </div>
        <button class="text-slate-400" @click="emit('close')"><X :size="18" /></button>
      </div>
      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <label class="text-sm font-medium dark:text-slate-200"
          >Tipe Transaksi<select
            v-model="form.type"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua Tipe</option>
            <option value="in">Kas Masuk</option>
            <option value="out">Kas Keluar</option>
          </select></label
        ><label class="text-sm font-medium dark:text-slate-200"
          >Kategori<select
            v-model="form.category"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua Kategori</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </option>
          </select></label
        ><DateRangePicker
          v-model:start="form.date_from"
          v-model:end="form.date_to"
          label="Periode Transaksi"
          class="col-span-full"
        />
      </div>
      <div class="mt-5 flex justify-between border-t pt-4">
        <Button variant="ghost" size="sm" @click="reset"
          ><RotateCcw :size="15" class="mr-2" />Reset</Button
        ><Button size="sm" @click="emit('apply', { ...form })"
          ><SlidersHorizontal :size="15" class="mr-2" />Terapkan</Button
        >
      </div>
    </div></Teleport
  >
</template>
