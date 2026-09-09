<script setup>
import { AlertTriangle } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
defineProps({ item: { type: Object, required: true } })
const emit = defineEmits(['close', 'confirm'])
</script>
<template>
  <Modal
    :model-value="true"
    title="Nonaktifkan Rekening"
    description="Rekening tidak dapat dipakai untuk transaksi baru."
    size="md"
    @update:model-value="emit('close')"
    ><div
      class="flex gap-4 rounded-xl border border-amber-100 bg-amber-50 p-4 dark:border-amber-400/20 dark:bg-amber-400/10"
    >
      <AlertTriangle class="shrink-0 text-amber-600" :size="22" />
      <div>
        <p class="font-medium text-amber-900 dark:text-amber-200">Nonaktifkan {{ item.name }}?</p>
        <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
          Riwayat transaksi tetap tersimpan dan dapat dilihat.
        </p>
      </div>
    </div>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button variant="danger" @click="emit('confirm')">Nonaktifkan</Button></template
    ></Modal
  >
</template>
