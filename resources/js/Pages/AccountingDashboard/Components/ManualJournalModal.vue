<script setup>
import { reactive, ref } from 'vue'
import { BookPlus } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
const emit = defineEmits(['close']),
  submitted = ref(false),
  form = reactive({
    date: '20/07/2026',
    reference: '',
    description: '',
    debitAccount: '',
    creditAccount: '',
    amount: '',
  })
const submit = () => {
  submitted.value = true
  if (form.date && form.debitAccount && form.creditAccount && form.amount) emit('close')
}
const select =
  'mt-1.5 w-full rounded-xl border-slate-300 px-3.5 py-3 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white'
</script>
<template>
  <Modal
    :model-value="true"
    title="Jurnal Manual"
    description="Gunakan hanya untuk penyesuaian yang tidak berasal dari transaksi bisnis."
    size="xl"
    @update:model-value="emit('close')"
    ><div
      class="mb-5 flex gap-3 rounded-xl bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-400/10 dark:text-amber-300"
    >
      <BookPlus :size="20" />
      <p>Kastra membuat jurnal otomatis. Jurnal manual hanya diperlukan untuk kondisi khusus.</p>
    </div>
    <form id="journal-form" class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
      <Input v-model="form.date" label="Tanggal" required /><Input
        v-model="form.reference"
        label="Referensi"
        placeholder="Opsional"
      /><label
        ><span class="text-sm font-medium dark:text-slate-200">Akun Debit *</span
        ><select v-model="form.debitAccount" :class="select">
          <option value="">Pilih akun</option>
          <option>Kas & Bank</option>
          <option>Beban Operasional</option>
        </select></label
      ><label
        ><span class="text-sm font-medium dark:text-slate-200">Akun Kredit *</span
        ><select v-model="form.creditAccount" :class="select">
          <option value="">Pilih akun</option>
          <option>Pendapatan Lain</option>
          <option>Hutang Usaha</option>
        </select></label
      ><Input v-model="form.amount" type="number" min="1" label="Nominal" required /><label
        ><span class="text-sm font-medium dark:text-slate-200">Deskripsi</span
        ><textarea v-model="form.description" rows="3" :class="select"></textarea>
      </label>
      <p
        v-if="
          submitted && (!form.date || !form.debitAccount || !form.creditAccount || !form.amount)
        "
        class="rounded-xl bg-red-50 p-3 text-sm font-medium text-red-600 dark:bg-red-400/10 dark:text-red-300 sm:col-span-2"
      >
        Lengkapi semua field wajib.
      </p>
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="journal-form">Simpan Jurnal</Button></template
    ></Modal
  >
</template>
