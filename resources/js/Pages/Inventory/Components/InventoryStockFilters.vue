<script setup>
import { reactive } from 'vue'
import { RotateCcw, SlidersHorizontal, X } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
  filters: { type: Object, default: () => ({}) },
  options: { type: Object, default: () => ({ warehouses: [], categories: [] }) },
})
const emit = defineEmits(['close', 'apply'])
const form = reactive({
  gudang_id: props.filters.gudang_id ?? '',
  category_id: props.filters.category_id ?? '',
  status: props.filters.status ?? '',
})
const reset = () => Object.assign(form, { gudang_id: '', category_id: '', status: '' })
</script>
<template>
  <div class="contents">
    <div
      class="absolute top-full left-0 z-50 mt-2 max-w-[calc(100vw-2rem)] w-[min(92vw,440px)] rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl sm:left-auto sm:-right-12 dark:border-[#29476b] dark:bg-[#102542]"
      @click.stop
    >
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="font-semibold text-slate-950 dark:text-white">Filter Stok</h3>
          <p class="mt-1 text-xs text-slate-500">
            Persempit stok berdasarkan Inventory, kategori, dan status.
          </p>
        </div>
        <button
          class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-[#163354]"
          aria-label="Tutup filter"
          @click="emit('close')"
        >
          <X :size="18" />
        </button>
      </div>
      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Inventory<select
            v-model="form.gudang_id"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <option value="">Semua Inventory</option>
            <option v-for="item in options.warehouses" :key="item.id" :value="item.id">
              {{ item.nama }}
            </option>
          </select></label
        ><label class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Kategori<select
            v-model="form.category_id"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <option value="">Semua Kategori</option>
            <option v-for="item in options.categories" :key="item.id" :value="item.id">
              {{ item.name }}
            </option>
          </select></label
        ><label class="text-sm font-medium text-slate-700 dark:text-slate-200 sm:col-span-2"
          >Status<select
            v-model="form.status"
            class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <option value="">Semua Status</option>
            <option value="safe">Aman</option>
            <option value="low">Hampir Habis</option>
            <option value="out">Habis</option>
          </select></label
        >
      </div>
      <div class="mt-5 flex justify-between border-t border-slate-100 pt-4 dark:border-[#29476b]">
        <Button variant="ghost" size="sm" @click="reset"
          ><RotateCcw :size="15" class="mr-2" />Reset</Button
        ><Button size="sm" @click="emit('apply', { ...form })"
          ><SlidersHorizontal :size="15" class="mr-2" />Terapkan</Button
        >
      </div>
    </div>
    <div class="fixed inset-0 z-40" aria-hidden="true" @click="emit('close')"></div>
  </div>
</template>
