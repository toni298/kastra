<script setup>
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  supplier: { type: Object, default: null },
})

const emit = defineEmits(['close'])

const editing = Boolean(props.supplier)
const form = useForm({
  name: props.supplier?.name ?? '',
  contact_supplier: props.supplier?.contact_supplier ?? '',
  email: props.supplier?.email ?? '',
  address: props.supplier?.address ?? '',
})

const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') }
  editing
    ? form.put(route('suppliers.update', props.supplier.id), options)
    : form.post(route('suppliers.store'), options)
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="editing ? 'Edit Supplier' : 'Tambah Supplier'"
    description="Kelola informasi utama dan kontak supplier."
    size="xl"
    @update:model-value="emit('close')"
  >
    <form id="supplier-form" class="space-y-5" @submit.prevent="submit">
      <div class="grid gap-4 sm:grid-cols-2">
        <Input
          v-model="form.name"
          label="Nama Supplier"
          placeholder="Contoh: PT Sumber Makmur"
          required
          :error="form.errors.name"
        />
        <Input
          v-model="form.contact_supplier"
          type="tel"
          label="Kontak Supplier"
          placeholder="Contoh: 0812 3456 7890"
          inputmode="tel"
          autocomplete="tel"
          required
          :error="form.errors.contact_supplier"
        />
        <Input
          v-model="form.email"
          type="email"
          label="Email (opsional)"
          placeholder="Contoh: supplier@perusahaan.com"
          inputmode="email"
          autocomplete="email"
          :error="form.errors.email"
        />
      </div>

      <div>
        <label
          for="supplier-address"
          class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
          >Alamat <span class="text-red-500">*</span></label
        >
        <textarea
          id="supplier-address"
          v-model="form.address"
          rows="3"
          placeholder="Contoh: Jl. Sudirman No. 10, Jakarta Pusat"
          autocomplete="street-address"
          required
          class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
        ></textarea>
        <p v-if="form.errors.address" class="mt-1.5 text-sm font-medium text-red-500">
          {{ form.errors.address }}
        </p>
      </div>
    </form>

    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button type="submit" form="supplier-form" :loading="form.processing">
        {{ editing ? 'Simpan perubahan' : 'Tambah Supplier' }}
      </Button>
    </template>
  </Modal>
</template>
