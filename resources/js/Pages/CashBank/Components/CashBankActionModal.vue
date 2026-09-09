<script setup>
import { computed, reactive, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowDownToLine, ArrowRightLeft, ArrowUpFromLine, Landmark } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import Modal from '@/Components/UI/Modal.vue'
const props = defineProps({
    type: { type: String, required: true },
    item: { type: Object, default: null },
    accounts: { type: Array, default: () => [] },
  }),
  emit = defineEmits(['close', 'saved']),
  submitted = ref(false)
const configs = {
  in: {
    title: props.item ? 'Edit Kas Masuk' : 'Kas Masuk',
    description: 'Catat penerimaan uang perusahaan.',
    icon: ArrowDownToLine,
    size: 'xl',
  },
  out: {
    title: props.item ? 'Edit Kas Keluar' : 'Kas Keluar',
    description: 'Catat pengeluaran uang perusahaan.',
    icon: ArrowUpFromLine,
    size: 'xl',
  },
  transfer: {
    title: props.item ? 'Edit Transfer Dana' : 'Transfer Dana',
    description: 'Pindahkan dana antar rekening perusahaan.',
    icon: ArrowRightLeft,
    size: 'full',
  },
  account: {
    title: props.item ? 'Edit Rekening' : 'Tambah Rekening',
    description: 'Tambahkan kas, bank, atau dompet digital.',
    icon: Landmark,
    size: 'xl',
  },
}
const config = computed(() => configs[props.type])
const form = reactive({
  account: props.item?.cash_bank_account_id ?? '',
  category: props.item?.category ?? '',
  amount: props.item ? Math.abs(props.item.amount) : '',
  date: props.item?.isoDate ?? new Date().toISOString().slice(0, 10),
  reference: props.item?.reference ?? '',
  note: '',
  source: props.item?.source === 'Transfer' ? props.item.account : '',
  destination: props.item?.source === 'Transfer' ? props.item.party : '',
  name: props.item?.name ?? '',
  accountType:
    props.item?.account_type === 'cash'
      ? 'Kas'
      : props.item?.account_type === 'e_wallet'
        ? 'E-Wallet'
        : 'Bank',
  bank: props.item?.bank ?? '',
  number: props.item?.number ?? '',
  holder: props.item?.holder ?? '',
  currency: props.item?.currency ?? 'IDR',
  openingBalance: props.item?.opening_balance ?? 0,
  status: props.item?.status ?? 'Aktif',
})
const required = computed(() =>
  props.type === 'transfer'
    ? [form.source, form.destination, form.amount, form.date]
    : props.type === 'account'
      ? [form.name, form.accountType, form.currency, form.status]
      : [form.account, form.category, form.amount, form.date]
)
const valid = computed(
  () =>
    required.value.every(Boolean) && (props.type !== 'transfer' || form.source !== form.destination)
)
const accountOptions = computed(() =>
  props.accounts.map((account) => ({
    id: account.id,
    text: [account.name, account.bank].filter(Boolean).join(' - '),
    currency: account.currency,
  }))
)
const selectedAccountCurrency = computed(
  () => props.accounts.find((account) => account.id === form.account)?.currency ?? 'IDR'
)
const submit = () => {
  submitted.value = true
  if (!valid.value) return
  if (props.type === 'in' || props.type === 'out') {
    if (!form.account) return
    useForm({
      cash_bank_account_id: form.account,
      type: props.type,
      category: form.category,
      amount: Number(form.amount),
      transaction_date: form.date,
      reference: form.reference || null,
      note: form.note || null,
    }).post(route('cash-bank.transactions.store'), {
      preserveScroll: true,
      onSuccess: () => emit('saved'),
    })
    return
  }
  if (props.type !== 'account') {
    emit('saved')
    return
  }
  const accountType = { Kas: 'cash', Bank: 'bank', 'E-Wallet': 'e_wallet' }[form.accountType]
  const payload = {
    name: form.name,
    type: accountType,
    bank_name: form.bank || null,
    account_number: form.number || null,
    account_holder: form.holder || null,
    currency: form.currency,
    opening_balance: Number(form.openingBalance || 0),
    is_active: form.status === 'Aktif',
  }
  useForm(payload).submit(
    props.item ? 'put' : 'post',
    props.item
      ? route('cash-bank.accounts.update', props.item.id)
      : route('cash-bank.accounts.store'),
    { preserveScroll: true, onSuccess: () => emit('saved') }
  )
}
const select =
  'mt-1.5 w-full rounded-xl border-slate-300 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white dark:placeholder:text-slate-500'
