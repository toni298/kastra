<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const props = defineProps({
  email: { type: String, required: true },
  token: { type: String, required: true },
})

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <GuestLayout>
    <Head title="Atur Ulang Password" />

    <div class="mb-8">
      <p class="text-sm font-medium uppercase tracking-[0.18em] text-emerald-600">
        Pemulihan akun
      </p>
      <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
        Buat password baru
      </h1>
      <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
        Gunakan password yang kuat dan mudah Anda ingat untuk mengamankan akun Kastra.
      </p>
    </div>

    <form class="space-y-5" @submit.prevent="submit">
      <Input
        id="email"
        v-model="form.email"
        label="Email bisnis"
        type="email"
        autocomplete="username"
        required
        autofocus
        :error="form.errors.email"
      />

      <Input
        id="password"
        v-model="form.password"
        label="Password baru"
        type="password"
        placeholder="Masukkan password baru"
        autocomplete="new-password"
        required
        :error="form.errors.password"
      />

      <Input
        id="password_confirmation"
        v-model="form.password_confirmation"
        label="Konfirmasi password baru"
        type="password"
        placeholder="Ulangi password baru"
        autocomplete="new-password"
        required
        :error="form.errors.password_confirmation"
      />

      <Button
        type="submit"
        size="lg"
        class="w-full"
        :loading="form.processing"
        :disabled="form.processing"
      >
        Simpan password baru
      </Button>
    </form>
  </GuestLayout>
</template>