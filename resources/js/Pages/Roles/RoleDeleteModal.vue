<script setup>
import { router } from '@inertiajs/vue3'
import { AlertTriangle, Trash2 } from '@lucide/vue'
import { ref } from 'vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({ role: { type: Object, required: true } })
const emit = defineEmits(['close'])
const processing = ref(false)
const error = ref('')

const remove = () => {
  processing.value = true
  error.value = ''
  router.delete(route('roles.destroy', props.role.id), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
    onError: (errors) => {
      error.value = errors.role ?? 'Role tidak dapat dihapus.'
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
    title="Hapus Role"
    description="Role hanya dapat dihapus jika tidak sedang digunakan pengguna."
    size="md"
    @update:model-value="emit('close')"
  >
    <div
      class="flex gap-4 rounded-xl border border-red-100 bg-red-50 p-4 dark:border-red-400/20 dark:bg-red-400/10"
    >
      <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-red-100 text-red-600">
        <AlertTriangle :size="20" />
      </span>
      <div>
        <p class="font-medium text-red-900 dark:text-red-200">Hapus role {{ role.name }}?</p>
        <p class="mt-1 text-sm text-red-700 dark:text-red-300">
          Role ini memiliki {{ role.users_count }} pengguna.
        </p>
      </div>
    </div>
    <p v-if="error" class="mt-3 text-sm font-medium text-red-500">{{ error }}</p>

    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button variant="danger" :loading="processing" @click="remove">
        <Trash2 :size="17" class="mr-2" />Hapus Role
      </Button>
    </template>
  </Modal>
</template>
