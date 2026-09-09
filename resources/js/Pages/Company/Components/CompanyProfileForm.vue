<script setup>
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import Input from '@/Components/UI/Input.vue'
import { useAuthorization } from '@/Composables/useAuthorization'

const props = defineProps({ company: { type: Object, required: true } })
const { can } = useAuthorization()
const form = useForm({
  name: props.company.name ?? '',
  legal_name: props.company.legal_name ?? '',
  npwp: props.company.npwp ?? '',
  nib: props.company.nib ?? '',
  email: props.company.email ?? '',
  phone: props.company.phone ?? '',
  address: props.company.address ?? '',
  city: props.company.city ?? '',
  province: props.company.province ?? '',
  postal_code: props.company.postal_code ?? '',
  country_code: props.company.country_code ?? 'ID',
})

const submit = () => form.put(route('company.update', props.company.id), { preserveScroll: true })
</script>

<template>
  <form @submit.prevent="submit">
    <div class="space-y-5 p-5 sm:p-6">
      <div class="grid gap-4 sm:grid-cols-2">
        <Input v-model="form.name" label="Nama usaha" required :error="form.errors.name" />
        <Input v-model="form.legal_name" label="Nama legal" :error="form.errors.legal_name" />
        <Input v-model="form.npwp" label="NPWP (opsional)" :error="form.errors.npwp" />
        <Input v-model="form.nib" label="NIB (opsional)" :error="form.errors.nib" />
        <Input
          v-model="form.email"
          label="Email perusahaan"
          type="email"
          :error="form.errors.email"
        />
        <Input v-model="form.phone" label="Nomor telepon" :error="form.errors.phone" />
      </div>
      <Input v-model="form.address" label="Alamat" :error="form.errors.address" />
      <div class="grid gap-4 sm:grid-cols-3">
        <Input v-model="form.city" label="Kota" :error="form.errors.city" />
        <Input v-model="form.province" label="Provinsi" :error="form.errors.province" />
        <Input v-model="form.postal_code" label="Kode pos" :error="form.errors.postal_code" />
      </div>
    </div>
    <FormActions>
      <Button v-if="can('company.edit')" type="submit" :loading="form.processing"
        >Simpan Profil</Button
      >
    </FormActions>
  </form>
</template>
