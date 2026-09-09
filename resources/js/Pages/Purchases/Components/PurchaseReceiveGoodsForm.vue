<script setup>
import { computed, reactive, ref } from 'vue'
import { PackageCheck } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import Input from '@/Components/UI/Input.vue'
import PurchaseWorkflowItemQuantities from './PurchaseWorkflowItemQuantities.vue'

const props = defineProps({
  transaction: { type: Object, required: true },
})

const emit = defineEmits(['cancel', 'submit'])

const form = reactive({
  receivedDate: props.transaction.date,
  warehouse: '',
  deliveryNote: '',
  note: '',
})
const items = ref(
  props.transaction.items.map((item) => ({
    name: item.name,
    unit: item.unit,
    orderedQty: item.qty,
    quantity: item.qty,
  }))
)
const attachment = ref(null)
const submitted = ref(false)

const valid = computed(
  () =>
    Boolean(form.receivedDate && form.warehouse) &&
    items.value.every(
      (item) =>
        Number.isInteger(item.quantity) && item.quantity > 0 && item.quantity <= item.orderedQty
    )
)

const updateQuantity = ({ index, value }) => {
  items.value[index].quantity = value
}
const selectAttachment = (file) => {
  attachment.value = file
}
const submit = () => {
  submitted.value = true
  if (!valid.value) return

  emit('submit', {
    type: 'receive-goods',
    data: { ...form, items: items.value, attachment: attachment.value },
  })
}
</script>

<template>
  <form id="receive-goods-form" class="space-y-5" @submit.prevent="submit">
    <section class="rounded-2xl border border-slate-200 dark:border-[#29476b]">
      <header
        class="flex items-start gap-3 border-b border-slate-100 px-5 py-4 dark:border-[#29476b]"
      >
        <PackageCheck :size="19" class="mt-0.5 text-emerald-600" />
        <div>
          <h3 class="font-semibold text-slate-900 dark:text-white">Informasi Penerimaan</h3>
          <p class="mt-0.5 text-sm text-slate-500">Referensi {{ transaction.number }}</p>
        </div>
      </header>
      <div class="grid gap-4 p-5 sm:grid-cols-2">
        <DatePicker v-model="form.receivedDate" label="Tanggal Diterima" required />
        <Input
          v-model="form.warehouse"
          label="Gudang Penerimaan"
          placeholder="Masukkan gudang tujuan"
          required
        />
        <Input v-model="form.deliveryNote" label="Nomor Surat Jalan" placeholder="Opsional" />
        <Input v-model="form.note" label="Catatan" placeholder="Opsional" />
      </div>
    </section>

    <PurchaseWorkflowItemQuantities :items="items" label="Qty Diterima" @update="updateQuantity" />

    <section>
      <FileUpload
        accept=".pdf,.jpg,.jpeg,.png"
        label="Lampirkan surat jalan"
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
      Lengkapi tanggal, gudang, dan jumlah penerimaan dengan benar.
    </p>

    <div
      class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-4 dark:border-[#29476b]"
    >
      <Button variant="secondary" @click="emit('cancel')">Batal</Button>
      <Button type="submit">Simpan Penerimaan</Button>
    </div>
  </form>
</template>
