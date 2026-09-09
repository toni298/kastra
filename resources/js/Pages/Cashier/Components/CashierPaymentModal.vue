<script setup>
import { computed, ref, watch } from 'vue'
import Button from '@/Components/UI/Button.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'

const props = defineProps({
  total: { type: Number, default: 0 },
  transactionDate: { type: String, default: '' },
  isSubmitting: { type: Boolean, default: false },
})
const emit = defineEmits(['close', 'submit'])
const paymentMethod = ref('cash')
const paymentAmount = ref(0)
const paymentNote = ref('')
const dueDate = ref('')
const proofFile = ref(null)
watch(paymentMethod, (method) => {
  paymentAmount.value = method === 'cash' ? 0 : props.total
})
const unpaid = computed(() => paymentAmount.value < props.total)
const change = computed(() => Math.max(0, paymentAmount.value - props.total))
const money = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value || 0))
const formatPrice = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0))
const parseNumber = (value) => Number(String(value).replace(/\D/g, '')) || 0
const submit = () => {
  if (unpaid.value && !dueDate.value) return
  emit('submit', {
    paymentMethod: paymentMethod.value,
    paymentAmount: paymentAmount.value,
    paymentNote: unpaid.value ? paymentNote.value : '',
    dueDate: dueDate.value,
    proofFile: proofFile.value,
  })
}
</script>

<template>
  <Modal
    :model-value="true"
    title="Pembayaran"
    description="Pilih metode pembayaran dan konfirmasi jumlah yang diterima."
    size="lg"
    :close-on-overlay="false"
    @update:model-value="emit('close')"
  >
    <div class="space-y-4">
      <div class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
        <p class="text-xs text-slate-500">Total yang harus dibayar</p>
        <p class="mt-1 text-2xl font-bold text-blue-600">{{ money(total) }}</p>
      </div>
      <SelectInput v-model="paymentMethod" label="Mode Pembayaran" required>
        <option value="cash">Cash</option>
        <option value="transfer">Transfer</option>
        <option value="qris">QRIS</option>
        <option value="ewallet">E-Wallet</option>
      </SelectInput>
      <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
        Jumlah Dibayar
        <div
          class="mt-1.5 flex items-center rounded-xl border border-slate-300 bg-white px-3 dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <span class="text-sm font-semibold text-slate-500">Rp</span>
          <input
            :value="formatPrice(paymentAmount)"
            class="w-full border-0 bg-transparent py-2.5 pl-2 text-right text-sm text-slate-900 focus:ring-0 dark:text-white"
            inputmode="numeric"
            @input="paymentAmount = parseNumber($event.target.value)"
          />
        </div>
      </label>
      <DatePicker
        v-if="unpaid"
        v-model="dueDate"
        label="Jatuh Tempo"
        :min-date="transactionDate"
        required
      />
      <Input
        v-if="unpaid"
        v-model="paymentNote"
        label="Catatan Pembayaran"
        placeholder="Alasan atau catatan hutang"
      />
      <FileUpload
        v-model="proofFile"
        accept="image/jpeg,image/png,image/webp,application/pdf"
        label="Bukti Pembayaran (opsional)"
        hint="JPG, PNG, WebP, atau PDF. Maks. 5 MB."
        :max-size="5"
        :show-preview="true"
      />
      <div class="rounded-xl border border-slate-200 p-3 text-sm dark:border-[#29476b]">
        <div class="flex justify-between">
          <span class="text-slate-500">Status</span>
          <strong :class="unpaid ? 'text-amber-600' : 'text-emerald-600'">{{
            unpaid ? 'Menjadi Hutang' : 'Lunas'
          }}</strong>
        </div>
        <div v-if="!unpaid" class="mt-2 flex justify-between">
          <span class="text-slate-500">Kembalian</span>
          <strong class="text-emerald-600">{{ money(change) }}</strong>
        </div>
        <p v-else class="mt-2 text-xs text-amber-600">
          Sisa pembayaran: {{ money(total - paymentAmount) }}
        </p>
      </div>
    </div>
    <template #footer>
      <Button variant="secondary" :disabled="isSubmitting" @click="emit('close')">Batal</Button>
      <Button :disabled="isSubmitting || (unpaid && !dueDate)" @click="submit"
        >Konfirmasi Pembayaran</Button
      >
    </template>
  </Modal>
</template>
