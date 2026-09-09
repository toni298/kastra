<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, CheckCircle2, ClipboardCheck, Save } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ opname: { type: Object, required: true } })
const search = ref('')
const form = useForm({
  note: props.opname.note ?? '',
  details: props.opname.details.map((detail) => ({
    id: detail.id,
    physical_quantity: detail.physical_quantity,
    note: detail.note ?? '',
  })),
})

const counted = computed(() => form.details.filter((detail) => detail.physical_quantity !== null && detail.physical_quantity !== '').length)
const complete = computed(() => counted.value === form.details.length && form.details.length > 0)
const progress = computed(() => form.details.length ? `${Math.round(counted.value / form.details.length * 100)}%` : '0%')
const filteredDetails = computed(() => {
  const keyword = search.value.trim().toLocaleLowerCase('id-ID')
  if (!keyword) return props.opname.details
  return props.opname.details.filter((item) => `${item.product} ${item.sku}`.toLocaleLowerCase('id-ID').includes(keyword))
})
const detailIndex = (id) => props.opname.details.findIndex((item) => item.id === id)
const save = () => form.transform((data) => ({ ...data, status: 'draft' })).put(route('inventory.opname.update', props.opname.id))
const finish = () => { if (complete.value) form.post(route('inventory.opname.complete', props.opname.id)) }
</script>

<template>
  <Head title="Proses Stock Opname" />
  <AuthenticatedLayout>
    <template #header>Stock Opname</template>
    <div class="space-y-5">
      <header class="sticky top-0 z-30 -mx-4 flex flex-col justify-between gap-4 border-b border-slate-200 bg-slate-50/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:flex-row sm:items-start sm:px-6 dark:border-[#29476b] dark:bg-[#0a1b33]/95">
        <div>
          <Link :href="route('inventory.opnames')" class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-emerald-600"><ArrowLeft :size="17" />Kembali ke Stock Opname</Link>
          <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"><ClipboardCheck :size="22" /></span>
            <div><h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ props.opname.number }}</h1><p class="mt-1 text-sm text-slate-500">{{ props.opname.warehouse }} · {{ props.opname.date }}</p></div>
          </div>
        </div>
        <div class="flex gap-2"><Button variant="secondary" :disabled="form.processing" @click="save"><Save :size="17" class="mr-2" />Simpan Draft</Button><Button :disabled="!complete || form.processing" @click="finish"><CheckCircle2 :size="17" class="mr-2" />Selesaikan Opname</Button></div>
      </header>

      <section class="sticky top-[113px] z-20 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-[#29476b] dark:bg-[#102542]">
        <div class="p-5">
          <div class="flex justify-between text-sm dark:text-slate-200"><span class="font-medium">Progress perhitungan</span><span class="font-semibold text-emerald-600">{{ counted }}/{{ form.details.length }} produk</span></div>
          <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-[#163354]"><div class="h-full rounded-full bg-emerald-600 transition-all" :style="{ width: progress }"></div></div>
        </div>
      </section>

      <section class="overflow-visible rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-[#29476b] dark:bg-[#102542]">
        <div class="sticky top-[191px] z-10 bg-white dark:bg-[#102542]">
          <div class="flex flex-col justify-between gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center dark:border-[#29476b]">
          <div><h2 class="font-semibold text-slate-950 dark:text-white">Perhitungan Stok Fisik</h2><p class="mt-1 text-sm text-slate-500">Masukkan jumlah fisik untuk setiap produk.</p></div>
          <SearchInput v-model="search" placeholder="Cari produk atau SKU..." aria-label="Cari produk opname" />
          </div>
          <table class="min-w-full table-fixed">
            <colgroup><col class="w-[28%]" /><col class="w-[15%]" /><col class="w-[15%]" /><col class="w-[15%]" /><col class="w-[27%]" /></colgroup>
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 shadow-sm dark:bg-[#163354] dark:text-slate-300"><tr><th class="px-5 py-3">Produk</th><th class="px-5 py-3">Stok Sistem</th><th class="px-5 py-3">Stok Fisik</th><th class="px-5 py-3">Selisih</th><th class="px-5 py-3">Catatan</th></tr></thead>
          </table>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full table-fixed">
            <colgroup><col class="w-[28%]" /><col class="w-[15%]" /><col class="w-[15%]" /><col class="w-[15%]" /><col class="w-[27%]" /></colgroup>
            <tbody class="divide-y divide-slate-100 text-slate-700 dark:divide-[#29476b] dark:text-slate-200">
              <tr v-for="item in filteredDetails" :key="item.id">
                <td class="px-5 py-4"><p class="font-medium dark:text-white">{{ item.product }}</p><p class="text-xs text-slate-400">{{ item.sku }}</p></td>
                <td class="px-5 py-4 font-medium">{{ item.system_quantity }} {{ item.unit }}</td>
                <td class="w-44 px-5 py-4"><Input v-model="form.details[detailIndex(item.id)].physical_quantity" type="number" min="0" label="" placeholder="0" /></td>
                <td class="px-5 py-4 font-semibold" :class="form.details[detailIndex(item.id)].physical_quantity === null || form.details[detailIndex(item.id)].physical_quantity === '' ? 'text-slate-400' : Number(form.details[detailIndex(item.id)].physical_quantity) - item.system_quantity < 0 ? 'text-red-600 dark:text-red-300' : 'text-emerald-600 dark:text-emerald-300'">{{ form.details[detailIndex(item.id)].physical_quantity === null || form.details[detailIndex(item.id)].physical_quantity === '' ? '-' : Number(form.details[detailIndex(item.id)].physical_quantity) - item.system_quantity }} {{ item.unit }}</td>
                <td class="min-w-56 px-5 py-4"><Input v-model="form.details[detailIndex(item.id)].note" label="" placeholder="Catatan selisih (opsional)" /></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="border-t border-slate-100 p-5 dark:border-[#29476b]"><label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Catatan Opname</label><textarea v-model="form.note" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" placeholder="Catatan umum stock opname"></textarea></div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
