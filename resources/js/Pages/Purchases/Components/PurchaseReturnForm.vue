<script setup>
import { computed, reactive, ref, watch } from 'vue'
import axios from 'axios'
import { Undo2 } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import PurchaseWorkflowItemQuantities from './PurchaseWorkflowItemQuantities.vue'

const props = defineProps({
  transaction: { type: Object, required: true },
})

const emit = defineEmits(['cancel', 'submit'])

const form = reactive({
  returnDate: props.transaction.date_iso ?? props.transaction.date,
  reason: '',
  resolution: '',
  refund_account_id: '',
  note: '',
})
const items = ref(
  props.transaction.items.map((item) => ({
    product_id: item.product_id ?? item.id,
    name: item.name,
    unit: item.unit,
    orderedQty: item.qty,
    quantity: 0,
  }))
)
const submitted = ref(false)
const refundAccounts = ref([])
const loadingAccounts = ref(false)
const showAccountPicker = ref(false)

const fetchRefundAccounts = async () => {
  if (form.resolution !== 'refund') {
    showAccountPicker.value = false
    form.refund_account_id = ''
    return
  }
  loadingAccounts.value = true
  try {
    const { data } = await axios.get(
      route('purchases.transactions.refund-accounts', props.transaction.id)
    )
    refundAccounts.value = data.accounts || []
    showAccountPicker.value = data.multiple
    if (!data.multiple && refundAccounts.value.length === 1) {
      form.refund_account_id = refundAccounts.value[0].id
    }
  } catch {
    refundAccounts.value = []
  } finally {
    loadingAccounts.value = false
  }
}

watch(() => form.resolution, fetchRefundAccounts, { immediate: false })

const valid = computed(
  () =>
    Boolean(form.returnDate && form.reason && form.resolution) &&
    (form.resolution !== 'refund' || Boolean(form.refund_account_id)) &&
    items.value.some((item) => item.quantity > 0) &&
    items.value.every(
      (item) =>
        Number.isInteger(item.quantity) && item.quantity >= 0 && item.quantity <= item.orderedQty
    )
)

const updateQuantity = ({ index, value }) => {
  items.value[index].quantity = value
}
const submit = () => {
  submitted.value = true
  if (!valid.value) return

  emit('submit', {
    type: 'return-goods',
    data: {
      ...form,
      items: items.value.filter((item) => item.quantity > 0),
    },
  })
}
</script>

<template>
  <form id="purchase-return-form" class="space-y-5" @submit.prevent="submit">
    <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-[#29476b]">
      <header
        class="flex items-start gap-3 border-b border-slate-100 px-5 py-4 dark:border-[#29476b]"
      >
        <Undo2 :size="19" class="mt-0.5 text-emerald-600" />
        <div>
          <h3 class="font-semibold text-slate-900 dark:text-white">Informasi Retur</h3>
          <p class="mt-0.5 text-sm text-slate-500">Referensi {{ transaction.number }}</p>
        </div>
      </header>
      <div class="grid gap-4 p-5 sm:grid-cols-2">
        <DatePicker v-model="form.returnDate" label="Tanggal Retur" required />
        <label>
          <span class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
            Alasan Retur <span class="text-red-500">*</span>
          </span>
          <SelectInput v-model="form.reason" class="w-full" aria-label="Alasan retur">
            <option value="">Pilih alasan</option>
            <option value="rusak">Barang Rusak</option>
            <option value="tidak_sesuai">Barang Tidak Sesuai</option>
            <option value="jumlah_lebih">Jumlah Berlebih</option>
            <option value="kedaluwarsa">Kedaluwarsa</option>
          </SelectInput>
        </label>
        <label>
          <span class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
            Penyelesaian <span class="text-red-500">*</span>
          </span>
          <SelectInput v-model="form.resolution" class="w-full" aria-label="Penyelesaian retur">
            <option value="">Pilih penyelesaian</option>
            <option value="penggantian">Penggantian Barang</option>
            <option value="potong_tagihan">Potong Tagihan</option>
            <option value="refund">Pengembalian Dana</option>
          </SelectInput>
        </label>
        <label v-if="form.resolution === 'refund' && showAccountPicker">
          <span class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
            Rekening Penerima <span class="text-red-500">*</span>
          </span>
          <SelectInput
            v-model="form.refund_account_id"
            class="w-full"
            aria-label="Rekening penerima refund"
            :disabled="loadingAccounts"
          >
            <option value="">{{ loadingAccounts ? 'Memuat...' : 'Pilih rekening' }}</option>
            <option v-for="account in refundAccounts" :key="account.id" :value="account.id">
              {{ account.label }}
            </option>
          </SelectInput>
        </label>
        <label>
          <span class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
            Catatan
          </span>
          <textarea
            v-model="form.note"
            rows="3"
            class="w-full rounded-xl border-slate-300 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
            placeholder="Kondisi barang atau catatan retur"
          ></textarea>
        </label>
      </div>
    </section>

    <PurchaseWorkflowItemQuantities
      :items="items"
      label="Qty Retur"
      allow-zero
      @update="updateQuantity"
    />

    <p
      v-if="submitted && !valid"
      class="rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600 dark:bg-red-400/10 dark:text-red-300"
    >
      Lengkapi informasi retur dan pilih minimal satu barang.
    </p>

    <div
      class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-4 dark:border-[#29476b]"
    >
      <Button variant="secondary" @click="emit('cancel')">Batal</Button>
      <Button type="submit">Simpan Retur</Button>
    </div>
  </form>
</template>
