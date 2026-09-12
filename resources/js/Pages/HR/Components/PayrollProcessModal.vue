<script setup>
import { ref } from 'vue'
import axios from 'axios'
import Button from '@/Components/UI/Button.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import Modal from '@/Components/UI/Modal.vue'
import { useToastify } from '@/Composables/useToastify'

const emit = defineEmits(['close', 'saved'])
const toast = useToastify()
const now = new Date()
const form = ref({ period: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`, cutoff_date: now.toISOString().slice(0, 10) })
const processing = ref(false)
const submit = async () => { processing.value = true; try { await axios.post(route('api.hr.payrolls.generate'), form.value); toast.success('Draft payroll berhasil dibuat.'); emit('saved'); emit('close') } catch (error) { toast.error(error.response?.data?.message || 'Payroll gagal dibuat.') } finally { processing.value = false } }
</script>
<template>
  <Modal :model-value="true" title="Proses Gaji Periode Baru" description="Buat draft payroll berdasarkan data master, komisi, dan lembur yang disetujui." size="md" @update:model-value="emit('close')"><form id="payroll-process-form" class="space-y-4" @submit.prevent="submit"><label class="block text-sm">Periode payroll<input v-model="form.period" required type="month" class="mt-1 w-full rounded-xl border-slate-300" /></label><DatePicker v-model="form.cutoff_date" label="Tanggal cut-off absensi & komisi" required /></form><template #footer><Button variant="secondary" @click="emit('close')">Batal</Button><Button type="submit" form="payroll-process-form" :loading="processing">Buat Draft Payroll</Button></template></Modal>
</template>
