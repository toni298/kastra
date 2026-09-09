<script setup>
import { computed, reactive, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import SalesReturnReplacementSection from './SalesReturnReplacementSection.vue'
import SalesReturnSummary from './SalesReturnSummary.vue'
import { useReturnSettlement } from '@/Composables/useReturnSettlement'

const props = defineProps({
  transaction: { type: Object, required: true },
  returnDraft: { type: Object, default: null },
})
const emit = defineEmits(['close', 'saved'])

const sourceItems = props.returnDraft?.details ?? props.transaction.details ?? []
const items = reactive(
  sourceItems.map((item) => ({
    ...item,
    selected: Boolean(props.returnDraft),
    returnable_quantity: Number(item.returnable_quantity ?? item.quantity),
    return_quantity: props.returnDraft ? Number(item.quantity ?? 0) : 0,
  }))
)

const branchId = props.transaction.branch_id ?? props.returnDraft?.branch_id
const resolution = ref(props.returnDraft?.resolution ?? 'refund')
const settlement = ref('refund')
const replacements = ref(
  (props.returnDraft?.replacements ?? []).map((r) => ({
    product_id: r.product_id,
    product: r.product,
    unit: r.unit,
    quantity: Number(r.quantity),
    unit_price: Number(r.unit_price),
  }))
)

const form = useForm({
  status: 'draft',
  reason: props.returnDraft?.reason ?? '',
  note: props.returnDraft?.note ?? '',
  resolution: resolution.value,
  settlement: 'refund',
  items: [],
  replacements: [],
})

const itemsRef = computed(() => items)
const {
  totalReturnValue,
  replacementTotal,
  difference,
  refundAmount,
  customerCreditAmount,
  customerPaysAmount,
  requiresCustomer,
} = useReturnSettlement(itemsRef, resolution, replacements, settlement)

const normalizeReturnQuantity = (item) => {
  const max = Number(item.returnable_quantity ?? 0)
  const rawValue = Number(item.return_quantity ?? 0)

  if (!Number.isFinite(rawValue) || rawValue <= 0) {
    item.return_quantity = 0
    return
  }

  item.return_quantity = Math.min(Math.max(rawValue, 0), max)
}

const selectedItems = computed(() =>
  items
    .filter((item) => item.selected)
    .map((item) => {
      normalizeReturnQuantity(item)
      return item.return_quantity > 0 ? item : null
    })
    .filter(Boolean)
)
const totalQty = computed(() =>
  selectedItems.value.reduce((sum, item) => sum + Number(item.return_quantity), 0)
)

const clampReturnQuantity = (item, rawValue) => {
  const max = Number(item.returnable_quantity ?? 0)
  const nextValue = Number(rawValue ?? 0)

  if (!Number.isFinite(nextValue) || nextValue <= 0) {
    item.return_quantity = 0
    return
  }

  item.return_quantity = Math.min(Math.max(nextValue, 0), max)
  if (Number.isFinite(item.return_quantity) && item.return_quantity >= 0) {
    if (Number(rawValue) !== item.return_quantity && rawValue !== '') {
      item.return_quantity = Math.min(nextValue, max)
    }
  }
}

const toggle = (item) => {
  if (!item.selected) {
    item.return_quantity = 0
    return
  }

  normalizeReturnQuantity(item)
}

const handleReturnQtyInput = (item, event) => {
  const nextValue = event?.target?.value
  clampReturnQuantity(item, nextValue)

  if (event?.target) {
    event.target.value = String(item.return_quantity)
  }
}

const addReplacement = (item) => {
  replacements.value.push(item)
}
const removeReplacement = (index) => {
  replacements.value.splice(index, 1)
}

const canComplete = computed(() => {
  if (!form.reason || !selectedItems.value.length) return false
  if (resolution.value === 'ganti_produk' && replacements.value.length === 0) return false
  if (requiresCustomer.value && !props.transaction.customer_id) return false
  return true
})

const submit = (status) => {
  form.status = status
  form.resolution = resolution.value
  form.settlement = settlement.value
  selectedItems.value.forEach((item) => {
    normalizeReturnQuantity(item)
  })

  form.items = selectedItems.value.map((item) => ({
    product_id: item.product_id,
    quantity: Math.min(Number(item.return_quantity), Number(item.returnable_quantity || 0)),
  }))
  form.replacements =
    resolution.value === 'ganti_produk'
      ? replacements.value.map((item) => ({
          product_id: item.product_id,
          quantity: Number(item.quantity),
          unit_price: Number(item.unit_price),
        }))
      : []
  const endpoint = props.returnDraft
    ? route('sales.returns.complete', props.returnDraft.id)
    : route('sales.transactions.returns.store', props.transaction.id)
  form.post(endpoint, { onSuccess: () => emit('saved') })
}
</script>

<template>
  <Modal
    :model-value="true"
    title="Retur Penjualan"
    description="Pilih produk dan tentukan penyelesaian retur."
    size="return"
    @update:model-value="emit('close')"
  >
    <div class="space-y-5">
      <section>
        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
          Informasi Retur
        </h3>
        <div
          class="grid grid-cols-2 gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <div>
            <p class="text-xs text-slate-400">Nomor Transaksi</p>
            <p class="mt-1 font-semibold dark:text-white">
              {{ transaction.number ?? returnDraft?.reference_number ?? '-' }}
            </p>
          </div>
          <div>
            <p class="text-xs text-slate-400">Pelanggan</p>
            <p class="mt-1 font-semibold dark:text-white">
              {{ transaction.customer ?? returnDraft?.customer ?? 'Penjualan Umum' }}
            </p>
          </div>
        </div>
      </section>

      <section>
        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
          Produk Retur
        </h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-[#29476b]">
          <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-[#0a1b33]">
              <tr>
                <th class="w-12 px-3 py-2 text-left">Retur</th>
                <th class="px-3 py-2 text-left">Produk</th>
                <th class="px-3 py-2 text-right">Sudah Retur</th>
                <th class="px-3 py-2 text-right">Maks. Retur</th>
                <th class="px-3 py-2 text-right">Qty Retur</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-[#29476b]">
              <tr
                v-for="item in items"
                :key="item.product_id"
                :class="item.returnable_quantity <= 0 ? 'opacity-50' : ''"
              >
                <td class="px-3 py-2">
                  <input
                    v-model="item.selected"
                    type="checkbox"
                    :disabled="item.returnable_quantity <= 0"
                    @change="toggle(item)"
                  />
                </td>
                <td class="px-3 py-2 font-medium dark:text-white">
                  <p>{{ item.product }}</p>
                  <p class="mt-0.5 text-xs font-normal text-slate-500">
                    {{ item.quantity }} {{ item.unit ?? 'Unit' }}
                  </p>
                </td>
                <td class="px-3 py-2 text-right text-slate-500">
                  {{ item.returned_quantity ?? 0 }}
                </td>
                <td class="px-3 py-2 text-right font-semibold">{{ item.returnable_quantity }}</td>
                <td class="px-3 py-2 text-right">
                  <input
                    :value="item.return_quantity"
                    type="number"
                    min="0"
                    :max="item.returnable_quantity"
                    :disabled="!item.selected || item.returnable_quantity <= 0"
                    class="w-20 rounded-lg border-slate-300 py-1.5 text-right dark:border-[#29476b] dark:bg-[#0a1b33]"
                    @input="handleReturnQtyInput(item, $event)"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="grid gap-4 sm:grid-cols-2">
        <SelectInput v-model="form.reason" label="Alasan Retur" required aria-label="Alasan retur">
          <option value="">Pilih alasan</option>
          <option value="damaged">Barang Rusak</option>
          <option value="wrong_delivery">Salah Kirim</option>
          <option value="not_as_ordered">Barang Tidak Sesuai</option>
          <option value="expired">Kadaluarsa</option>
          <option value="other">Lainnya</option>
        </SelectInput>
        <label
          v-if="form.reason === 'other'"
          class="block text-sm font-medium text-slate-700 dark:text-slate-200"
        >
          Keterangan
          <textarea
            v-model="form.note"
            rows="3"
            class="mt-1.5 w-full rounded-xl border-slate-300 dark:border-[#29476b] dark:bg-[#0a1b33]"
            placeholder="Tuliskan alasan retur"
          ></textarea>
        </label>
      </section>

      <section>
        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
          Penyelesaian Retur
        </h3>
        <div class="grid grid-cols-3 gap-3">
          <button
            v-for="opt in [
              { value: 'refund', label: 'Refund', desc: 'Pengembalian uang' },
              { value: 'ganti_produk', label: 'Ganti Produk', desc: 'Tukar dengan produk lain' },
              { value: 'potong_tagihan', label: 'Potong Tagihan', desc: 'Kredit pelanggan' },
            ]"
            :key="opt.value"
            type="button"
            :class="[
              'rounded-xl border p-4 text-left transition',
              resolution === opt.value
                ? 'border-emerald-500 bg-emerald-50 dark:border-emerald-400 dark:bg-emerald-400/10'
                : 'border-slate-200 hover:border-slate-300 dark:border-[#29476b]',
            ]"
            @click="resolution = opt.value"
          >
            <p class="text-sm font-semibold dark:text-white">{{ opt.label }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ opt.desc }}</p>
          </button>
        </div>
      </section>

      <section v-if="resolution === 'ganti_produk'">
        <SalesReturnReplacementSection
          :replacements="replacements"
          :branch-id="branchId"
          @add="addReplacement"
          @remove="removeReplacement"
        />
      </section>

      <section
        v-if="requiresCustomer && !transaction.customer_id"
        class="rounded-xl border border-amber-300 bg-amber-50 p-4 dark:border-amber-400/40 dark:bg-amber-400/10"
      >
        <p class="text-sm text-amber-700 dark:text-amber-300">
          Pelanggan wajib dipilih untuk Potong Tagihan. Transaksi ini tidak memiliki pelanggan
          terdaftar.
        </p>
      </section>

      <SalesReturnSummary
        v-model:settlement="settlement"
        :resolution="resolution"
        :selected-count="selectedItems.length"
        :total-qty="totalQty"
        :total-return-value="totalReturnValue"
        :replacement-total="replacementTotal"
        :difference="difference"
        :refund-amount="refundAmount"
        :customer-credit-amount="customerCreditAmount"
        :customer-pays-amount="customerPaysAmount"
      />
    </div>

    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button
        variant="secondary"
        :disabled="!form.reason || !selectedItems.length || form.processing"
        @click="submit('draft')"
        >Simpan Draft</Button
      >
      <Button :disabled="!canComplete || form.processing" @click="submit('completed')"
        >Selesaikan Retur</Button
      >
    </template>
  </Modal>
</template>
