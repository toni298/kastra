<script setup>
import { CalendarDays, Repeat, Trash2 } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import { useReminderForm } from '../Composables/useReminderForm'

const props = defineProps({
  date: { type: Date, required: true },
  reminder: { type: Object, default: null },
})

const emit = defineEmits(['close', 'saved', 'delete'])
const {
  form,
  editing,
  isRecurring,
  showRecurrenceEnd,
  scope,
  frequencyTypes,
  transactionTypes,
  previewDates,
  formatPreviewDate,
  submit,
  modalTitle,
  modalDescription,
} = useReminderForm(props, emit)

const requestDelete = () => emit('delete', { ...props.reminder, deleteScope: scope.value })
</script>

<template>
  <Modal
    :model-value="true"
    :title="modalTitle"
    :description="modalDescription"
    size="xl"
    @update:model-value="emit('close')"
  >
    <form id="reminder-quick-form" class="space-y-5" @submit.prevent="submit">
      <Input
        v-model="form.title"
        label="Judul Event"
        placeholder="Contoh: Bayar sewa kantor"
        required
        :error="form.errors.title"
      />

      <div class="grid gap-4 sm:grid-cols-2">
        <DatePicker
          v-model="form.start"
          label="Tanggal Mulai"
          placeholder="DD/MM/YYYY"
          required
          :error="form.errors.start"
        />
        <DatePicker
          v-if="isRecurring"
          v-model="form.end"
          label="Tanggal Selesai"
          placeholder="DD/MM/YYYY"
          :error="form.errors.end"
        />
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
            Jenis Transaksi <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-2">
            <button
              v-for="transaction in transactionTypes"
              :key="transaction.value"
              type="button"
              class="rounded-xl border px-3 py-2.5 text-sm font-medium transition"
              :class="
                form.type_transcation === transaction.value
                  ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
                  : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-[#29476b] dark:text-slate-300 dark:hover:bg-[#163354]'
              "
              @click="form.type_transcation = transaction.value"
            >
              {{ transaction.label }}
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
      </div>

      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
          Frekuensi <span class="text-red-500">*</span>
        </label>
        <div class="space-y-2">
          <button
            v-for="freq in frequencyTypes"
            :key="freq.value"
            type="button"
            class="flex w-full items-start gap-3 rounded-xl border p-3 text-left transition"
            :class="
              form.type === freq.value
                ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10'
                : 'border-slate-200 hover:bg-slate-50 dark:border-[#29476b] dark:hover:bg-[#163354]'
            "
            @click="form.type = freq.value"
          >
            <div
              class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full border-2"
              :class="
                form.type === freq.value
                  ? 'border-emerald-500 bg-emerald-500'
                  : 'border-slate-300 dark:border-[#29476b]'
              "
            >
              <div v-if="form.type === freq.value" class="size-2 rounded-full bg-white"></div>
            </div>
            <div class="min-w-0 flex-1">
              <p
                class="font-medium"
                :class="
                  form.type === freq.value
                    ? 'text-emerald-700 dark:text-emerald-400'
                    : 'text-slate-900 dark:text-white'
                "
              >
                {{ freq.label }}
              </p>
              <p class="text-sm text-slate-500">{{ freq.description }}</p>
            </div>
            <Repeat v-if="freq.value !== 'once'" :size="18" class="shrink-0 text-slate-400" />
          </button>
        </div>
        <p v-if="form.errors.type" class="mt-1.5 text-sm font-medium text-red-500">
          {{ form.errors.type }}
        </p>
      </div>

      <div
        v-if="editing && isRecurring"
        class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/20 dark:bg-amber-500/10"
      >
        <p class="mb-2 text-sm font-medium text-amber-800 dark:text-amber-300">Event berulang</p>
        <div
          class="flex flex-col gap-2 text-sm text-amber-900 dark:text-amber-200 sm:flex-row sm:gap-5"
        >
          <label class="flex items-center gap-2">
            <input v-model="scope" type="radio" value="this" />
            Hanya event tanggal ini
          </label>
          <label class="flex items-center gap-2">
            <input v-model="scope" type="radio" value="future" />
            Event ini dan yang akan datang
          </label>
          <label class="flex items-center gap-2">
            <input v-model="scope" type="radio" value="all" />
            Semua event dalam rangkaian
          </label>
        </div>
      </div>

      <div
        v-if="isRecurring"
        class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-[#29476b] dark:bg-[#0a1b33]"
      >
        <button
          type="button"
          class="flex w-full items-center justify-between text-sm font-medium text-slate-700 dark:text-slate-200"
          @click="showRecurrenceEnd = !showRecurrenceEnd"
        >
          <span>Batasi akhir pengulangan (opsional)</span>
        </button>
        <div v-if="showRecurrenceEnd" class="mt-3">
          <div class="mb-3 flex gap-2">
            <label
              v-for="mode in [
                { value: 'forever', label: 'Selamanya' },
                { value: 'until', label: 'Sampai' },
                { value: 'count', label: 'Jumlah' },
              ]"
              :key="mode.value"
              class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300"
            >
              <input v-model="form.recurrence_end_mode" type="radio" :value="mode.value" />{{
                mode.label
              }}
            </label>
          </div>
          <DatePicker
            v-if="form.recurrence_end_mode === 'until'"
            v-model="form.recurrence_until"
            label="Berakhir pada"
            placeholder="DD/MM/YYYY"
            :error="form.errors.recurrence_until"
          />
          <Input
            v-if="form.recurrence_end_mode === 'count'"
            v-model="form.recurrence_count"
            type="number"
            min="1"
            label="Jumlah pengulangan"
            :error="form.errors.recurrence_count"
          />
        </div>
      </div>

      <div
        v-if="isRecurring && previewDates.length"
        class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10"
      >
        <div
          class="flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-400"
        >
          <CalendarDays :size="16" /><span>Jadwal Berikutnya</span>
        </div>
        <div class="mt-2 flex flex-wrap gap-2">
          <span
            v-for="(previewDate, index) in previewDates"
            :key="index"
            class="rounded-full bg-white px-3 py-1 text-sm font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300"
          >
            {{ formatPreviewDate(previewDate) }}
          </span>
        </div>
      </div>

      <div>
        <label
          for="reminder-notes"
          class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
          >Catatan (opsional)</label
        >
        <textarea
          id="reminder-notes"
          v-model="form.notes"
          rows="3"
          placeholder="Catatan tambahan..."
          class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
        ></textarea>
        <p v-if="form.errors.notes" class="mt-1.5 text-sm font-medium text-red-500">
          {{ form.errors.notes }}
        </p>
      </div>
    </form>

    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button v-if="editing" type="button" variant="danger" @click="requestDelete">
        <Trash2 :size="16" />Hapus
      </Button>
      <Button type="submit" form="reminder-quick-form" :loading="form.processing">
        {{ editing ? 'Simpan Perubahan' : 'Tambah Event' }}
      </Button>
    </template>
  </Modal>
</template>
