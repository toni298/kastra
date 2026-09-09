<script setup>
import { router } from '@inertiajs/vue3'
import { AlertTriangle, Trash2 } from '@lucide/vue'
import { ref } from 'vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({ stock: { type: Object, required: true } })
const emit = defineEmits(['close'])
const processing = ref(false)
const error = ref('')

const remove = () => {
  processing.value = true
  error.value = ''
  router.delete(route('inventory.products.destroy', props.stock.id), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
    onError: (errors) => {
      error.value = errors.stock ?? 'Produk tidak dapat dihapus dari Inventory.'
    },
    onFinish: () => {
      processing.value = false
    },
  })
}
</script>

<template>
  <Modal
    :model-value="true"
    title="Hapus dari Inventory"
    description="Master produk tidak akan dihapus."
    size="md"
    @update:model-value="emit('close')"
  >
    <div
      class="flex gap-4 rounded-xl border border-red-100 bg-red-50 p-4 dark:border-red-400/20 dark:bg-red-400/10"
    >
      <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-red-100 text-red-600"
        ><AlertTriangle :size="20"
      /></span>
      <div>
        <p class="font-medium text-red-900 dark:text-red-200">
          Hapus {{ stock.product.name }} dari {{ stock.gudang.nama }}?
        </p>
        <p class="mt-1 text-sm leading-6 text-red-700 dark:text-red-300">
          Assignment hanya dapat dihapus jika saldo sudah 0. Data master produk tetap tersedia.
        </p>
      </div>
    </div>
    <p v-if="error" class="mt-3 text-sm font-medium text-red-500">{{ error }}</p>
    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button variant="danger" :loading="processing" @click="remove"
        ><Trash2 :size="17" class="mr-2" />Hapus dari Inventory</Button
      >
    </template>
  </Modal>
</template>
