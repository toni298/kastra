<script setup>
import { AlertTriangle, Trash2 } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'

defineProps({ account: { type: Object, required: true } })
const emit = defineEmits(['close', 'confirm'])
</script>

<template>
  <Modal
    :model-value="true"
    title="Hapus Akun"
    description="Tindakan ini tidak dapat dibatalkan."
    size="md"
    :close-on-overlay="false"
    @update:model-value="emit('close')"
  >
    <div
      class="flex gap-4 rounded-xl border border-red-100 bg-red-50 p-4 dark:border-red-400/20 dark:bg-red-400/10"
    >
      <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-red-100 text-red-600"
        ><AlertTriangle :size="20"
      /></span>
      <div>
        <p class="font-medium text-red-900 dark:text-red-200">Hapus {{ account.name }}?</p>
        <p class="mt-1 text-sm leading-6 text-red-700 dark:text-red-300">
          Akun <strong>{{ account.code }}</strong> akan dihapus dari struktur akun perusahaan.
        </p>
      </div>
    </div>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button variant="danger" @click="emit('confirm')"
        ><Trash2 :size="17" class="mr-2" />Hapus Akun</Button
      ></template
    >
  </Modal>
</template>
