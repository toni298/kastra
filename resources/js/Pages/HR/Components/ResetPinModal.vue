<script setup>
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({ employee: { type: Object, required: true } })
const emit = defineEmits(['close'])
const form = useForm({ pin: '' })
const submit = () =>
  form.post(route('hr.employees.reset-pin', props.employee.id), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  })
</script>

<template>
  <Modal
    :model-value="true"
    title="Reset PIN Karyawan"
    :description="`Buat PIN baru untuk ${employee.name}. PIN disimpan secara aman dalam bentuk hash.`"
    size="sm"
    @update:model-value="emit('close')"
  >
    <form id="reset-pin-form" class="space-y-4" @submit.prevent="submit">
      <Input
        v-model="form.pin"
        label="PIN baru"
        type="password"
        inputmode="numeric"
        maxlength="6"
        required
        :error="form.errors.pin"
      />
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="reset-pin-form" :loading="form.processing"
        >Simpan PIN</Button
      ></template
    >
  </Modal>
</template>
