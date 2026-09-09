<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  user: { type: Object, default: null },
  roles: { type: Array, default: () => [] },
})
const emit = defineEmits(['close'])
const editing = computed(() => Boolean(props.user))
const description = computed(() =>
  editing.value
    ? 'Perbarui informasi akun, role, atau password pengguna.'
    : 'Tambahkan akun baru dan tentukan role akses awalnya.'
)
const form = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  password: '',
  password_confirmation: '',
  role: props.user?.roles?.[0]?.name ?? '',
})
const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') }
  editing.value
    ? form.put(route('users.update', props.user.id), options)
    : form.post(route('users.store'), options)
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="editing ? 'Edit Pengguna' : 'Tambah Pengguna'"
    :description="description"
    size="lg"
    @update:model-value="emit('close')"
  >
    <form id="user-form" class="space-y-5" @submit.prevent="submit">
      <Input
        v-model="form.name"
        label="Nama lengkap"
        placeholder="Masukkan nama lengkap"
        required
        :error="form.errors.name"
      />
      <Input
        v-model="form.email"
        label="Email"
        type="email"
        placeholder="nama@bisnis.com"
        required
        :error="form.errors.email"
      />
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
          >Role <span class="text-red-500">*</span></label
        ><select
          v-model="form.role"
          required
          class="w-full rounded-xl border-slate-300 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
        >
          <option value="" disabled>Pilih role pengguna</option>
          <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
        </select>
        <p v-if="form.errors.role" class="mt-1.5 text-sm font-medium text-red-500">
          {{ form.errors.role }}
        </p>
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <Input
          v-model="form.password"
          :label="editing ? 'Password baru' : 'Password'"
          type="password"
          :placeholder="editing ? 'Kosongkan jika tetap' : 'Minimal 8 karakter'"
          :required="!editing"
          :error="form.errors.password"
        /><Input
          v-model="form.password_confirmation"
          label="Konfirmasi password"
          type="password"
          placeholder="Ulangi password"
          :required="!editing"
        />
      </div>
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="user-form" :loading="form.processing">{{
        editing ? 'Simpan perubahan' : 'Tambah pengguna'
      }}</Button></template
    >
  </Modal>
</template>
