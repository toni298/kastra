<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { ShoppingCart } from 'lucide-vue-next'
import Modal from '@/Components/UI/Modal.vue'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import SupplierModal from './SupplierModal.vue'
import PurchaseItemPicker from './PurchaseItemPicker.vue'
import PurchaseTransactionSummary from './PurchaseTransactionSummary.vue'
const props = defineProps({
  transaction: { type: Object, default: null },
  warehouses: { type: Array, default: () => [] },
  branches: { type: Array, default: () => [] },
  organizationMode: { type: String, default: 'branch' },
})
const emit = defineEmits(['close', 'saved'])
const supplierModal = ref(false)
const supplierSelect = ref(null)
const isBranchMode = computed(() => props.organizationMode === 'branch')
const stockLocations = computed(() => (isBranchMode.value ? props.branches : props.warehouses))
const singleLocation = computed(() =>
  stockLocations.value.length === 1 ? stockLocations.value[0] : null
)

const documentTypeMap = {
  'Draft Invoice': 'Purchase Invoice',
  'Draft Purchase Order': 'Purchase Order',
}
const items = ref(
  (props.transaction?.items ?? []).map((item, index) => ({
    ...item,
    id: item.id ?? `existing-${index}`,
    discount: item.discount ?? 0,
  }))
)
const grandTotalRef = ref(0)
const autoStockLocationId =
  props.transaction?.gudang_id ?? (singleLocation.value ? singleLocation.value.id : '')
const form = useForm({
  supplier_id: props.transaction?.supplier_id ?? '',
  gudang_id: isBranchMode.value ? '' : autoStockLocationId,
  branch_id: isBranchMode.value ? autoStockLocationId : '',
  documentType:
    documentTypeMap[props.transaction?.type] ?? props.transaction?.type ?? 'Purchase Invoice',
  date: props.transaction?.date_iso ?? new Date().toISOString().slice(0, 10),
  due_date: props.transaction?.due_date_iso ?? '',
  note: '',
})
const editing = computed(() => Boolean(props.transaction))
const supplierEndpoint = route('search.suppliers')
const selectClass =
  'mt-1.5 w-full rounded-xl border-slate-300 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white'
const add = (item) => {
  const found = items.value.find((entry) => entry.id === item.id)
  if (found) found.qty += 1
  else items.value.push({ ...item, qty: 1, discount: 0 })
}
const update = ({ id, key, value }) => {
  const item = items.value.find((entry) => entry.id === id)
  if (item) item[key] = value
}
const remove = (id) => {
  items.value = items.value.filter((item) => item.id !== id)
}
const finish = (mode, payment = {}) => {
  const totalPayment = Number(payment.paymentAmount || 0) + Number(payment.ownerAmount || 0)
  const isPaid = mode !== 'draft' && totalPayment >= grandTotalRef.value
  form
    .transform(() => ({
      supplier_id: form.supplier_id || null,
      gudang_id: isBranchMode.value ? null : form.gudang_id || null,
      branch_id: isBranchMode.value ? form.branch_id || null : null,
      document_type: form.documentType === 'Purchase Order' ? 'purchase_order' : 'purchase_invoice',
      status: mode === 'draft' ? 'draft' : 'completed',
      transaction_date: form.date,
      due_date: isPaid ? null : form.due_date || null,
      payment_method: payment.paymentMethod || null,
      payment_amount: Number(payment.paymentAmount || 0),
      owner_amount: Number(payment.ownerAmount || 0),
      proof_file: payment.proofFile || null,
      details: items.value.map((item) => ({
        product_id: item.id,
        quantity: Number(item.qty),
        unit_price: Number(item.price),
        discount: Number(item.discount || 0),
      })),
      note: form.note,
    }))
    .submit(
      editing.value ? 'put' : 'post',
      editing.value
        ? route('purchases.transactions.update', props.transaction.id)
        : route('purchases.transactions.store'),
      {
        preserveScroll: true,
        onSuccess: () => emit('saved'),
        onError: () => {
          form.processing = false
        },
      }
    )
}
</script>
<template>
  <Modal
    :model-value="true"
    :title="transaction ? 'Edit Pembelian' : 'Pembelian Baru'"
    description="Buat transaksi produk atau jasa tanpa berpindah halaman."
    size="screen"
    :close-on-overlay="false"
    @update:model-value="emit('close')"
  >
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.55fr)_minmax(360px,.75fr)]">
      <main class="space-y-6">
        <section>
          <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2">
              <span
                class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"
                ><ShoppingCart :size="18"
              /></span>
              <div>
                <h2 class="font-semibold text-slate-950 dark:text-white">Informasi Pembelian</h2>
                <p class="text-xs text-slate-500">
                  Supplier kosong akan disimpan sebagai Pembelian Umum.
                </p>
              </div>
            </div>
            <DatePicker v-model="form.date" label="Tanggal" required />
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <AsyncSelect
              ref="supplierSelect"
              v-model="form.supplier_id"
              label="Supplier (Opsional)"
              :endpoint="supplierEndpoint"
              placeholder="Cari supplier..."
              addable
              add-label="Tambah Supplier"
              @add="supplierModal = true"
            />
            <label
              ><span class="text-sm font-medium">Jenis Dokumen</span
              ><select v-model="form.documentType" :class="selectClass">
                <option>Purchase Invoice</option>
                <option>Pembelian Jasa</option>
                <option>Purchase Order</option>
              </select></label
            >
            <SelectInput
              v-if="!singleLocation"
              :model-value="isBranchMode ? form.branch_id : form.gudang_id"
              :label="isBranchMode ? 'Cabang' : 'Gudang'"
              required
              aria-label="Lokasi stok"
              @update:model-value="
                isBranchMode ? (form.branch_id = $event) : (form.gudang_id = $event)
              "
            >
              <option value="">Pilih {{ isBranchMode ? 'cabang' : 'gudang' }}</option>
              <option v-for="loc in stockLocations" :key="loc.id" :value="loc.id">
                {{ loc.nama || loc.name }}
              </option>
            </SelectInput>
            <label class="sm:col-span-2"
              ><span class="text-sm font-medium">Catatan (Opsional)</span
              ><input v-model="form.note" :class="selectClass" placeholder="Catatan transaksi"
            /></label>
          </div>
        </section>
        <section>
          <h2 class="mb-3 font-semibold text-slate-950 dark:text-white">Cari Produk / Jasa</h2>
          <PurchaseItemPicker
            :gudang-id="isBranchMode ? '' : form.gudang_id"
            :branch-id="isBranchMode ? form.branch_id : ''"
            @add="add"
          />
        </section>
      </main>
      <div
        class="border-t border-slate-200 pt-6 dark:border-[#29476b] lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0"
      >
        <PurchaseTransactionSummary
          :items="items"
          :due-date="form.due_date"
          :transaction-date="form.date"
          @update="update"
          @update:due-date="form.due_date = $event"
          @update:grand-total="grandTotalRef = $event"
          @remove="remove"
          @cancel="emit('close')"
          @draft="finish('draft')"
          @save="finish('save', $event)"
        />
      </div>
    </div>
    <SupplierModal
      v-if="supplierModal"
      @close="supplierModal = false"
      @saved="
        (supplier) => {
          supplierModal = false
          if (supplier?.id) {
            supplierSelect?.addItems({ id: supplier.id, text: supplier.name })
            form.supplier_id = supplier.id
          }
        }
      "
    />
  </Modal>
</template>
