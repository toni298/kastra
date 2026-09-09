<script setup>
import { computed, ref } from 'vue'
import { UserPlus } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import { useForm } from '@inertiajs/vue3'
const props = defineProps({ item: { type: Object, default: null }, branchId: { type: String, default: '' } })
const emit = defineEmits(['close', 'saved'])
const submitted = ref(false)
const editing = computed(() => Boolean(props.item))
const form = useForm({
  branch_id: props.branchId,
  name: props.item?.name ?? '',
  telp: props.item?.telp ?? '',
  address: props.item?.address ?? '',
})
const valid = computed(() => Boolean(form.name && form.telp))
const submit = () => {
  submitted.value = true
  if (valid.value) {
    const options = { onSuccess: (page) => emit('saved', page.props.flash.customer) }
    editing.value ? form.put(route('sales.customers.update', props.item.id), options) : form.post(route('sales.customers.store'), options)
  }
}
</script>
<template>
  <Modal
    :model-value="true"
    :title="editing ? 'Edit Pelanggan' : 'Tambah Pelanggan'"
    description="Kelola informasi kontak dan status pelanggan."
    size="lg"
    @update:model-value="emit('close')"
    ><div
      class="mb-5 flex gap-3 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-300"
    >
      <UserPlus :size="19" />Data demo hanya disimpan sementara.
    </div>
    <form id="customer-form" class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
      <Input
        v-model="form.name"
        label="Nama Pelanggan"
        placeholder="Contoh: PT Maju Bersama"
        required
      /><Input
        v-model="form.telp"
        label="Telepon"
        placeholder="Contoh: 0812 3456 7890"
        required
      /><label class="sm:col-span-2"
        ><span class="text-sm font-medium">Alamat</span
        ><textarea
          v-model="form.address"
          rows="3"
          class="mt-1.5 w-full rounded-xl border-slate-300 dark:border-[#29476b] dark:bg-[#0a1b33]"
          placeholder="Alamat pelanggan"
        ></textarea>
      </label>
      <p
        v-if="submitted && !valid"
        class="rounded-xl bg-red-50 p-3 text-sm font-medium text-red-600 dark:bg-red-400/10 dark:text-red-300 sm:col-span-2"
      >
        Nama dan telepon wajib diisi.
      </p>
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="customer-form">{{
        editing ? 'Update' : 'Simpan'
      }}</Button></template
    ></Modal
  >
</template>
