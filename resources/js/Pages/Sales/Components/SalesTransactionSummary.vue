<script setup>
import { computed, watch } from 'vue'
import { Minus, Plus, Trash2 } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'

const props = defineProps({
  items: { type: Array, default: () => [] },
  discount: { type: Number, default: 0 },
  discountType: { type: String, default: 'amount' },
  taxRate: { type: Number, default: 11 },
  paymentAmount: { type: Number, default: 0 },
  paymentMethod: { type: String, default: 'cash' },
  dueDate: { type: String, default: '' },
  transactionDate: { type: String, default: '' },
  editing: { type: Boolean, default: false },
  proofFile: { type: [Object, null], default: null },
})
const emit = defineEmits([
  'update',
  'remove',
  'cancel',
  'draft',
  'save',
  'update:discount',
  'update:discountType',
  'update:taxRate',
  'update:paymentAmount',
  'update:paymentMethod',
  'update:dueDate',
  'update:proofFile',
])

const totalQty = computed(() => props.items.reduce((sum, item) => sum + Number(item.qty || 0), 0))
const subtotal = computed(() =>
  props.items.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 0), 0)
)
const discountAmount = computed(() =>
  props.discountType === 'percent'
    ? Math.round((subtotal.value * Number(props.discount || 0)) / 100)
    : Number(props.discount || 0)
)
const taxAmount = computed(() => Math.round((subtotal.value * Number(props.taxRate || 0)) / 100))
const grandTotal = computed(() =>
  Math.max(0, subtotal.value - discountAmount.value + taxAmount.value)
)
const unpaid = computed(() => Number(props.paymentAmount || 0) < grandTotal.value)
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
const hasInsufficientStock = computed(() =>
  props.items.some(
    (item) => Number(item.qty || 0) > Number(item.available_quantity ?? Number.MAX_SAFE_INTEGER)
  )
)
const onDiscountTypeChange = (event) => {
  emit('update:discountType', event.target.value)
  emit('update:discount', 0)
}
const money = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value || 0))
const formatPrice = (value) =>
  new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(value || 0))
const parseNumber = (value) => Number(String(value).replace(/\D/g, '')) || 0
const modify = (item, key, value) => emit('update', { id: item.id, key, value: Number(value) || 0 })
const priceInput = (item, event) => modify(item, 'price', parseNumber(event.target.value))
const insufficient = (item) =>
  Number(item.qty || 0) > Number(item.available_quantity ?? Number.MAX_SAFE_INTEGER)
