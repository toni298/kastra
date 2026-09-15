<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { RotateCcw, SlidersHorizontal } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'

const props = defineProps({
  filters: { type: Object, default: () => ({}) },
  panelStyle: { type: Object, default: () => ({}) },
  triggerElement: { type: Object, default: null },
})
const emit = defineEmits(['close', 'apply'])
const panelElement = ref(null)
const form = reactive({
  date: props.filters.date ?? '',
  status: props.filters.status ?? '',
})
const reset = () => {
  form.date = ''
  form.status = ''
}
const apply = () => emit('apply', { ...form })
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
      class="fixed z-[120] w-[min(22rem,calc(100vw-2rem))] rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      :style="panelStyle"
      @click.stop
    >
      <div class="grid gap-4">
        <DatePicker v-model="form.date" label="Tanggal absensi" />
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
          Status
          <select
            v-model="form.status"
            class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua status</option>
            <option value="present">Tepat waktu</option>
            <option value="late">Terlambat</option>
            <option value="absent">Alpa</option>
            <option value="incomplete">Belum lengkap</option>
          </select>
        </label>
      </div>
      <div class="mt-4 flex justify-between border-t border-slate-100 pt-3 dark:border-[#29476b]">
        <Button variant="ghost" size="sm" @click="reset"
          ><RotateCcw :size="15" class="mr-2" />Reset</Button
        >
        <Button size="sm" @click="apply"
          ><SlidersHorizontal :size="15" class="mr-2" />Terapkan</Button
        >
      </div>
    </div>
  </Teleport>
</template>
