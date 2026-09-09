<script setup>
import { computed, ref } from 'vue'
import Modal from '@/Components/UI/Modal.vue'
import PurchaseInvoiceForm from './PurchaseInvoiceForm.vue'
import PurchasePaymentForm from './PurchasePaymentForm.vue'
import PurchaseReceiveGoodsForm from './PurchaseReceiveGoodsForm.vue'
import PurchaseReturnForm from './PurchaseReturnForm.vue'

const props = defineProps({
  type: { type: String, required: true },
  transaction: { type: Object, required: true },
})

const emit = defineEmits(['close', 'submit'])

const isOpen = ref(true)
let closeRequested = false

const workflows = {
  'receive-goods': {
    title: 'Terima Barang',
    description: 'Catat barang yang diterima dari supplier.',
    component: PurchaseReceiveGoodsForm,
  },
  'create-invoice': {
    title: 'Purchase Invoice',
    description: 'Catat invoice supplier berdasarkan transaksi pembelian.',
    component: PurchaseInvoiceForm,
  },
  'pay-supplier': {
    title: 'Bayar Supplier',
    description: 'Catat pembayaran tagihan supplier.',
    component: PurchasePaymentForm,
  },
  'return-goods': {
    title: 'Retur Barang',
    description: 'Catat barang yang dikembalikan kepada supplier.',
    component: PurchaseReturnForm,
  },
}

const workflow = computed(() => workflows[props.type])

const close = () => {
  if (closeRequested) return

  closeRequested = true
  isOpen.value = false
  emit('close')
}

const handleOpenChange = (value) => {
  isOpen.value = value

  if (!value) {
    close()
  }
}
</script>

<template>
  <Modal
    :model-value="isOpen"
    :title="workflow.title"
    :description="workflow.description"
    :size="type === 'return-goods' ? 'lg' : type === 'pay-supplier' ? 'purchase' : 'full'"
    z-index-class="z-[70]"
    :close-on-overlay="true"
    :close-on-escape="true"
    @update:model-value="handleOpenChange"
  >
    <component
      :is="workflow.component"
      :transaction="transaction"
      @cancel="close"
      @submit="emit('submit', $event)"
    />
  </Modal>
</template>
