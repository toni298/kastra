<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { RotateCcw, SlidersHorizontal } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'

const props = defineProps({
  mode: { type: String, default: 'commission' },
  range: { type: Array, default: () => ['', ''] },
  commissionType: { type: String, default: '' },
  overtimeStatus: { type: String, default: '' },
  panelStyle: { type: Object, default: () => ({}) },
  triggerElement: { type: Object, default: null },
})
const emit = defineEmits(['close', 'apply'])
const panelElement = ref(null)
const form = reactive({
  range: [...props.range],
  commissionType: props.commissionType,
  overtimeStatus: props.overtimeStatus,
})
const reset = () => {
  form.range = ['', '']
  form.commissionType = ''
  form.overtimeStatus = ''
}
const apply = () =>
  emit('apply', {
    range: [...form.range],
    commissionType: form.commissionType,
    overtimeStatus: form.overtimeStatus,
  })
const handleOutsidePointerDown = (event) => {
  if (
    panelElement.value?.contains(event.target) ||
    props.triggerElement?.contains(event.target) ||
    event.target.closest('[data-date-range-picker-popover]')
  ) return
  emit('close')
}
onMounted(() => document.addEventListener('pointerdown', handleOutsidePointerDown, true))
onBeforeUnmount(() => document.removeEventListener('pointerdown', handleOutsidePointerDown, true))
</script>

<template>
  <Teleport to="body">
    <div
      ref="panelElement"
      class="fixed z-[120] w-[min(92vw,28rem)] rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      :style="panelStyle"
      @click.stop
    >
      <h3 class="font-semibold text-slate-900 dark:text-white">Filter {{ mode === 'commission' ? 'Komisi' : 'Lembur' }}</h3>
      <p class="mt-1 text-xs text-slate-500">Pilih periode dan status data yang ingin ditampilkan.</p>
      <div class="mt-5 grid gap-4">
        <DateRangePicker v-model="form.range" label="Periode" />
        <label v-if="mode === 'commission'" class="text-sm font-medium text-slate-700 dark:text-slate-200">
          Skema komisi
          <select v-model="form.commissionType" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white">
            <option value="">Semua skema</option>
            <option value="percentage">% Omzet</option>
            <option value="per_quantity">Nominal Qty</option>
          </select>
        </label>
        <label v-else class="text-sm font-medium text-slate-700 dark:text-slate-200">
          Status lembur
          <select v-model="form.overtimeStatus" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white">
            <option value="">Semua status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </label>
      </div>
      <div class="mt-5 flex justify-between border-t border-slate-100 pt-4 dark:border-[#29476b]">
        <Button variant="ghost" size="sm" @click="reset"><RotateCcw :size="15" class="mr-2" />Reset</Button>
        <Button size="sm" @click="apply"><SlidersHorizontal :size="15" class="mr-2" />Terapkan</Button>
      </div>
    </div>
  </Teleport>
</template>
