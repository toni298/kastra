<script setup>
import { computed } from 'vue'
import {
  CreditCard,
  PackageCheck,
  Pencil,
  Printer,
  ReceiptText,
  Send,
  Trash2,
  Undo2,
} from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import { useAccessControl } from '@/Composables/useAccessControl'

const props = defineProps({
  transaction: { type: Object, required: true },
})

const emit = defineEmits(['action'])
const { can } = useAccessControl()

const actionMap = {
  draft: [
    { id: 'edit-purchase', label: 'Edit', icon: Pencil, variant: 'secondary' },
    { id: 'send-po', label: 'Kirim PO', icon: Send, variant: 'primary' },
    { id: 'delete-purchase', label: 'Hapus', icon: Trash2, variant: 'danger' },
  ],
  purchase_order: [
    { id: 'receive-goods', label: 'Terima Barang', icon: PackageCheck, variant: 'primary' },
  ],
  goods_received: [
    { id: 'create-invoice', label: 'Buat Purchase Invoice', icon: ReceiptText, variant: 'primary' },
  ],
  invoice: [
    { id: 'pay-supplier', label: 'Bayar Supplier', icon: CreditCard, variant: 'primary' },
    { id: 'return-goods', label: 'Retur Barang', icon: Undo2, variant: 'secondary' },
    { id: 'print-purchase', label: 'Cetak Invoice', icon: Printer, variant: 'secondary' },
  ],
  received: [
    { id: 'create-invoice', label: 'Buat Purchase Invoice', icon: ReceiptText, variant: 'primary' },
  ],
  paid: [
    { id: 'return-goods', label: 'Retur Barang', icon: Undo2, variant: 'secondary' },
    { id: 'print-purchase', label: 'Cetak Invoice', icon: Printer, variant: 'secondary' },
    {
      id: 'complete-purchase',
      label: 'Selesaikan Transaksi',
      icon: ReceiptText,
      variant: 'primary',
    },
  ],
  closed: [{ id: 'print-purchase', label: 'Cetak Invoice', icon: Printer, variant: 'secondary' }],
}

const state = computed(() => {
  if (['closed', 'returned'].includes(props.transaction.status) || props.transaction.is_closed)
    return 'closed'
  if (props.transaction.payment === 'paid' || props.transaction.payment === 'Lunas') return 'paid'

  if (actionMap[props.transaction.workflowStage]) return props.transaction.workflowStage
  if (props.transaction.status === 'received') return 'received'
  if (
    props.transaction.status === 'completed' ||
    props.transaction.payment === 'partial' ||
    props.transaction.payment === 'unpaid'
  )
    return 'invoice'
  if (props.transaction.status === 'Draft') return 'draft'
  if (props.transaction.status === 'Menunggu Barang') return 'purchase_order'
  if (props.transaction.status === 'Barang Diterima') return 'goods_received'
  if (props.transaction.status === 'Purchase Invoice') return 'invoice'

  return null
})

const actionPermissions = {
  'edit-purchase': 'pembelian.edit',
  'delete-purchase': 'pembelian.delete',
  'send-po': 'pembelian.edit',
  'receive-goods': 'pembelian.edit',
  'create-invoice': 'pembelian.edit',
  'pay-supplier': 'pembelian.pay',
  'return-goods': 'pembelian.return',
  'print-purchase': 'pembelian.print',
  'complete-purchase': 'pembelian.complete',
}
const actions = computed(() =>
  (actionMap[state.value] ?? []).filter((action) => {
    const permission = actionPermissions[action.id]
    return !permission || can(permission)
  })
)

const choose = (action) => {
  emit('action', { action: action.id, item: props.transaction })
}
</script>

<template>
  <section
    class="shrink-0 border-b border-slate-200 bg-white px-5 py-4 dark:border-[#29476b] dark:bg-[#102542] sm:px-6"
    aria-label="Aksi transaksi pembelian"
  >
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aksi Tersedia</p>
        <p class="mt-1 text-sm text-slate-500">Disesuaikan dengan status transaksi saat ini.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <Button
          v-for="action in actions"
          :key="action.id"
          :variant="action.variant"
          size="sm"
          @click="choose(action)"
        >
          <component :is="action.icon" :size="16" class="mr-2" />
          {{ action.label }}
        </Button>
      </div>
    </div>
  </section>
</template>
