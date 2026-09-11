<script setup>
import { reactive } from 'vue'
import { RotateCcw, SlidersHorizontal } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
  filters: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['close', 'apply'])
const form = reactive({
  role: props.filters.role ?? '',
  status: props.filters.status ?? '',
})
const reset = () => {
  form.role = ''
  form.status = ''
}
const apply = () => emit('apply', { ...form })
</script>

<template>
  <div class="contents">
    <div
      class="absolute left-0 top-full z-50 mt-2 w-[min(22rem,calc(100vw-2rem))] rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      @click.stop
    >
      <div class="grid gap-4 sm:grid-cols-2">
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
          Role
          <select
            v-model="form.role"
            class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua role</option>
            <option value="cashier">Kasir</option>
            <option value="supervisor">Supervisor</option>
            <option value="staff">Staff</option>
          </select>
        </label>
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
          Status
          <select
            v-model="form.status"
            class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
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
    <div class="fixed inset-0 z-40" aria-hidden="true" @click="emit('close')"></div>
  </div>
</template>
