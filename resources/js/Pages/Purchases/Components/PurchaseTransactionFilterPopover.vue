<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { RotateCcw, SlidersHorizontal, X } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'

const props = defineProps({
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ suppliers: [] }) },
  panelStyle: { type: Object, default: () => ({}) },
  triggerElement: { type: Object, default: null },
})
const emit = defineEmits(['close', 'apply'])
const panelElement = ref(null)
const form = reactive({
  supplier_id: props.filters.supplier_id ?? '',
  document_type: props.filters.document_type ?? '',
  status: props.filters.status ?? '',
  payment_status: props.filters.payment_status ?? '',
  date_from: props.filters.date_from ?? '',
  date_to: props.filters.date_to ?? '',
})
const reset = () =>
  Object.assign(form, {
    supplier_id: '',
    document_type: '',
    status: '',
    payment_status: '',
    date_from: '',
    date_to: '',
  })
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
      class="fixed z-[120] w-[min(92vw,520px)] rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      :style="panelStyle"
    >
      <div class="flex justify-between">
        <div>
          <h3 class="font-semibold dark:text-white">Filter Pembelian</h3>
          <p class="mt-1 text-xs text-slate-500">
            Supplier, dokumen, status, pembayaran, dan periode.
          </p>
        </div>
        <button @click="emit('close')"><X :size="18" /></button>
      </div>
      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <label
          >Supplier<select
            v-model="form.supplier_id"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm"
          >
            <option value="">Semua Supplier</option>
            <option v-for="item in options.suppliers" :key="item.id" :value="item.id">
              {{ item.name }}
            </option>
          </select></label
        ><label
          >Jenis Dokumen<select
            v-model="form.document_type"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm"
          >
            <option value="">Semua Dokumen</option>
            <option value="purchase_order">Purchase Order</option>
            <option value="purchase_invoice">Purchase Invoice</option>
          </select></label
        ><label
          >Status<select
            v-model="form.status"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm"
          >
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="ordered">Menunggu Barang</option>
            <option value="received">Barang Diterima</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select></label
        ><label
          >Pembayaran<select
            v-model="form.payment_status"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm"
          >
            <option value="">Semua Pembayaran</option>
            <option value="paid">Lunas</option>
            <option value="partial">Sebagian Dibayar</option>
            <option value="unpaid">Belum Dibayar</option>
          </select></label
        ><DateRangePicker
          v-model:start="form.date_from"
          v-model:end="form.date_to"
          label="Periode Pembelian"
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
    </div>
  </Teleport>
</template>
