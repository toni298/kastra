<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { RotateCcw, SlidersHorizontal } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'

const props = defineProps({
  period: { type: String, default: '' },
  status: { type: String, default: '' },
  panelStyle: { type: Object, default: () => ({}) },
  triggerElement: { type: Object, default: null },
})
const emit = defineEmits(['close', 'apply'])
const panelElement = ref(null)
const form = reactive({
  periodDate: props.period ? `${props.period}-01` : '',
  status: props.status,
})
const reset = () => {
  form.periodDate = ''
  form.status = ''
}
const apply = () => emit('apply', {
  period: form.periodDate ? form.periodDate.slice(0, 7) : '',
  status: form.status,
})
const handleOutsidePointerDown = (event) => {
  if (panelElement.value?.contains(event.target) || props.triggerElement?.contains(event.target)) return
  emit('close')
}
onMounted(() => document.addEventListener('pointerdown', handleOutsidePointerDown, true))
onBeforeUnmount(() => document.removeEventListener('pointerdown', handleOutsidePointerDown, true))
</script>

<template>
  <Teleport to="body">
    <div
      ref="panelElement"
      class="fixed z-[120] w-[min(92vw,22rem)] rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      :style="panelStyle"
      @click.stop
    >
      <h3 class="font-semibold text-slate-900 dark:text-white">Filter Payroll</h3>
      <p class="mt-1 text-xs text-slate-500">Pilih periode dan status payroll.</p>
      <div class="mt-4 grid gap-4">
        <DatePicker v-model="form.periodDate" label="Periode payroll" />
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
          Status
          <select v-model="form.status" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white">
            <option value="">Semua status</option>
            <option value="draft">Draft</option>
            <option value="approved">Approved</option>
            <option value="posted">Posted</option>
          </select>
        </label>
      </div>
      <div class="mt-4 flex justify-between border-t border-slate-100 pt-3 dark:border-[#29476b]">
        <Button variant="ghost" size="sm" @click="reset"><RotateCcw :size="15" class="mr-2" />Reset</Button>
        <Button size="sm" @click="apply"><SlidersHorizontal :size="15" class="mr-2" />Terapkan</Button>
      </div>
    </div>
  </Teleport>
</template>
