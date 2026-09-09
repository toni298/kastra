<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RotateCcw, SlidersHorizontal, X } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'

const props = defineProps({
  filters: { type: Object, default: () => ({}) },
  options: {
    type: Object,
    default: () => ({ categories: [], brands: [], branches: [], warehouses: [] }),
  },
  panelStyle: { type: Object, default: () => ({}) },
  triggerElement: { type: Object, default: null },
  exporting: { type: Boolean, default: false },
})
const emit = defineEmits(['apply', 'close', 'export'])
const panelElement = ref(null)
const exportOpen = ref(false)
const form = reactive({
  search: props.filters.search ?? '',
  category_id: props.filters.category_id ?? '',
  brand_id: props.filters.brand_id ?? '',
  branch_id: props.filters.branch_id ?? '',
  gudang_id: props.filters.gudang_id ?? '',
  movement_type: props.filters.movement_type ?? '',
  date_from: props.filters.date_from ?? '',
  date_to: props.filters.date_to ?? '',
})
const reset = () => {
  Object.assign(form, {
    search: '',
    category_id: '',
    brand_id: '',
    branch_id: '',
    gudang_id: '',
    movement_type: '',
    date_from: '',
    date_to: '',
  })
  emit('apply', { ...form })
}
const syncFilters = (value) =>
  Object.assign(form, {
    search: value.search ?? '',
    category_id: value.category_id ?? '',
    brand_id: value.brand_id ?? '',
    branch_id: value.branch_id ?? '',
    gudang_id: value.gudang_id ?? '',
    movement_type: value.movement_type ?? '',
    date_from: value.date_from ?? '',
    date_to: value.date_to ?? '',
  })
watch(() => props.filters, syncFilters, { deep: true })
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
  <Teleport to="body">
    <div
      ref="panelElement"
      class="fixed z-[120] w-[min(92vw,600px)] rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      :style="panelStyle"
    >
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="font-semibold text-slate-950 dark:text-white">Filter Laporan Stok</h3>
          <p class="mt-1 text-xs text-slate-500">
            Gunakan filter untuk mempersempit data stok dan mutasi.
          </p>
        </div>
        <button
          type="button"
          class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100"
          aria-label="Tutup filter"
          @click="emit('close')"
        >
          <X :size="18" />
        </button>
      </div>
      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <DateRangePicker
          v-model:start="form.date_from"
          v-model:end="form.date_to"
          label="Periode"
          class="col-span-full"
        />
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Kategori<select
            v-model="form.category_id"
            class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua Kategori</option>
            <option v-for="item in options.categories" :key="item.id" :value="item.id">
              {{ item.name }}
            </option>
          </select></label
        >
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Brand<select
            v-model="form.brand_id"
            class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua Brand</option>
            <option v-for="item in options.brands" :key="item.id" :value="item.id">
              {{ item.name }}
            </option>
          </select></label
        >
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Cabang<select
            v-model="form.branch_id"
            class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua Cabang</option>
            <option v-for="item in options.branches" :key="item.id" :value="item.id">
              {{ item.name }}
            </option>
          </select></label
        >
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Gudang<select
            v-model="form.gudang_id"
            class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua Gudang</option>
            <option v-for="item in options.warehouses" :key="item.id" :value="item.id">
              {{ item.nama }}
            </option>
          </select></label
        >
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Tipe Pergerakan<select
            v-model="form.movement_type"
            class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua Tipe</option>
            <option value="IN">Masuk</option>
            <option value="OUT">Keluar</option>
            <option value="ADJUSTMENT">Adjustment</option>
            <option value="TRANSFER">Transfer</option>
          </select></label
        >
      </div>
      <div class="mt-5 flex justify-between border-t border-slate-100 pt-4">
        <Button variant="ghost" size="sm" @click="reset"
          ><RotateCcw :size="15" class="mr-2" />Reset Filter</Button
        >
        <div class="relative flex items-center gap-2">
          <div class="relative">
            <Button
              variant="secondary"
              size="sm"
              :disabled="exporting"
              @click.stop="exportOpen = !exportOpen"
              >Export <span aria-hidden="true">▾</span></Button
            >
            <div
              v-if="exportOpen"
              class="absolute bottom-full right-0 z-10 mb-2 w-36 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl dark:border-[#29476b] dark:bg-[#102542]"
            >
              <button
                type="button"
                class="block w-full px-3 py-2 text-left text-sm hover:bg-emerald-50"
                :disabled="exporting"
                @click="
                  exportOpen = false;
                  emit('export', 'excel')
                "
              >
                Excel</button
              ><button
                type="button"
                class="block w-full px-3 py-2 text-left text-sm hover:bg-emerald-50"
                :disabled="exporting"
                @click="
                  exportOpen = false;
                  emit('export', 'pdf')
                "
              >
                PDF
              </button>
            </div>
          </div>
          <Button size="sm" :disabled="exporting" @click="emit('apply', { ...form })"
            ><SlidersHorizontal :size="15" class="mr-2" />Terapkan</Button
          >
        </div>
      </div>
    </div>
  </Teleport>
</template>
