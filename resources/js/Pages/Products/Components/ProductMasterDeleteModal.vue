<script setup>
import { router } from '@inertiajs/vue3'
import { AlertTriangle, Trash2 } from '@lucide/vue'
import { ref } from 'vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
const props = defineProps({
  item: { type: Object, required: true },
  master: { type: String, required: true },
  label: { type: String, required: true },
})
const emit = defineEmits(['close', 'deleted'])
const processing = ref(false)
const remove = () => {
  processing.value = true
  router.delete(route('products.master.destroy', [props.master, props.item.id]), {
    preserveScroll: true,
    onSuccess: () => {
      emit('deleted')
      emit('close')
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
    :title="`Hapus ${label}`"
    description="Tindakan ini tidak dapat dibatalkan."
    size="md"
    @update:model-value="emit('close')"
    ><div
      class="flex gap-4 rounded-xl border border-red-100 bg-red-50 p-4 dark:border-red-400/20 dark:bg-red-400/10"
    >
      <AlertTriangle class="shrink-0 text-red-600" :size="22" />
      <div>
        <p class="font-medium text-red-900 dark:text-red-200">Hapus {{ item.name }}?</p>
        <p class="mt-1 text-sm text-red-700 dark:text-red-300">
          Data tidak dapat dihapus jika masih digunakan produk.
        </p>
      </div>
    </div>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button variant="danger" :loading="processing" @click="remove"
        ><Trash2 :size="16" class="mr-2" />Hapus</Button
      ></template
    ></Modal
  >
</template>
