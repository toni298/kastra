<script setup>
import { LockKeyhole } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
const emit = defineEmits(['close'])
</script>
<template>
  <Modal
    :model-value="true"
    title="Tutup Periode"
    description="Kunci pembukuan periode Juli 2026."
    size="md"
    @update:model-value="emit('close')"
    ><div
      class="flex gap-4 rounded-xl border border-amber-100 bg-amber-50 p-4 dark:border-amber-400/20 dark:bg-amber-400/10"
    >
      <LockKeyhole class="shrink-0 text-amber-600" :size="22" />
      <div>
        <p class="font-medium text-amber-900 dark:text-amber-200">
          Pastikan seluruh transaksi sudah diperiksa.
        </p>
        <p class="mt-1 text-sm leading-6 text-amber-700 dark:text-amber-300">
          Setelah ditutup, transaksi pada periode ini tidak dapat diubah tanpa membuka kembali
          periode.
        </p>
      </div>
    </div>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button @click="emit('close')">Tutup Periode</Button></template
    ></Modal
  >
</template>
