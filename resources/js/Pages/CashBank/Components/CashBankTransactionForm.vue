<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowDownToLine } from 'lucide-vue-next'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import CashBankProofField from './CashBankProofField.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  item: { type: Object, default: null },
  accounts: { type: Array, default: () => [] },
  initialType: { type: String, default: 'in' },
})
const emit = defineEmits(['close', 'saved'])
const submitted = ref(false)
const proofFile = ref(null)
const proofFileRemoved = ref(false)
const initialProofFile = props.item?.proof_file_url
  ? [{ id: 'proof', name: 'Bukti Transaksi', url: props.item.proof_file_url, source: null }]
  : []
const form = reactive({
  account: props.item?.cash_bank_account_id ?? '',
  type: props.item?.type ?? props.initialType,
  category: props.item?.category ?? '',
  amount: props.item?.amount_value ?? '',
  date: props.item?.isoDate ?? new Date().toISOString().slice(0, 10),
  note: props.item?.note ?? '',
})
const typeOptions = [
  { value: 'in', label: 'Kas Masuk' },
  { value: 'out', label: 'Kas Keluar' },
  { value: 'none', label: 'Tidak Mempengaruhi Kas' },
]
const compatibleAccounts = computed(() =>
  props.accounts.filter(
    (account) =>
      form.type === 'none' ||
      account.settings?.some(
        (setting) =>
          setting.is_active &&
          (form.type === 'in' ? setting.can_receive_money : setting.can_send_money)
      )
  )
)
const removeInitialPreview = () => {
  proofFile.value = null
  proofFileRemoved.value = true
}
const submit = () => {
  submitted.value = true
  if (!valid.value) return
  useForm({
    cash_bank_account_id: form.account || null,
    type: form.type,
    category: form.category,
    amount: Number(form.amount),
    transaction_date: form.date,
    note: form.note || null,
    proof_file: proofFile.value || null,
    proof_file_removed: proofFileRemoved.value,
  }).submit(
    props.item ? 'put' : 'post',
    props.item
      ? route('cash-bank.transactions.update', props.item.id)
      : route('cash-bank.transactions.store'),
    { preserveScroll: true, onSuccess: () => emit('saved') }
  )
}
const accountOptions = computed(() =>
  compatibleAccounts.value.map((account) => ({
    id: account.id,
    text: [account.name, account.bank].filter(Boolean).join(' - '),
    currency: account.currency,
  }))
)
const accountEndpoint = computed(() => `${route('cash-bank.accounts.search')}?type=${form.type}`)
const currency = computed(
  () => props.accounts.find((account) => account.id === form.account)?.currency ?? 'IDR'
)
const valid = computed(
  () => form.category && form.amount && form.date && (form.type === 'none' || form.account)
)
watch(
  () => form.type,
  () => {
    if (form.account && !compatibleAccounts.value.some((account) => account.id === form.account))
      form.account = ''
  }
)
const select =
  'mt-1.5 w-full rounded-xl border-slate-300 px-3.5 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white'
</script>
<template>
  <Modal
    :model-value="true"
    :title="item ? 'Edit Transaksi Kas & Bank' : 'Transaksi Kas & Bank'"
    description="Catat transaksi kas sesuai rekening dan penggunaannya."
    size="xl"
    @update:model-value="emit('close')"
    ><div
      class="mb-5 flex gap-3 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300"
    >
      <ArrowDownToLine :size="19" />Data akan disimpan ke database perusahaan.
    </div>
    <form id="transaction-form" class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
      <label class="sm:col-span-2"
        ><span class="text-sm font-medium text-slate-700 dark:text-slate-200"
          >Jenis Transaksi *</span
        ><select v-model="form.type" :class="select">
          <option v-for="option in typeOptions" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select></label
      ><AsyncSelect
        v-model="form.account"
        label="Rekening"
        :endpoint="accountEndpoint"
        :initial-options="accountOptions"
        :additional-options="accountOptions"
        :clearable="true"
        :required="form.type !== 'none'"
        placeholder="Cari dan pilih rekening..."
      /><label
        ><span class="text-sm font-medium text-slate-700 dark:text-slate-200">Kategori *</span
        ><select v-model="form.category" :class="select">
          <option value="">Pilih kategori</option>
          <option v-if="form.type === 'in'">Pendapatan Lain</option>
          <option v-if="form.type === 'in'">Penjualan Tunai</option>
          <option v-if="form.type === 'in'">Setoran Modal</option>
          <option v-if="form.type === 'out'">Operasional</option>
          <option v-if="form.type === 'out'">Utilitas</option>
          <option v-if="form.type === 'out'">Biaya Lainnya</option>
          <option v-if="form.type === 'none'">Penyesuaian Informasi</option>
        </select></label
      ><CurrencyInput
        v-model="form.amount"
        label="Nominal"
        :currency="currency"
        placeholder="Contoh: 1500000"
        required
      /><DatePicker
        v-model="form.date"
        label="Tanggal"
        placeholder="Pilih tanggal transaksi"
        required
      /><CashBankProofField
        v-model="proofFile"
        class="sm:col-span-2"
        :initial-file="initialProofFile[0] ?? null"
        :error="''"
        @removed="removeInitialPreview"
      /><label class="sm:col-span-2"
        ><span class="text-sm font-medium text-slate-700 dark:text-slate-200">Catatan</span
        ><textarea
          v-model="form.note"
          rows="3"
          :class="select"
          placeholder="Catatan transaksi (opsional)"
        ></textarea>
      </label>
      <p
        v-if="submitted && !valid"
        class="rounded-xl bg-red-50 p-3 text-sm font-medium text-red-600 dark:bg-red-400/10 dark:text-red-300 sm:col-span-2"
      >
        Lengkapi semua field wajib dengan data yang valid.
      </p>
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="transaction-form">Simpan</Button></template
    ></Modal
  >
</template>
