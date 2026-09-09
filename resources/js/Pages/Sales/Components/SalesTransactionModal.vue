<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { ShoppingCart } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import SalesItemPicker from './SalesItemPicker.vue'
import SalesTransactionSummary from './SalesTransactionSummary.vue'
import CustomerModal from './CustomerModal.vue'

const props = defineProps({
  branches: { type: Array, default: () => [] },
  item: { type: Object, default: null },
})
const emit = defineEmits(['close', 'saved'])
const items = ref([])
const approveOpen = ref(false)
const customerModal = ref(false)
const customerSelect = ref(null)
const unpaidReason = ref('')
const proofFile = ref(null)
const singleBranch = computed(() => (props.branches.length === 1 ? props.branches[0] : null))
const form = reactive({
  branch_id: '',
  customer_id: '',
  documentType: 'Invoice Produk',
  date: new Date().toISOString().slice(0, 10),
  due_date: '',
  note: '',
  discount: 0,
  discount_type: 'amount',
  tax: 11,
  payment_method: 'cash',
  payment_amount: 0,
  payment_reference: '',
  payment_note: '',
})
if (props.item) {
  form.branch_id = props.item.branch_id
  form.customer_id = props.item.customer_id ?? ''
  form.documentType =
    props.item.document_type === 'quotation'
      ? 'Quotation'
      : props.item.document_type === 'sales_order'
        ? 'Sales Order'
        : 'Invoice Produk'
  form.date = props.item.date_iso ?? new Date().toISOString().slice(0, 10)
  form.due_date = props.item.due_date_iso ?? ''
  form.note = props.item.note ?? ''
  form.discount = props.item.discount ?? 0
  form.tax = props.item.tax_rate ?? 0
  form.payment_method = props.item.payment?.method ?? 'cash'
  form.payment_amount = props.item.payment?.amount ?? 0
  items.value = (props.item.details ?? []).map((detail) => ({
    id: detail.product_id,
    name: detail.product,
    sku: detail.sku,
    unit: detail.unit,
    price: detail.unit_price,
    qty: detail.quantity,
    available_quantity: Number(detail.available_quantity ?? detail.quantity),
  }))
} else if (singleBranch.value) {
  form.branch_id = singleBranch.value.id
}
const submitForm = useForm({})
const editing = computed(() => Boolean(props.item))
watch(
  () => form.branch_id,
  (branchId, previousBranchId) => {
    if (!previousBranchId || branchId === previousBranchId) return
    form.customer_id = ''
    form.note = ''
    form.documentType = 'Invoice Produk'
    form.date = new Date().toISOString().slice(0, 10)
    form.due_date = ''
    form.discount = 0
    form.discount_type = 'amount'
    form.tax = 11
    form.payment_method = 'cash'
    form.payment_amount = 0
    form.payment_reference = ''
    form.payment_note = ''
    proofFile.value = null
    items.value = []
    customerSelect.value?.clearItems()
  }
)
const customerEndpoint = computed(
  () => `${route('search.customers')}?branch_id=${encodeURIComponent(form.branch_id)}`
)
const total = computed(() => {
  const subtotal = items.value.reduce((sum, item) => sum + item.price * item.qty, 0)
  const discount =
    form.discount_type === 'percent'
      ? Math.round((subtotal * form.discount) / 100)
      : Number(form.discount || 0)
  return Math.max(0, subtotal - discount + Math.round((subtotal * form.tax) / 100))
})
const branchName = computed(
  () =>
    props.branches.find((branch) => String(branch.id) === String(form.branch_id))?.name ??
    props.item?.branch ??
    '-'
)
const customerName = computed(() => props.item?.customer ?? 'Penjualan Umum')
const documentLabel = computed(() => form.documentType)
const add = (item) => {
  const available = Number(item.available_quantity ?? 0)
  const found = items.value.find((entry) => entry.id === item.id)
  if (found) found.qty = Math.min(found.qty + 1, available)
  else
    items.value.push({
      ...item,
      available_quantity: available,
      qty: Math.min(1, available),
      discount: 0,
    })
}
const update = ({ id, key, value }) => {
  const item = items.value.find((entry) => entry.id === id)
  if (item) item[key] = value
}
const remove = (id) => {
  items.value = items.value.filter((item) => item.id !== id)
}
const send = (status) => {
  const subtotal = items.value.reduce((sum, item) => sum + item.price * item.qty, 0)
  const isPaid = Number(form.payment_amount) >= total.value
  submitForm
    .transform(() => ({
      branch_id: form.branch_id,
      customer_id: form.customer_id || null,
      document_type:
        form.documentType === 'Invoice Produk'
          ? 'invoice'
          : form.documentType === 'Quotation'
            ? 'quotation'
            : 'sales_order',
      transaction_date: form.date,
      due_date: isPaid ? null : form.due_date || null,
      payment_status: isPaid ? 'paid' : 'unpaid',
      status,
      discount:
        form.discount_type === 'percent'
          ? Math.round((subtotal * form.discount) / 100)
          : Number(form.discount || 0),
      tax: Math.round((subtotal * form.tax) / 100),
      payment_method: form.payment_method,
      payment_amount: Number(form.payment_amount || 0),
      payment_reference: form.payment_reference || null,
      payment_note: form.payment_note || unpaidReason.value || null,
      proof_file: proofFile.value || null,
      note: form.note || null,
      details: items.value.map((item) => ({
        product_id: item.id,
        quantity: item.qty,
        unit_price: item.price,
      })),
    }))
    [editing.value ? 'put' : 'post'](
      editing.value
        ? route('sales.transactions.update', props.item.id)
        : route('sales.transactions.store'),
      { onSuccess: () => emit('saved') }
    )
}
const submit = (status = 'completed') => {
  if (!form.branch_id || !items.value.length) return
  if (status === 'completed' && Number(form.payment_amount) < total.value && !unpaidReason.value) {
    approveOpen.value = true
    return
  }
  send(status)
}
const finish = () => submit('completed')
const saveDraft = () => submit('draft')
const approveUnpaid = () => {
  if (unpaidReason.value.trim()) {
    approveOpen.value = false
    send('completed')
  }
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="editing ? 'Edit Penjualan' : 'Penjualan Baru'"
    :description="
      editing
        ? 'Atur produk, ringkasan, dan pembayaran transaksi.'
        : 'Buat transaksi penjualan berdasarkan cabang.'
    "
    size="screen"
    :close-on-overlay="false"
    @update:model-value="emit('close')"
  >
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.55fr)_minmax(360px,.75fr)]">
      <main class="space-y-6">
        <section>
          <div class="mb-4 flex items-center gap-2">
            <span
              class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"
              ><ShoppingCart :size="18"
            /></span>
            <div>
              <h2 class="font-semibold text-slate-950 dark:text-white">Informasi Penjualan</h2>
              <p class="text-xs text-slate-500">Pilih cabang untuk menampilkan stok produk.</p>
            </div>
          </div>
          <div v-if="editing" class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl bg-slate-50 p-3 dark:bg-[#0a1b33]">
              <p class="text-xs text-slate-400">Cabang</p>
              <p class="mt-1 font-semibold dark:text-white">{{ branchName }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3 dark:bg-[#0a1b33]">
              <p class="text-xs text-slate-400">Pelanggan</p>
              <p class="mt-1 font-semibold dark:text-white">{{ customerName }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3 dark:bg-[#0a1b33]">
              <p class="text-xs text-slate-400">Tanggal</p>
              <p class="mt-1 font-semibold dark:text-white">{{ props.item?.date }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3 dark:bg-[#0a1b33]">
              <p class="text-xs text-slate-400">Jenis Dokumen</p>
              <p class="mt-1 font-semibold dark:text-white">{{ documentLabel }}</p>
            </div>
            <div class="sm:col-span-2">
              <Input
                v-model="form.note"
                label="Catatan"
                placeholder="Catatan transaksi (opsional)"
              />
            </div>
          </div>
          <div v-else class="grid gap-4 sm:grid-cols-2">
            <SelectInput
              v-if="!singleBranch"
              v-model="form.branch_id"
              label="Cabang"
              required
              aria-label="Cabang transaksi"
              ><option value="">Pilih cabang</option>
              <option v-for="branch in props.branches" :key="branch.id" :value="branch.id">
                {{ branch.name }}
              </option></SelectInput
            ><AsyncSelect
              ref="customerSelect"
              v-model="form.customer_id"
              label="Pelanggan"
              :endpoint="customerEndpoint"
              :disabled="!form.branch_id"
              placeholder="Cari pelanggan..."
              addable
              add-label="Tambah Pelanggan"
              @add="customerModal = true"
            /><DatePicker v-model="form.date" label="Tanggal" required /><SelectInput
              v-model="form.documentType"
              label="Jenis Dokumen"
              required
              aria-label="Jenis dokumen"
              ><option>Invoice Produk</option>
              <option>Quotation</option>
              <option>Sales Order</option></SelectInput
            ><Input
              v-model="form.note"
              label="Catatan"
              placeholder="Catatan transaksi (opsional)"
            />
          </div>
        </section>
        <section>
          <h2 class="mb-3 font-semibold text-slate-950 dark:text-white">Cari Produk Cabang</h2>
          <SalesItemPicker :branch-id="form.branch_id" @add="add" />
        </section>
      </main>
      <div
        class="border-t border-slate-200 pt-6 dark:border-[#29476b] lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0"
      >
        <SalesTransactionSummary
          :items="items"
          :discount="form.discount"
          :discount-type="form.discount_type"
          :tax-rate="form.tax"
          :payment-amount="form.payment_amount"
          :payment-method="form.payment_method"
          :due-date="form.due_date"
          :transaction-date="form.date"
          :editing="editing"
          :proof-file="proofFile"
          @update:discount="form.discount = $event"
          @update:discount-type="form.discount_type = $event"
          @update:tax-rate="form.tax = $event"
          @update:payment-amount="form.payment_amount = $event"
          @update:payment-method="form.payment_method = $event"
          @update:due-date="form.due_date = $event"
          @update:proof-file="proofFile = $event"
          @update="update"
          @remove="remove"
          @cancel="emit('close')"
          @draft="saveDraft"
          @save="finish"
        />
      </div>
    </div>
    <Modal
      v-if="approveOpen"
      :model-value="true"
      title="Konfirmasi Belum Lunas"
      description="Transaksi belum lunas. Masukkan alasan sebelum menyimpan."
      size="md"
      @update:model-value="approveOpen = false"
      ><Input
        v-model="unpaidReason"
        label="Alasan"
        placeholder="Contoh: Pembayaran termin"
        required
      /><template #footer
        ><Button variant="secondary" @click="approveOpen = false">Batal</Button
        ><Button :disabled="!unpaidReason.trim()" @click="approveUnpaid"
          >Setujui & Simpan</Button
        ></template
      ></Modal
    >
    <CustomerModal
      v-if="customerModal"
      :branch-id="form.branch_id"
      @close="customerModal = false"
      @saved="
        (customer) => {
          customerModal = false
          if (customer?.id) {
            customerSelect?.addItems({ id: customer.id, text: customer.name })
            form.customer_id = customer.id
          }
        }
      "
    />
  </Modal>
</template>