const money = (v) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(v) || 0)
</script>
<template>
  <Modal
    :model-value="true"
    :title="config.title"
    :description="config.description"
    :size="config.size"
    @update:model-value="emit('close')"
    ><div
      class="mb-5 flex gap-3 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300"
    >
      <component :is="config.icon" :size="19" />Data akan disimpan ke database perusahaan.
    </div>
    <form id="cash-form" class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
      <template v-if="type === 'in' || type === 'out'"
        ><AsyncSelect
          v-model="form.account"
          label="Rekening"
          :endpoint="route('cash-bank.accounts.search')"
          :initial-options="accountOptions"
          placeholder="Cari dan pilih rekening..."
          required />
        <label
          ><span class="text-sm font-medium text-slate-700 dark:text-slate-200">Kategori *</span
          ><select v-model="form.category" :class="select">
            <option value="">Pilih kategori</option>
            <option v-if="type === 'in'">Pendapatan Lain</option>
            <option v-if="type === 'in'">Penjualan Tunai</option>
            <option v-if="type === 'in'">Setoran Modal</option>
            <option v-if="type === 'out'">Operasional</option>
            <option v-if="type === 'out'">Utilitas</option>
            <option v-if="type === 'out'">Biaya Lainnya</option>
          </select></label
        ><CurrencyInput
          v-model="form.amount"
          label="Nominal"
          :currency="selectedAccountCurrency"
          placeholder="Contoh: 1500000"
          required /><DatePicker
          v-model="form.date"
          label="Tanggal"
          placeholder="Pilih tanggal transaksi"
          required /><Input
          v-model="form.reference"
          label="Referensi"
          placeholder="Nomor dokumen (opsional)" /><label
          ><span class="text-sm font-medium text-slate-700 dark:text-slate-200">Catatan</span
          ><textarea
            v-model="form.note"
            rows="3"
            :class="select"
            placeholder="Contoh: Pembayaran biaya operasional bulan ini"
          ></textarea></label></template
      ><template v-else-if="type === 'transfer'"
        ><label
          ><span class="text-sm font-medium text-slate-700 dark:text-slate-200"
            >Rekening Asal *</span
          ><select v-model="form.source" :class="select">
            <option value="">Pilih rekening asal</option>
            <option>Bank BCA Operasional</option>
            <option>Kas Utama</option>
          </select></label
        ><label
          ><span class="text-sm font-medium text-slate-700 dark:text-slate-200"
            >Rekening Tujuan *</span
          ><select v-model="form.destination" :class="select">
            <option value="">Pilih rekening tujuan</option>
            <option>Bank Mandiri Payroll</option>
            <option>Bank BCA Operasional</option>
          </select></label
        ><Input v-model="form.amount" type="number" min="1" label="Nominal" required /><Input
          v-model="form.date"
          label="Tanggal"
          required
        /><Input v-model="form.reference" label="Referensi" /><label
          ><span class="text-sm font-medium text-slate-700 dark:text-slate-200">Catatan</span
          ><textarea v-model="form.note" rows="3" :class="select"></textarea>
        </label>
        <div class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33] sm:col-span-2">
          <p class="text-xs font-medium uppercase text-slate-400">Ringkasan Transfer</p>
          <div class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
            <div>
              <span class="text-slate-500 dark:text-slate-400">Dari</span
              ><strong class="mt-1 block text-slate-900 dark:text-white">{{
                form.source || '-'
              }}</strong>
            </div>
            <div>
              <span class="text-slate-500 dark:text-slate-400">Ke</span
              ><strong class="mt-1 block text-slate-900 dark:text-white">{{
                form.destination || '-'
              }}</strong>
            </div>
            <div>
              <span class="text-slate-500 dark:text-slate-400">Nominal</span
              ><strong class="mt-1 block text-emerald-600">{{ money(form.amount) }}</strong>
            </div>
          </div>
        </div></template
      ><template v-else
        ><Input
          v-model="form.name"
          label="Nama Rekening"
          placeholder="Contoh: Kas Operasional Ahmadi"
          required
        /><label
          ><span class="text-sm font-medium text-slate-700 dark:text-slate-200"
            >Jenis Rekening *</span
          ><select v-model="form.accountType" :class="select">
            <option>Kas</option>
            <option>Bank</option>
            <option>E-Wallet</option>
            <option>Virtual Account</option>
            <option>Lainnya</option>
          </select></label
        ><Input
          v-if="form.accountType === 'Bank'"
          v-model="form.bank"
          label="Nama Bank"
          placeholder="Contoh: Bank BCA"
        /><Input
          v-model="form.number"
          label="Nomor Rekening"
          placeholder="Contoh: 1234567890"
        /><Input v-model="form.holder" label="Atas Nama" placeholder="Contoh: Ahmadi" /><label
          ><span class="text-sm font-medium text-slate-700 dark:text-slate-200">Mata Uang *</span
          ><select v-model="form.currency" :class="select">
            <option>IDR</option>
            <option>USD</option>
          </select></label
        ><CurrencyInput
          v-model="form.openingBalance"
          :currency="form.currency"
          label="Saldo Awal"
          placeholder="Contoh: 5000000"
        /><label
          ><span class="text-sm font-medium text-slate-700 dark:text-slate-200">Status *</span
          ><select v-model="form.status" :class="select">
            <option>Aktif</option>
            <option>Nonaktif</option>
          </select></label
        ></template
      >
      <p
        v-if="submitted && !valid"
        class="rounded-xl bg-red-50 p-3 text-sm font-medium text-red-600 dark:bg-red-400/10 dark:text-red-300 sm:col-span-2"
      >
        Lengkapi semua field wajib dengan data yang valid.
      </p>
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="cash-form">{{
        type === 'transfer' ? 'Transfer Dana' : 'Simpan'
      }}</Button></template
    ></Modal
  >
</template>
