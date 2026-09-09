<script setup>
import { computed, reactive, ref } from 'vue'
import { ReceiptText } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import Input from '@/Components/UI/Input.vue'
import { formatCurrency } from '@/Utils/helpers'

const props = defineProps({
  transaction: { type: Object, required: true },
})

const emit = defineEmits(['cancel', 'submit'])

const form = reactive({
  invoiceNumber: '',
  invoiceDate: props.transaction.date,
  dueDate: '',
  subtotal: props.transaction.totalValue,
  tax: 0,
  extraFee: 0,
  note: '',
})
const attachment = ref(null)
const submitted = ref(false)

const grandTotal = computed(() => form.subtotal + form.tax + form.extraFee)
const valid = computed(
  () =>
    Boolean(form.invoiceNumber && form.invoiceDate && form.dueDate) &&
    form.subtotal > 0 &&
    Number.isInteger(form.subtotal) &&
    Number.isInteger(form.tax) &&
    Number.isInteger(form.extraFee)
)

const selectAttachment = (file) => {
  attachment.value = file
}
const submit = () => {
  submitted.value = true
  if (!valid.value) return

  emit('submit', {
    type: 'create-invoice',
    data: { ...form, grandTotal: grandTotal.value, attachment: attachment.value },
  })
}
</script>

<template>
  <form id="purchase-invoice-form" class="space-y-5" @submit.prevent="submit">
    <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-[#29476b]">
      <header
        class="flex items-start gap-3 border-b border-slate-100 px-5 py-4 dark:border-[#29476b]"
      >
        <ReceiptText :size="19" class="mt-0.5 text-emerald-600" />
        <div>
          <h3 class="font-semibold text-slate-900 dark:text-white">Informasi Invoice Supplier</h3>
          <p class="mt-0.5 text-sm text-slate-500">Referensi {{ transaction.number }}</p>
        </div>
      </header>
      <div class="grid gap-4 p-5 sm:grid-cols-2">
        <Input
          v-model="form.invoiceNumber"
          label="Nomor Invoice Supplier"
          placeholder="Contoh: INV-SUP-001"
          required
        />
        <DatePicker v-model="form.invoiceDate" label="Tanggal Invoice" required />
        <DatePicker v-model="form.dueDate" label="Jatuh Tempo" required />
        <Input v-model="form.note" label="Catatan" placeholder="Opsional" />
      </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-[#29476b]">
      <header class="border-b border-slate-100 px-5 py-4 dark:border-[#29476b]">
        <h3 class="font-semibold text-slate-900 dark:text-white">Nilai Invoice</h3>
        <p class="mt-0.5 text-sm text-slate-500">Nominal Rupiah tanpa pecahan desimal.</p>
      </header>
      <div class="grid gap-4 p-5 sm:grid-cols-2">
        <CurrencyInput v-model="form.subtotal" label="Subtotal" />
        <CurrencyInput v-model="form.tax" label="Pajak" />
        <CurrencyInput v-model="form.extraFee" label="Biaya Tambahan" />
        <div class="rounded-xl bg-slate-950 p-4 text-white dark:bg-emerald-400 dark:text-[#071426]">
          <p class="text-xs font-medium uppercase tracking-wide opacity-60">Total Invoice</p>
          <p class="mt-1 text-xl font-bold">{{ formatCurrency(grandTotal) }}</p>
        </div>
      </div>
    </section>

    <section>
      <FileUpload
        accept=".pdf,.jpg,.jpeg,.png"
        label="Lampirkan invoice supplier"
        hint="PDF, JPG, atau PNG. Maks. 5 MB."
        :max-size="5"
        @select="selectAttachment"
      />
      <p v-if="attachment" class="mt-2 text-xs font-medium text-emerald-700 dark:text-emerald-300">
        {{ attachment.name }}
      </p>
    </section>

    <p
      v-if="submitted && !valid"
      class="rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600 dark:bg-red-400/10 dark:text-red-300"
    >
      Lengkapi nomor, tanggal, jatuh tempo, dan nilai invoice dengan benar.
    </p>

    <div
      class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-4 dark:border-[#29476b]"
    >
      <Button variant="secondary" @click="emit('cancel')">Batal</Button>
      <Button type="submit">Simpan Purchase Invoice</Button>
    </div>
  </form>
</template>
