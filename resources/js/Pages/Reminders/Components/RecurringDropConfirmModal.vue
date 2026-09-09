<script setup>
import { computed, ref } from 'vue'
import { Check, X } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  drop: { type: Object, required: true },
})

const emit = defineEmits(['cancel', 'apply'])
const scope = ref('this')

const options = [
  {
    value: 'this',
    label: 'Hanya event tanggal ini',
    description: 'Pisahkan instance ini dan buat event tunggal di tanggal tujuan.',
  },
  {
    value: 'future',
    label: 'Event ini dan yang akan datang',
    description: 'Potong rangkaian lama lalu buat rangkaian baru dari tanggal tujuan.',
  },
  {
    value: 'all',
    label: 'Semua event dalam rangkaian',
    description: 'Geser seluruh rangkaian dengan mempertahankan jadwal perulangan.',
  },
]

const originalDate = computed(() =>
  new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' }).format(new Date(props.drop.originalStart))
)
const targetDate = computed(() =>
  new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' }).format(new Date(props.drop.targetStart))
)
</script>

<template>
  <Modal
    :model-value="true"
    title="Pindahkan event berulang"
    description="Pilih cakupan perubahan jadwal"
    size="lg"
    @update:model-value="emit('cancel')"
  >
    <div class="space-y-4">
      <div
        class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
      >
        <p class="text-slate-500 dark:text-slate-400">{{ originalDate }}</p>
        <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ props.drop.title }}</p>
        <p class="mt-1 text-emerald-600 dark:text-emerald-400">Dipindahkan ke {{ targetDate }}</p>
      </div>

      <div class="space-y-2">
        <label
          v-for="option in options"
          :key="option.value"
          class="flex cursor-pointer gap-3 rounded-xl border p-4 transition"
          :class="
            scope === option.value
              ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10'
              : 'border-slate-200 hover:bg-slate-50 dark:border-[#29476b] dark:hover:bg-[#163354]'
          "
        >
          <input
            v-model="scope"
            class="mt-1 accent-emerald-600"
            type="radio"
            name="recurring-drop-scope"
            :value="option.value"
          />
          <span>
            <span class="block font-medium text-slate-900 dark:text-white">{{ option.label }}</span>
            <span class="mt-1 block text-sm text-slate-500 dark:text-slate-400">{{
              option.description
            }}</span>
          </span>
        </label>
      </div>
    </div>

    <template #footer>
      <Button variant="secondary" @click="emit('cancel')"><X :size="16" />Batal</Button>
      <Button @click="emit('apply', scope)"><Check :size="16" />Terapkan Perubahan</Button>
    </template>
  </Modal>
</template>
