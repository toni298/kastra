<script setup>
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({ item: { type: Object, required: true } })
const emit = defineEmits(['close', 'saved'])
const form = useForm({ details: props.item.details.map((detail) => ({ id: detail.detail_id, received_quantity: detail.quantity, adjustment_note: '' })) })
const submit = () => form.post(route('inventory.transfers.receive', props.item.id), { onSuccess: () => emit('saved', { type: 'transfer' }) })
</script>

<template>
  <Modal :model-value="true" title="Terima Transfer" description="Konfirmasi jumlah barang yang diterima." size="lg" @update:model-value="emit('close')">
    <form id="receive-transfer-form" class="space-y-4" @submit.prevent="submit"><div v-for="(detail, index) in item.details" :key="detail.detail_id" class="rounded-xl border p-4 dark:border-[#29476b]"><div class="flex justify-between"><div><p class="font-medium dark:text-white">{{ detail.product }}</p><p class="text-xs text-slate-500">Dikirim {{ detail.quantity }} {{ detail.unit }}</p></div><span class="text-xs text-slate-500">{{ detail.sku }}</span></div><div class="mt-3 grid gap-3 sm:grid-cols-2"><Input v-model="form.details[index].received_quantity" type="number" min="0" :max="detail.quantity" label="Jumlah Diterima" required /><Input v-model="form.details[index].adjustment_note" label="Catatan Selisih" placeholder="Contoh: 2 pcs rusak" /></div></div></form>
    <template #footer><Button variant="secondary" @click="emit('close')">Batal</Button><Button type="submit" form="receive-transfer-form" :disabled="form.processing">Simpan Penerimaan</Button></template>
  </Modal>
</template>
