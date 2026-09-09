<script setup>
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({ warehouses: { type: Array, default: () => [] }, branches: { type: Array, default: () => [] }, item: { type: Object, default: null } })
const emit = defineEmits(['close', 'saved'])
const editing = Boolean(props.item)
const form = useForm({ source_type: props.item?.source_type ?? 'gudang', gudang_id: props.item?.warehouse_id ?? '', branch_id: props.item?.branch_id ?? '', opname_date: props.item?.date ?? new Date().toISOString().slice(0, 10), note: props.item?.note ?? '', details: props.item?.details?.map((detail) => ({ id: detail.id, physical_quantity: detail.physical_quantity, note: detail.note ?? '' })) ?? [] })
const submit = () => {
  const options = { onSuccess: () => emit('saved', { type: 'opname' }) }
  editing ? form.put(route('inventory.opname.update', props.item.id), options) : form.post(route('inventory.opnames.store'), options)
}
</script>

<template>
  <Modal :model-value="true" :title="editing ? 'Edit Stock Opname' : 'Buat Stock Opname'" :description="editing ? 'Perbarui catatan dan detail stock opname.' : 'Pilih sumber stok yang akan diperiksa.'" size="lg" @update:model-value="emit('close')">
    <form id="stock-opname-form" class="space-y-4" @submit.prevent="submit">
      <div><label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Sumber Stock Opname <span class="text-red-500">*</span></label><div class="grid grid-cols-2 gap-2"><button type="button" :disabled="editing" :class="form.source_type === 'gudang' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' : 'border-slate-200 text-slate-600 dark:border-[#29476b] dark:text-slate-300'" class="rounded-xl border px-3 py-2.5 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-60" @click="form.source_type = 'gudang'; form.branch_id = ''">Gudang</button><button type="button" :disabled="editing" :class="form.source_type === 'cabang' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' : 'border-slate-200 text-slate-600 dark:border-[#29476b] dark:text-slate-300'" class="rounded-xl border px-3 py-2.5 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-60" @click="form.source_type = 'cabang'; form.gudang_id = ''">Cabang</button></div></div>
      <AsyncSelect v-if="form.source_type === 'gudang'" v-model="form.gudang_id" label="Gudang" :endpoint="route('search.gudang')" :initial-options="warehouses.map((warehouse) => ({ id: warehouse.id, text: warehouse.nama }))" placeholder="Cari dan pilih gudang..." :error="form.errors.gudang_id" required />
      <AsyncSelect v-else v-model="form.branch_id" label="Cabang" :endpoint="route('search.cabang')" :initial-options="branches.map((branch) => ({ id: branch.id, text: branch.name }))" placeholder="Cari dan pilih cabang..." :error="form.errors.branch_id" required />
      <DatePicker v-model="form.opname_date" label="Tanggal Opname" :error="form.errors.opname_date" required />
      <div v-if="editing" class="space-y-4 rounded-xl border border-slate-200 p-4 dark:border-[#29476b]"><p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Detail Produk</p><div v-for="(detail, index) in form.details" :key="detail.id" class="grid gap-3 sm:grid-cols-[1fr_120px]"><p class="self-center text-sm text-slate-600 dark:text-slate-300">{{ props.item.details[index]?.product }}<span class="block text-xs text-slate-400">Stok sistem: {{ props.item.details[index]?.system_quantity }} {{ props.item.details[index]?.unit }}</span></p><Input v-model="form.details[index].physical_quantity" type="number" min="0" label="Stok Fisik" /></div></div>
      <label><span class="text-sm font-medium text-slate-700 dark:text-slate-200">Catatan</span><textarea v-model="form.note" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white" placeholder="Catatan umum opname"></textarea><p v-if="form.errors.note" class="mt-1.5 text-sm font-medium text-red-500">{{ form.errors.note }}</p></label>
    </form>
    <template #footer><Button variant="secondary" @click="emit('close')">Batal</Button><Button type="submit" form="stock-opname-form" :disabled="form.processing">{{ editing ? 'Simpan Perubahan' : 'Buat Stock Opname' }}</Button></template>
  </Modal>
</template>
