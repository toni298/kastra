<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import Modal from '@/Components/UI/Modal.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'

const props = defineProps({ transaction: { type: Object, required: true } })
const emit = defineEmits(['close', 'saved'])
const parseAmount = (value) => {
  if (typeof value === 'number' && Number.isFinite(value)) return Math.max(0, Math.trunc(value))
  const digits = String(value ?? '').replace(/[^0-9]/g, '')
  return digits ? Number(digits) : 0
}
const proofFile = ref(null)
const form = useForm({
  payment_method: props.transaction.payment?.method ?? 'cash',
  amount: 0,
  payment_date: new Date().toISOString().slice(0, 10),
  proof_file: null,
})
const outstanding = computed(() => parseAmount(props.transaction.outstanding))
const submitting = ref(false)
const submit = () => {
  if (submitting.value || form.processing) return
  submitting.value = true
  form.amount = Math.min(parseAmount(form.amount), outstanding.value)
  form.proof_file = proofFile.value
  form.post(route('sales.transactions.payments.store', props.transaction.id), {
    onSuccess: () => emit('saved'),
    onFinish: () => {
      submitting.value = false
    },
  })
}
</script>

<template>
  <Modal
    :model-value="true"
    title="Tambah Pembayaran"
    description="Catat pembayaran untuk transaksi penjualan ini."
    size="md"
    @update:model-value="emit('close')"
  >
    <form id="sales-payment-form" class="grid gap-4" @submit.prevent="submit">
      <SelectInput
        v-model="form.payment_method"
        label="Metode Pembayaran"
        required
        aria-label="Metode pembayaran"
        ><option value="cash">Cash</option>
        <option value="transfer">Transfer</option>
        <option value="qris">QRIS</option>
        <option value="ewallet">E-Wallet</option></SelectInput
      >
      <CurrencyInput
        v-model="form.amount"
        label="Jumlah Pembayaran"
        :max="outstanding"
        :hint="`Maksimal ${new Intl.NumberFormat('id-ID').format(outstanding)}`"
        required
      />
      <DatePicker v-model="form.payment_date" label="Tanggal Pembayaran" required />
      <FileUpload
        :model-value="proofFile"
        accept="image/jpeg,image/png,image/webp,application/pdf"
        label="Bukti Pembayaran (opsional)"
        hint="JPG, PNG, WebP, atau PDF. Maks. 5 MB."
        :max-size="5"
        :show-preview="true"
        @update:model-value="proofFile = $event"
      />
    </form>
    <template #footer
      ><Button variant="secondary" :disabled="submitting" @click="emit('close')">Batal</Button
      ><Button type="submit" form="sales-payment-form" :disabled="submitting || form.processing"
        >Simpan Pembayaran</Button
      ></template
    >
  </Modal>
</template>
