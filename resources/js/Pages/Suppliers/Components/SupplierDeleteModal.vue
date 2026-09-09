<script setup>
import { router } from '@inertiajs/vue3'
import { AlertTriangle, Trash2 } from '@lucide/vue'
import { ref } from 'vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({ supplier: { type: Object, required: true } })
const emit = defineEmits(['close'])
const processing = ref(false)

const remove = () => {
  processing.value = true
  router.delete(route('suppliers.destroy', props.supplier.id), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
    onFinish: () => {
      processing.value = false
    },
  })
}
</script>

<template>
  <Modal
    :model-value="true"
    title="Hapus Supplier"
    description="Supplier akan dinonaktifkan dari daftar aktif dan disimpan untuk kebutuhan audit."
    size="md"
    @update:model-value="emit('close')"
  >
    <div
      class="flex gap-4 rounded-xl border border-red-100 bg-red-50 p-4 dark:border-red-400/20 dark:bg-red-400/10"
    >
      <span
        class="grid size-10 shrink-0 place-items-center rounded-xl bg-red-100 text-red-600 dark:bg-red-400/15 dark:text-red-300"
      >
        <AlertTriangle :size="20" />
      </span>
      <div>
        <p class="font-medium text-red-900 dark:text-red-200">
          Yakin ingin menghapus {{ supplier.name }}?
        </p>
        <p class="mt-1 text-sm leading-6 text-red-700 dark:text-red-300">
          Tindakan ini tidak dapat dibatalkan dari halaman ini.
        </p>
      </div>
    </div>

    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button variant="danger" :loading="processing" @click="remove">
        <Trash2 :size="17" class="mr-2" />Hapus Supplier
      </Button>
    </template>
  </Modal>
</template>
