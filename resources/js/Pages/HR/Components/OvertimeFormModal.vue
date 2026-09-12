<script setup>
import { ref } from 'vue'
import axios from 'axios'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import { useToastify } from '@/Composables/useToastify'

defineProps({})
const emit = defineEmits(['close', 'saved'])
const toast = useToastify()
const processing = ref(false)
const form = ref({ employee_id: '', overtime_date: new Date().toISOString().slice(0, 10), hours: 1, hourly_rate: 0, reason: '' })
const submit = async () => {
  processing.value = true
  try { await axios.post(route('api.hr.overtimes.store'), form.value); toast.success('Pengajuan lembur berhasil dicatat.'); emit('saved'); emit('close') }
  catch (error) { toast.error(error.response?.data?.message || 'Pengajuan lembur gagal disimpan.') }
  finally { processing.value = false }
}
</script>

<template>
  <Modal :model-value="true" title="Catat Lembur Manual" description="Masukkan detail pengajuan lembur karyawan." size="lg" @update:model-value="emit('close')">
    <form id="overtime-form" class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
      <AsyncSelect v-model="form.employee_id" class="sm:col-span-2" label="Karyawan" endpoint="/search/employees" placeholder="Cari nama atau NIK..." required />
      <DatePicker v-model="form.overtime_date" label="Tanggal" required />
      <label class="text-sm">Jumlah jam<input v-model.number="form.hours" required type="number" min="0.25" max="24" step="0.25" class="mt-1 w-full rounded-xl border-slate-200" /></label>
      <CurrencyInput v-model="form.hourly_rate" label="Tarif per jam" :min="0" required />
      <label class="text-sm sm:col-span-2">Alasan / Keterangan<textarea v-model="form.reason" required rows="3" class="mt-1 w-full rounded-xl border-slate-200"></textarea></label>
    </form>
    <template #footer><Button variant="secondary" @click="emit('close')">Batal</Button><Button type="submit" form="overtime-form" :loading="processing">Simpan Pengajuan</Button></template>
  </Modal>
</template>
