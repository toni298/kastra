<script setup>
import { router } from '@inertiajs/vue3'
import { AlertTriangle, Trash2 } from '@lucide/vue'
import { computed, ref } from 'vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  item: { type: Object, required: true },
  type: { type: String, required: true },
})

const emit = defineEmits(['close'])
const processing = ref(false)
const entityLabel = computed(() => props.type.charAt(0).toUpperCase() + props.type.slice(1))

const remove = () => {
  processing.value = true
  router.delete(route(`${props.type}.destroy`, props.item.id), {
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
    :title="`Hapus ${entityLabel}`"
    description="Tindakan ini tidak dapat dibatalkan."
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
          Yakin ingin menghapus {{ item.nama }}?
        </p>
        <p class="mt-1 text-sm leading-6 text-red-700 dark:text-red-300">
          Data {{ type }} ini akan dihapus dari struktur perusahaan.
        </p>
      </div>
    </div>
    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button variant="danger" :loading="processing" @click="remove">
        <Trash2 :size="17" class="mr-2" />Hapus {{ entityLabel }}
      </Button>
    </template>
  </Modal>
</template>
