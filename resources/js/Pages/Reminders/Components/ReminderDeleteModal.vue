<script setup>
import { router } from '@inertiajs/vue3'
import { Trash2 } from '@lucide/vue'
import { computed, ref } from 'vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  reminder: { type: Object, default: null },
  initialScope: { type: String, default: 'this' },
})

const emit = defineEmits(['close', 'deleted'])

const isRecurringDelete = computed(
  () =>
    Boolean(props.reminder?.rrule) ||
    ['daily', 'weekly', 'monthly', 'yearly'].includes(props.reminder?.type)
)

// Scope for delete operation
const scope = ref(props.initialScope)
const scopeOptions = [
  {
    value: 'this',
    label: 'Hapus hanya di acara ini',
    description: 'Tambahkan tanggal ini ke EXDATE tanpa menghapus master.',
  },
  {
    value: 'future',
    label: 'Hapus acara ini dan yang akan datang',
    description: 'Hentikan rangkaian sehari sebelum tanggal ini.',
  },
  {
    value: 'all',
    label: 'Hapus seluruh acara di seri ini',
    description: 'Hapus master event dari kalender.',
  },
]

const confirmDelete = () => {
  const data = { scope: isRecurringDelete.value ? scope.value : 'all' }
  const occurrenceStart = props.reminder?.occurrence_start || props.reminder?.start
  if (data.scope !== 'all' && occurrenceStart) data.occurrence_start = occurrenceStart

  router.delete(route('reminders.destroy', props.reminder.id), {
    data,
    preserveScroll: true,
    onSuccess: () => {
      emit('deleted')
      emit('close')
    },
  })
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="isRecurringDelete ? 'Hapus Reminder Berulang' : 'Hapus Reminder'"
    :description="
      isRecurringDelete
        ? 'Pilih bagian mana yang akan dihapus'
        : 'Apakah Anda yakin ingin menghapus reminder ini?'
    "
    size="md"
    @update:model-value="emit('close')"
  >
    <div class="space-y-4">
      <div
        class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-500/20 dark:bg-red-500/10"
      >
        <p class="font-medium text-red-700 dark:text-red-300">
          {{ reminder?.title || reminder?.name }}
        </p>
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">
          Reminder yang dihapus tidak dapat dikembalikan.
        </p>
      </div>

      <!-- Scope selection is only relevant for recurring masters. -->
      <div v-if="isRecurringDelete" class="space-y-3">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
          Pilih Penghapusan <span class="text-red-500">*</span>
        </label>
        <div class="space-y-2">
          <button
            v-for="option in scopeOptions"
            :key="option.value"
            type="button"
            class="flex w-full items-start gap-3 rounded-xl border p-3 text-left transition"
            :class="
              scope === option.value
                ? 'border-red-500 bg-red-50 dark:border-red-500/50 dark:bg-red-500/10'
                : 'border-slate-200 hover:bg-slate-50 dark:border-[#29476b] dark:hover:bg-[#163354]'
            "
            @click="scope = option.value"
          >
            <div
              class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full border-2"
              :class="
                scope === option.value
                  ? 'border-red-500 bg-red-500'
                  : 'border-slate-300 dark:border-[#29476b]'
              "
            >
              <div v-if="scope === option.value" class="size-2 rounded-full bg-white"></div>
            </div>
            <div class="min-w-0 flex-1">
              <p
                class="text-sm font-medium"
                :class="
                  scope === option.value
                    ? 'text-red-700 dark:text-red-400'
                    : 'text-slate-900 dark:text-white'
                "
              >
                {{ option.label }}
              </p>
              <p class="text-xs text-slate-500">{{ option.description }}</p>
            </div>
          </button>
        </div>
      </div>
    </div>

    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button variant="danger" @click="confirmDelete">
        <Trash2 :size="17" class="mr-2" />Hapus Reminder
      </Button>
    </template>
  </Modal>
</template>
