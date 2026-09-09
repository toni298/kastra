<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { RotateCcw, SlidersHorizontal, X } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'

const props = defineProps({
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ branches: [], warehouses: [], users: [] }) },
  panelStyle: { type: Object, default: () => ({}) },
  triggerElement: { type: Object, default: null },
})
const emit = defineEmits(['close', 'apply'])
const panelElement = ref(null)
const form = reactive({
  branch_id: props.filters.branch_id ?? '',
  gudang_id: props.filters.gudang_id ?? '',
  user_id: props.filters.user_id ?? '',
  movement_type: props.filters.movement_type ?? '',
  date_from: props.filters.date_from ?? '',
  date_to: props.filters.date_to ?? '',
})
const movementTypes = [
  { value: 'SALE', label: 'Penjualan' },
  { value: 'PURCHASE', label: 'Pembelian' },
  { value: 'OPNAME_ADJUSTMENT', label: 'Penyesuaian Opname' },
  { value: 'TRANSFER_IN', label: 'Transfer Stok' },
  { value: 'adjustment', label: 'Adjustment Manual' },
]
const reset = () =>
  Object.assign(form, { branch_id: '', gudang_id: '', user_id: '', movement_type: '', date_from: '', date_to: '' })
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
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="font-semibold text-slate-950 dark:text-white">Filter Mutasi Stok</h3>
          <p class="mt-1 text-xs text-slate-500">
            Filter cabang, gudang, user, tipe mutasi, dan periode.
          </p>
        </div>
        <button
          class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100"
          aria-label="Tutup filter"
          @click="emit('close')"
        >
          <X :size="18" />
        </button>
      </div>
      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Cabang<select
            v-model="form.branch_id"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <option value="">Semua Cabang</option>
            <option v-for="branch in options.branches" :key="branch.id" :value="branch.id">
              {{ branch.name }}
            </option>
          </select></label
        ><label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Gudang<select
            v-model="form.gudang_id"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <option value="">Semua Gudang</option>
            <option v-for="warehouse in options.warehouses" :key="warehouse.id" :value="warehouse.id">
              {{ warehouse.nama }}
            </option>
          </select></label
        ><label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Tipe Mutasi<select
            v-model="form.movement_type"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <option value="">Semua Tipe</option>
            <option v-for="type in movementTypes" :key="type.value" :value="type.value">
              {{ type.label }}
            </option>
          </select></label
        ><label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >User<select
            v-model="form.user_id"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <option value="">Semua User</option>
            <option v-for="user in options.users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select></label
        ><DateRangePicker
          v-model:start="form.date_from"
          v-model:end="form.date_to"
          label="Periode Mutasi"
          class="col-span-full"
        />
      </div>
      <div class="mt-5 flex justify-between border-t border-slate-100 pt-4">
        <Button variant="ghost" size="sm" @click="reset"
          ><RotateCcw :size="15" class="mr-2" />Reset</Button
        ><Button size="sm" @click="emit('apply', { ...form })"
          ><SlidersHorizontal :size="15" class="mr-2" />Terapkan</Button
        >
      </div>
    </div></Teleport
  >
</template>
