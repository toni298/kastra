<script setup>
import { computed, reactive, watch } from 'vue'
import { Save } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  account: { type: Object, default: null },
  groups: { type: Array, default: () => [] },
})
const emit = defineEmits(['close', 'submit'])
const editing = computed(() => Boolean(props.account))
const form = reactive({
  groupId: 'aset',
  name: '',
  code: '',
  parent: '',
  type: '',
  normal: 'Debit',
  status: 'Aktif',
})
const errors = reactive({})
const fillForm = () => {
  Object.assign(form, {
    groupId: props.account?.groupId ?? 'aset',
    name: props.account?.name ?? '',
    code: props.account?.code ?? '',
    parent: props.account?.parent ?? '',
    type: props.account?.type ?? '',
    normal: props.account?.normal ?? 'Debit',
    status: props.account?.status ?? 'Aktif',
  })
  Object.keys(errors).forEach((key) => delete errors[key])
}
const submit = () => {
  Object.keys(errors).forEach((key) => delete errors[key])
  if (!form.name.trim()) errors.name = 'Nama akun wajib diisi.'
  if (!form.code.trim()) errors.code = 'Kode akun wajib diisi.'
  if (!form.parent.trim()) errors.parent = 'Parent akun wajib diisi.'
  if (!form.type.trim()) errors.type = 'Tipe akun wajib diisi.'
  if (Object.keys(errors).length) return
  emit('submit', {
    ...form,
    name: form.name.trim(),
    code: form.code.trim(),
    parent: form.parent.trim(),
    type: form.type.trim(),
  })
}
watch(() => props.account, fillForm, { immediate: true })
</script>

<template>
  <Modal
    :model-value="true"
    :title="editing ? 'Edit Akun' : 'Tambah Akun'"
    :description="
      editing
        ? 'Perbarui identitas dan pemetaan akun.'
        : 'Tambahkan akun ke struktur Chart of Accounts.'
    "
    size="xl"
    @update:model-value="emit('close')"
  >
    <form id="coa-form" class="space-y-5" @submit.prevent="submit">
      <div class="grid gap-4 sm:grid-cols-2">
        <Input
          v-model="form.name"
          label="Nama akun"
          placeholder="Contoh: Kas Kecil"
          required
          :error="errors.name"
        />
        <Input
          v-model="form.code"
          label="Kode akun"
          placeholder="Contoh: 1-1002"
          required
          :error="errors.code"
        />
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
            >Kelompok <span class="text-red-500">*</span></label
          ><select
            v-model="form.groupId"
            class="w-full rounded-xl border-slate-300 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option v-for="group in groups" :key="group.id" :value="group.id">
              {{ group.name }}
            </option>
          </select>
        </div>
        <Input
          v-model="form.parent"
          label="Parent akun"
          placeholder="Contoh: Aset Lancar"
          required
          :error="errors.parent"
        />
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <Input
          v-model="form.type"
          label="Tipe akun"
          placeholder="Contoh: Akun Kas"
          required
          :error="errors.type"
        />
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
            >Saldo normal</label
          ><select
            v-model="form.normal"
            class="w-full rounded-xl border-slate-300 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option>Debit</option>
            <option>Kredit</option>
          </select>
        </div>
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
          >Status akun</label
        ><select
          v-model="form.status"
          class="w-full rounded-xl border-slate-300 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
        >
          <option>Aktif</option>
          <option>Nonaktif</option>
        </select>
      </div>
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="coa-form"
        ><Save :size="16" class="mr-2" />{{ editing ? 'Simpan perubahan' : 'Tambah akun' }}</Button
      ></template
    >
  </Modal>
</template>
