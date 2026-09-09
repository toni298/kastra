<script setup>
import { computed, ref, watch } from 'vue'
import { Minus, Plus, Trash2 } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import { usePurchasePayment } from '../Composables/usePurchasePayment'
const props = defineProps({
  items: { type: Array, default: () => [] },
  dueDate: { type: String, default: '' },
  transactionDate: { type: String, default: '' },
})
const emit = defineEmits([
  'update',
  'remove',
  'cancel',
  'draft',
  'save',
  'update:dueDate',
  'update:grandTotal',
])
const discount = ref(0)
const discountType = ref('amount')
const taxRate = ref(11)
const extraFee = ref(0)
const proofFile = ref(null)
const totalQty = computed(() => props.items.reduce((sum, item) => sum + item.qty, 0))
const subtotal = computed(() =>
  props.items.reduce((sum, item) => sum + (item.price * item.qty - item.discount), 0)
)
const tax = computed(() => (subtotal.value * taxRate.value) / 100)
const grandTotal = computed(() =>
  Math.max(0, subtotal.value - discountAmount.value + tax.value + Number(extraFee.value || 0))
)
const discountAmount = computed(() =>
  discountType.value === 'percent'
    ? Math.round((subtotal.value * Number(discount.value || 0)) / 100)
    : Number(discount.value || 0)
)
const {
  paymentMethod,
  paymentAmount,
  setPaymentAmount,
  ownerAmount,
  accountBalance,
  useCombinedPayment,
  fetchingBalance,
  totalCovered,
  canUseCombined,
  getPaymentPayload,
} = usePurchasePayment(grandTotal)
const remaining = computed(() => Math.max(0, grandTotal.value - totalCovered.value))
const unpaid = computed(() => totalCovered.value < grandTotal.value)
watch(grandTotal, (val) => emit('update:grandTotal', val), { immediate: true })
watch(
  unpaid,
  (isUnpaid) => {
    if (isUnpaid && !props.dueDate && props.transactionDate) {
      const d = new Date(props.transactionDate)
      d.setDate(d.getDate() + 7)
      emit('update:dueDate', d.toISOString().slice(0, 10))
    }
    if (!isUnpaid && props.dueDate) {
      emit('update:dueDate', '')
    }
  },
  { immediate: true }
)
const money = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value)
const modify = (item, key, value) => emit('update', { id: item.id, key, value: Number(value) || 0 })
const parseMoney = (value) => Number(String(value ?? '').replace(/[^0-9]/g, '')) || 0
const formatted = (value) => Number(value || 0).toLocaleString('id-ID')
const save = () => emit('save', { ...getPaymentPayload(), proofFile: proofFile.value })
</script>
<template>
  <aside class="space-y-4 lg:sticky lg:top-0">
    <div>
      <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Ringkasan Transaksi</h2>
      <p class="mt-1 text-sm text-slate-500">Tinjau item sebelum menyimpan.</p>
    </div>
    <div class="max-h-[330px] space-y-3 overflow-y-auto pr-1">
      <div
        v-for="item in items"
        :key="item.id"
        class="rounded-xl border border-slate-200 p-3 dark:border-[#29476b] dark:bg-[#0a1b33]"
      >
        <div class="flex justify-between gap-2">
          <p class="text-sm font-medium text-slate-900 dark:text-white">{{ item.name }}</p>
          <button class="text-slate-400 hover:text-red-500" @click="emit('remove', item.id)">
            <Trash2 :size="16" />
          </button>
        </div>
        <div class="mt-3 grid grid-cols-[100px_1fr] gap-2">
          <div class="flex items-center rounded-lg border border-slate-200 dark:border-[#29476b]">
            <button class="p-2" @click="modify(item, 'qty', Math.max(1, item.qty - 1))">
              <Minus :size="13" /></button
            ><input
              :value="item.qty"
              type="number"
              min="1"
              class="w-full border-0 bg-transparent p-0 text-center text-sm"
              @input="modify(item, 'qty', $event.target.value)"
            /><button class="p-2" @click="modify(item, 'qty', item.qty + 1)">
              <Plus :size="13" />
            </button>
          </div>
          <input
            :value="formatted(item.price)"
            type="text"
            inputmode="numeric"
            class="rounded-lg border-slate-200 py-2 text-right text-sm dark:border-[#29476b] dark:bg-[#102542]"
            aria-label="Harga"
            @input="modify(item, 'price', parseMoney($event.target.value))"
          />
        </div>
        <div class="mt-2 flex items-center justify-between gap-2">
          <div class="flex items-center rounded-lg border border-slate-200 dark:border-[#29476b]">
            <span class="px-2 text-xs text-slate-500">Rp</span
            ><input
              :value="formatted(item.discount)"
              type="text"
              inputmode="numeric"
              min="0"
              class="w-28 border-0 bg-transparent py-1.5 text-xs dark:bg-transparent"
              placeholder="Diskon item (Rp)"
              @input="modify(item, 'discount', parseMoney($event.target.value))"
            />
          </div>
          <p class="text-sm font-bold">{{ money(item.price * item.qty - item.discount) }}</p>
        </div>
      </div>
      <p
        v-if="!items.length"
        class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-[#29476b]"
      >
        Belum ada item dipilih.
      </p>
    </div>
    <dl class="space-y-2 border-t border-slate-200 pt-4 text-sm dark:border-[#29476b]">
      <div class="flex justify-between">
        <dt>Jumlah Item</dt>
        <dd class="font-medium">{{ items.length }}</dd>
      </div>
      <div class="flex justify-between">
        <dt>Total Qty</dt>
        <dd class="font-bold">{{ totalQty }}</dd>
      </div>
      <div class="flex justify-between">
        <dt>Subtotal</dt>
        <dd class="font-bold">{{ money(subtotal) }}</dd>
      </div>
      <div class="flex items-center justify-between">
        <dt>Diskon</dt>
        <dd>
          <div class="flex gap-1">
            <select
              v-model="discountType"
              class="rounded-lg border-slate-200 py-1 text-xs dark:border-[#29476b] dark:bg-[#0a1b33]"
            >
              <option value="amount">Rp</option>
              <option value="percent">%</option></select
            ><input
              :value="discountType === 'amount' ? formatted(discount) : discount"
              type="text"
              inputmode="numeric"
              class="w-24 rounded-lg border-slate-200 py-1 text-right text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
              @input="
                discount =
                  discountType === 'amount'
                    ? parseMoney($event.target.value)
                    : Number($event.target.value) || 0
              "
            />
          </div>
        </dd>
      </div>
      <div class="flex items-center justify-between">
        <dt>Pajak</dt>
        <dd>
          <select
            v-model="taxRate"
            class="rounded-lg border-slate-200 py-1 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <option :value="0">Tanpa Pajak</option>
            <option :value="11">PPN 11%</option>
            <option :value="12">PPN 12%</option>
          </select>
        </dd>
      </div>
      <div class="flex items-center justify-between">
        <dt>Biaya Tambahan</dt>
        <dd>
          <input
            :value="formatted(extraFee)"
            type="text"
            inputmode="numeric"
            min="0"
            class="w-28 rounded-lg border-slate-200 py-1 text-right text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
            @input="extraFee = parseMoney($event.target.value)"
          />
        </dd>
      </div>
    </dl>
    <div class="rounded-2xl bg-slate-950 p-4 text-white dark:bg-emerald-400 dark:text-[#071426]">
      <p class="text-xs font-medium uppercase tracking-wide opacity-60">Grand Total</p>
      <p class="mt-1 text-2xl font-bold">{{ money(grandTotal) }}</p>
    </div>
    <section class="rounded-xl border border-slate-200 p-3 dark:border-[#29476b]">
      <h3 class="font-semibold dark:text-white">Pembayaran</h3>
      <div class="mt-3 grid gap-3">
        <select
          v-model="paymentMethod"
          class="w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
        >
          <option value="">Pilih metode pembayaran</option>
          <option value="transfer_bank">Transfer Bank</option>
          <option value="tunai">Tunai</option>
          <option value="giro">Giro</option>
        </select>
        <div v-if="accountBalance !== null" class="text-xs text-slate-500">
          Saldo Rekening: <strong>{{ money(accountBalance) }}</strong>
          <span v-if="fetchingBalance">(memuat...)</span>
        </div>
        <div
          v-if="canUseCombined"
          class="flex items-center gap-2 rounded-lg bg-amber-50 p-2 text-xs text-amber-800 dark:bg-amber-400/10 dark:text-amber-300"
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
            label="Jumlah Pembayaran"
            :max="accountBalance"
            :hint="`Maksimal ${new Intl.NumberFormat('id-ID').format(accountBalance)}`"
            placeholder="Contoh: 500.000"
            required
            @update:model-value="setPaymentAmount($event)"
          />
        </template>
        <template v-else>
          <CurrencyInput
            :model-value="paymentAmount"
            label="Dari Rekening Perusahaan"
            :max="accountBalance"
            :hint="
              accountBalance !== null
                ? `Maks. saldo ${new Intl.NumberFormat('id-ID').format(accountBalance)}`
                : ''
            "
            placeholder="Maks. saldo rekening"
            required
            @update:model-value="setPaymentAmount($event)"
          />
          <CurrencyInput
            v-model="ownerAmount"
            label="Sumber Dana Lain"
            placeholder="Sisa yang ditanggung sumber lain"
          />
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Total Gabungan</span>
            <strong :class="totalCovered === grandTotal ? 'text-emerald-600' : 'text-orange-600'">
              {{ money(totalCovered) }}
            </strong>
          </div>
        </template>
        <DatePicker
          v-if="unpaid"
          :model-value="dueDate"
          label="Jatuh Tempo"
          required
          @update:model-value="emit('update:dueDate', $event)"
        />
        <FileUpload
          :model-value="proofFile"
          accept="image/jpeg,image/png,image/webp,application/pdf"
          label="Bukti Pembayaran (opsional)"
          hint="JPG, PNG, WebP, atau PDF. Maks. 5 MB."
          :max-size="5"
          :show-preview="true"
          @update:model-value="proofFile = $event"
        />
        <div class="flex justify-between text-sm">
          <span class="text-slate-500">Sisa Hutang</span>
          <strong class="text-orange-600">{{ money(remaining) }}</strong>
        </div>
      </div>
    </section>
    <div class="grid grid-cols-2 gap-2">
      <Button variant="secondary" @click="emit('draft')">Simpan Draft</Button
      ><Button :disabled="!items.length" @click="save">Simpan Transaksi</Button
      ><Button class="col-span-2" variant="ghost" @click="emit('cancel')">Batal</Button>
    </div>
  </aside>
</template>
