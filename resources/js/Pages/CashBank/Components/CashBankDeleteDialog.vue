<script setup>
import { AlertTriangle } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'

defineProps({
  transaction: { type: Object, required: true },
})
const emit = defineEmits(['close', 'confirm'])
</script>

<template>
  <Modal
    :model-value="true"
    title="Hapus Transaksi?"
    description="Perubahan ini akan menghapus transaksi dari financial timeline."
    size="md"
    @update:model-value="emit('close')"
  >
    <div
      class="flex gap-4 rounded-xl border border-red-100 bg-red-50 p-4 dark:border-red-400/20 dark:bg-red-400/10"
    >
      <AlertTriangle class="shrink-0 text-red-600 dark:text-red-300" :size="22" />
      <div>
        <p class="font-medium text-red-900 dark:text-red-200">{{ transaction.description }}</p>
        <p class="mt-1 text-sm text-red-700 dark:text-red-300">
          Referensi {{ transaction.reference }} dan jurnal terkait akan ditinjau ulang.
        </p>
      </div>
    </div>
    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button variant="danger" @click="emit('confirm')">Hapus Transaksi</Button>
    </template>
  </Modal>
</template>
