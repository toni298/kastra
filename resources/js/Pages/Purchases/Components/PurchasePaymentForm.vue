<script setup>
import { computed, reactive, ref } from 'vue'
import { CreditCard } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import Input from '@/Components/UI/Input.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import { formatCurrency } from '@/Utils/helpers'
import { usePurchasePayment } from '../Composables/usePurchasePayment'

const props = defineProps({
  transaction: { type: Object, required: true },
})

const emit = defineEmits(['cancel', 'submit'])

const remaining = computed(() => {
  const r = props.transaction.remainingValue
  if (r != null) return Number(r)
  const total = Number(props.transaction.totalValue ?? 0)
  const paid = Number(props.transaction.paidValue ?? 0)
  return Math.max(0, total - paid)
})

const {
  paymentMethod,
  paymentAmount,
  ownerAmount,
  accountBalance,
  useCombinedPayment,
  fetchingBalance,
  totalCovered,
  canUseCombined,
  valid,
  getPaymentPayload,
} = usePurchasePayment(remaining)

const form = reactive({
  paymentDate: props.transaction.date_iso ?? props.transaction.date,
  reference: '',
  note: '',
})
const proofFile = ref(null)
const submitted = ref(false)

const formValid = computed(() => valid.value && Boolean(form.paymentDate) && totalCovered.value > 0)

const submit = () => {
  submitted.value = true
  if (!formValid.value) return
  const payload = getPaymentPayload()
  emit('submit', {
    type: 'pay-supplier',
    data: {
      ...form,
      amount: payload.paymentAmount,
      owner_amount: payload.ownerAmount,
      method: payload.paymentMethod,
      proof_file: proofFile.value,
    },
  })
}
</script>

<template>
  <form id="purchase-payment-form" class="space-y-5" @submit.prevent="submit">
    <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-[#29476b]">
      <header
        class="flex items-start gap-3 border-b border-slate-100 px-5 py-4 dark:border-[#29476b]"
      >
        <CreditCard :size="19" class="mt-0.5 text-emerald-600" />
        <div>
          <h3 class="font-semibold text-slate-900 dark:text-white">Informasi Pembayaran</h3>
          <p class="mt-0.5 text-sm text-slate-500">Sisa tagihan {{ formatCurrency(remaining) }}</p>
        </div>
      </header>
      <div class="grid gap-4 p-5 sm:grid-cols-2">
        <DatePicker v-model="form.paymentDate" label="Tanggal Pembayaran" required />
        <label>
          <span class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
            Metode Pembayaran <span class="text-red-500">*</span>
          </span>
          <SelectInput v-model="paymentMethod" class="w-full" aria-label="Metode pembayaran">
            <option value="">Pilih metode</option>
            <option value="transfer_bank">Transfer Bank</option>
            <option value="tunai">Tunai</option>
            <option value="giro">Giro</option>
          </SelectInput>
        </label>
        <div v-if="accountBalance !== null" class="text-xs text-slate-500">
          Saldo Rekening: <strong>{{ formatCurrency(accountBalance) }}</strong>
          <span v-if="fetchingBalance">(memuat...)</span>
        </div>
        <div
          v-if="canUseCombined"
          class="flex items-center gap-2 rounded-lg bg-amber-50 p-2 text-xs text-amber-800 dark:bg-amber-400/10 dark:text-amber-300 sm:col-span-2"
        >
          <input
            v-model="useCombinedPayment"
            type="checkbox"
            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
          />Saldo tidak mencukupi — gunakan pembayaran gabungan
        </div>
        <template v-if="!useCombinedPayment">
          <CurrencyInput
            :model-value="paymentAmount"
            label="Jumlah Dibayar"
            :max="accountBalance"
            :hint="`Maksimal ${new Intl.NumberFormat('id-ID').format(accountBalance)}`"
            required
            @update:model-value="paymentAmount = $event"
          />
        </template>
        <template v-else>
          <CurrencyInput
            :model-value="paymentAmount"
            label="Dari Rekening Perusahaan"
            :max="accountBalance ?? remaining"
            :hint="
              accountBalance !== null
                ? `Maks. saldo ${new Intl.NumberFormat('id-ID').format(accountBalance)}`
                : ''
            "
            placeholder="Maks. saldo rekening"
            required
            @update:model-value="paymentAmount = $event"
          />
          <CurrencyInput
            v-model="ownerAmount"
            label="Sumber Dana Lain"
            :max="Math.max(0, remaining - (accountBalance ?? 0))"
            :hint="`Maksimal ${new Intl.NumberFormat('id-ID').format(Math.max(0, remaining - (accountBalance ?? 0)))}`"
            placeholder="Sisa yang ditanggung sumber lain"
          />
          <div class="flex justify-between text-sm sm:col-span-2">
            <span class="text-slate-500">Total Gabungan</span>
            <strong :class="totalCovered === remaining ? 'text-emerald-600' : 'text-orange-600'">
              {{ formatCurrency(totalCovered) }}
            </strong>
          </div>
        </template>
        <Input v-model="form.reference" label="Nomor Referensi" placeholder="Opsional" />
        <Input v-model="form.note" label="Catatan" placeholder="Opsional" />
        <div class="sm:col-span-2">
          <FileUpload
            :model-value="proofFile"
            accept="image/jpeg,image/png,image/webp,application/pdf"
            label="Bukti Pembayaran (opsional)"
            hint="JPG, PNG, WebP, atau PDF. Maks. 5 MB."
            :max-size="5"
            :show-preview="true"
            @update:model-value="proofFile = $event"
          />
        </div>
      </div>
    </section>

    <p
      v-if="submitted && !formValid"
      class="rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600 dark:bg-red-400/10 dark:text-red-300"
    >
      Lengkapi data pembayaran. Jumlah tidak boleh melebihi sisa tagihan.
    </p>

    <div
      class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-4 dark:border-[#29476b]"
    >
      <Button variant="secondary" @click="emit('cancel')">Batal</Button>
      <Button type="submit" :disabled="!formValid">Simpan Pembayaran</Button>
    </div>
  </form>
</template>
