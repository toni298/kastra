<script setup>
import { useForm } from '@inertiajs/vue3'
import { ArrowDownToLine, ArrowUpFromLine, Check, Trash2, X } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  reminder: { type: Object, required: true },
})

const emit = defineEmits(['close', 'saved', 'delete'])

const formatDate = (value) => {
  if (!value) return ''
  const text = String(value)
  if (/^\d{4}-\d{2}-\d{2}/.test(text)) return text.slice(0, 10)

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const selectedDate = formatDate(
  props.reminder.due_date || props.reminder.start || props.reminder.occurrence_start
)
const occurrenceStart = formatDate(
  props.reminder.occurrence_start || props.reminder.due_date || props.reminder.start
)
const form = useForm({
  title: props.reminder.name || props.reminder.title || '',
  start: selectedDate,
  type_transcation: props.reminder.type_transcation || 'out',
  amount: props.reminder.amount ?? 0,
  notes: props.reminder.notes || '',
})

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      type: 'once',
      scope: 'this',
      occurrence_start: occurrenceStart,
      end: null,
      all_day: true,
      status: props.reminder.status || 'pending',
    }))
    .put(route('reminders.update', props.reminder.id), {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
        emit('close')
      },
    })
}
</script>

<template>
  <Modal
    :model-value="true"
    title="Detail Reminder"
    description="Kelola informasi dan jadwal reminder"
    size="lg"
    @update:model-value="emit('close')"
  >
    <form class="space-y-5" @submit.prevent="submit">
      <Input
        v-model="form.title"
        label="Judul Event"
        placeholder="Contoh: Bayar sewa kantor"
        required
        :error="form.errors.title"
      />

      <DatePicker
        v-model="form.start"
        label="Tanggal Mulai"
        placeholder="DD/MM/YYYY"
        required
        :error="form.errors.start"
      />

      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
          Jenis Transaksi <span class="text-red-500">*</span>
        </label>
        <div class="grid grid-cols-2 gap-2">
          <button
            type="button"
            class="flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-sm font-medium transition"
            :class="
              form.type_transcation === 'in'
                ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
                : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-[#29476b] dark:text-slate-300 dark:hover:bg-[#163354]'
            "
            @click="form.type_transcation = 'in'"
          >
            <ArrowDownToLine :size="16" />Penerimaan
          </button>
          <button
            type="button"
            class="flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-sm font-medium transition"
            :class="
              form.type_transcation === 'out'
                ? 'border-red-500 bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400'
                : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-[#29476b] dark:text-slate-300 dark:hover:bg-[#163354]'
            "
            @click="form.type_transcation = 'out'"
          >
            <ArrowUpFromLine :size="16" />Pengeluaran
          </button>
        </div>
        <p v-if="form.errors.type_transcation" class="mt-1.5 text-sm text-red-500">
          {{ form.errors.type_transcation }}
        </p>
      </div>

      <CurrencyInput
        v-model="form.amount"
        label="Nominal"
        placeholder="0"
        required
        :error="form.errors.amount"
      />

      <Input
        v-model="form.notes"
        label="Catatan"
        placeholder="Tambahkan catatan (opsional)"
        :error="form.errors.notes"
      />
    </form>

    <template #footer>
      <Button variant="danger" @click="emit('delete', { ...props.reminder, deleteScope: 'this' })">
        Hapus
      </Button>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button :disabled="form.processing" @click="submit">
        Simpan Perubahan
      </Button>
    </template>
  </Modal>
</template>
