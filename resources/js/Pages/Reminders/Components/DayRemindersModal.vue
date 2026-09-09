<script setup>
import { Plus } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import ReminderListItem from './ReminderListItem.vue'
import { useDayReminderHelpers } from '../Composables/useDayReminderHelpers'

const props = defineProps({
  date: { type: Date, required: true },
  reminders: { type: Array, default: () => [] },
})

const emit = defineEmits(['close', 'add', 'edit', 'delete', 'complete'])

const { formattedDate, completeReminder, handleEdit, handleDelete } =
  useDayReminderHelpers(props, emit)
</script>

<template>
  <Modal :model-value="true" :title="formattedDate" size="xl" @update:model-value="emit('close')">
    <p class="-mt-2 mb-4 text-sm text-slate-400 dark:text-slate-500">
      {{ reminders.length }} reminder pada tanggal ini
    </p>

    <!-- List -->
    <div v-if="reminders.length" class="max-h-[55vh] space-y-4 overflow-y-auto">
      <ReminderListItem
        v-for="reminder in reminders"
        :key="reminder.id"
        :reminder="reminder"
        @edit="handleEdit"
        @delete="handleDelete"
        @complete="completeReminder"
      />
    </div>

    <!-- Empty state -->
    <div
      v-else
      class="rounded-2xl border border-slate-200 px-6 py-14 text-center dark:border-[#29476b]"
    >
      <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
        Tidak ada reminder untuk tanggal ini
      </p>
      <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
        Klik tombol tambah untuk membuat reminder baru.
      </p>
    </div>

    <template #footer>
      <Button variant="secondary" @click="emit('close')">Tutup</Button>
      <Button @click="emit('add')">
        <Plus :size="16" :stroke-width="2.5" />
        Tambah Reminder
      </Button>
    </template>
  </Modal>
</template>