const canIncrease = (item) =>
  Number(item.qty || 0) < Number(item.available_quantity ?? Number.MAX_SAFE_INTEGER)
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
        class="rounded-xl border p-3 dark:border-[#29476b] dark:bg-[#0a1b33]"
        :class="insufficient(item) ? 'border-red-400' : 'border-slate-200'"
      >
        <div class="flex justify-between gap-2">
          <p class="text-sm font-medium text-slate-900 dark:text-white">{{ item.name }}</p>
          <button class="text-slate-400 hover:text-red-500" @click="emit('remove', item.id)">
            <Trash2 :size="16" />
          </button>
        </div>
        <div class="mt-3 grid grid-cols-[140px_1fr] gap-2">
          <div class="flex items-center rounded-lg border border-slate-200 dark:border-[#29476b]">
            <button class="p-2" @click="modify(item, 'qty', Math.max(1, item.qty - 1))">
              <Minus :size="13" /></button
            ><input
              :value="item.qty"
              type="number"
              min="1"
              :max="item.available_quantity"
              class="w-full border-0 bg-transparent p-0 text-center text-sm"
              @input="
                modify(item, 'qty', Math.min(Number(item.available_quantity), $event.target.value))
              "
            /><button
              class="p-2 disabled:cursor-not-allowed disabled:opacity-40"
              :disabled="!canIncrease(item)"
              @click="modify(item, 'qty', item.qty + 1)"
            >
              <Plus :size="13" />
            </button>
          </div>
          <input
            :value="formatPrice(item.price)"
            inputmode="numeric"
            class="rounded-lg border-slate-200 py-2 text-right text-sm dark:border-[#29476b] dark:bg-[#102542]"
            aria-label="Harga"
            @input="priceInput(item, $event)"
          />
        </div>
        <p
          class="mt-2 text-xs"
          :class="insufficient(item) ? 'text-red-600 dark:text-red-300' : 'text-slate-500'"
        >
          Stok tersedia: {{ item.available_quantity }} {{ item.unit ?? ''
          }}<span v-if="insufficient(item)" class="font-semibold"> · Stok tidak mencukupi</span>
        </p>
        <p class="mt-2 text-right text-sm font-bold">{{ money(item.price * item.qty) }}</p>
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
      <div class="flex items-center justify-between gap-2">
        <dt>Diskon</dt>
        <dd class="flex gap-1">
          <select
            :value="discountType"
            class="w-17 rounded-lg border-slate-200 py-1 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
            @change="onDiscountTypeChange"
          >
            <option value="amount">Rp</option>
            <option value="percent">%</option></select
          ><input
            :value="discount"
            type="number"
            min="0"
            :max="discountType === 'percent' ? 100 : undefined"
            class="w-36 rounded-lg border-slate-200 py-1 text-right text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
            @input="emit('update:discount', parseNumber($event.target.value))"
          />
        </dd>
      </div>
      <div class="flex items-center justify-between gap-2">
        <dt>Pajak</dt>
        <dd>
          <select
            :value="taxRate"
            class="rounded-lg border-slate-200 py-1 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
            @change="emit('update:taxRate', Number($event.target.value))"
          >
            <option :value="0">0%</option>
            <option :value="11">11%</option>
            <option :value="12">12%</option>
          </select>
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
        <SelectInput
          :model-value="paymentMethod"
          label="Metode Pembayaran"
          required
          aria-label="Metode pembayaran"
          @update:model-value="
            (value) => {
              emit('update:paymentMethod', value)
              if (value !== 'cash') emit('update:paymentAmount', grandTotal)
            }
          "
          ><option value="cash">Cash</option>
          <option value="transfer">Transfer</option>
          <option value="qris">QRIS</option>
          <option value="ewallet">E-Wallet</option></SelectInput
        ><label class="block text-sm font-medium text-slate-700 dark:text-slate-200"
          >Jumlah Dibayar
          <div
            class="mt-1.5 flex items-center rounded-xl border border-slate-300 bg-white px-3 dark:border-[#29476b] dark:bg-[#0a1b33]"
          >
            <span class="text-sm font-semibold text-slate-500">Rp</span
            ><input
              :value="formatPrice(paymentAmount)"
              class="w-full border-0 bg-transparent py-2.5 pl-2 text-right text-sm text-slate-900 focus:ring-0 dark:text-white"
              inputmode="numeric"
              @input="emit('update:paymentAmount', parseNumber($event.target.value))"
            /></div></label
        ><DatePicker
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
          @update:model-value="emit('update:proofFile', $event)"
        />
        <div class="flex justify-between text-sm font-medium text-slate-600 dark:text-slate-300">
          <span>Kembalian</span
          ><span class="text-emerald-600 dark:text-emerald-300">{{
            money(Math.max(0, paymentAmount - grandTotal))
          }}</span>
        </div>
        <p
          class="text-xs"
          :class="
            unpaid ? 'text-amber-600 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-300'
          "
        >
          Status: <strong>{{ unpaid ? 'Belum Lunas' : 'Lunas' }}</strong>
        </p>
      </div>
    </section>
    <div class="grid grid-cols-2 gap-2">
      <Button variant="secondary" @click="emit('draft')">Simpan Draft</Button
      ><Button :disabled="!items.length || hasInsufficientStock" @click="emit('save')"
        >Simpan Transaksi</Button
      ><Button class="col-span-2" variant="ghost" @click="emit('cancel')">Batal</Button>
    </div>
  </aside>
</template>
