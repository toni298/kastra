<script setup>
import { computed, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { UserPlus } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({ item: { type: Object, default: null } })
const emit = defineEmits(['close', 'saved'])
const submitted = ref(false)
const editing = computed(() => Boolean(props.item))
const page = usePage()
const form = useForm({
  name: props.item?.name ?? '',
  contact_supplier: props.item?.contact_supplier ?? props.item?.phone ?? '',
  email: props.item?.email ?? '',
  address: props.item?.address ?? '',
})
const valid = computed(() => Boolean(form.name.trim() && form.contact_supplier.trim() && form.address.trim()))
const submit = () => {
  submitted.value = true
  if (!valid.value || form.processing) return
  form.submit(editing.value ? 'put' : 'post', editing.value ? route('purchases.suppliers.update', props.item.id) : route('purchases.suppliers.store'), {
    preserveScroll: true,
    onSuccess: () => emit('saved', page.props.flash?.supplier ?? null),
  })
}
</script>

<template>
  <Modal :model-value="true" :title="editing ? 'Edit Supplier' : 'Tambah Supplier'" description="Kelola informasi kontak supplier." size="lg" @update:model-value="emit('close')">
    <div class="mb-5 flex gap-3 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300"><UserPlus :size="19" />Data supplier akan disimpan ke database.</div>
    <form id="supplier-form" class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
      <div><Input v-model="form.name" label="Nama Supplier" placeholder="Contoh: PT Maju Bersama" required :error="form.errors.name" /><p v-if="submitted && !form.name.trim()" class="mt-1 text-sm text-red-500">Nama supplier wajib diisi.</p></div>
      <div><Input v-model="form.contact_supplier" label="Telepon" placeholder="Contoh: 0812 3456 7890" required :error="form.errors.contact_supplier" /><p v-if="submitted && !form.contact_supplier.trim()" class="mt-1 text-sm text-red-500">Telepon supplier wajib diisi.</p></div>
      <Input v-model="form.email" type="email" label="Email (Opsional)" placeholder="nama@perusahaan.id" :error="form.errors.email" />
      <label class="sm:col-span-2"><span class="text-sm font-medium text-slate-700 dark:text-slate-200">Alamat <span class="text-red-500">*</span></span><textarea v-model="form.address" rows="3" class="mt-1.5 w-full rounded-xl border-slate-300 dark:border-[#29476b] dark:bg-[#0a1b33]" placeholder="Alamat supplier"></textarea><p v-if="submitted && !form.address.trim()" class="mt-1 text-sm text-red-500">Alamat supplier wajib diisi.</p><p v-else-if="form.errors.address" class="mt-1 text-sm text-red-500">{{ form.errors.address }}</p></label>
    </form>
    <template #footer><Button variant="secondary" :disabled="form.processing" @click="emit('close')">Batal</Button><Button type="submit" form="supplier-form" :loading="form.processing">{{ editing ? 'Update' : 'Simpan' }}</Button></template>
  </Modal>
</template>
