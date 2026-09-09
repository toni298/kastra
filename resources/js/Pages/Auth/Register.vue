<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' })

const submit = () => {
  form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') })
}
</script>

<template>
  <GuestLayout>
    <Head title="Daftar" />
    <div class="mb-8">
      <p class="text-sm font-medium uppercase tracking-[0.18em] text-emerald-600">
        Mulai bertumbuh
      </p>
      <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
        Buat akun Kastra
      </h1>
      <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
        Bangun fondasi operasional bisnis yang lebih rapi hari ini.
      </p>
    </div>
    <form class="space-y-4" @submit.prevent="submit">
      <Input
        id="name"
        v-model="form.name"
        label="Nama lengkap"
        type="text"
        placeholder="Nama Anda"
        autocomplete="name"
        required
        autofocus
        :error="form.errors.name"
      />
      <Input
        id="email"
        v-model="form.email"
        label="Email bisnis"
        type="email"
        placeholder="nama@bisnis.com"
        autocomplete="username"
        required
        :error="form.errors.email"
      />
      <Input
        id="password"
        v-model="form.password"
        label="Password"
        type="password"
        placeholder="Minimal 8 karakter"
        autocomplete="new-password"
        required
        :error="form.errors.password"
      />
      <Input
        id="password_confirmation"
        v-model="form.password_confirmation"
        label="Konfirmasi password"
        type="password"
        placeholder="Ulangi password"
        autocomplete="new-password"
        required
        :error="form.errors.password_confirmation"
      />
      <Button
        type="submit"
        size="lg"
        class="mt-2 w-full"
        :loading="form.processing"
        :disabled="form.processing"
        >Buat akun gratis</Button
      >
    </form>
    <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-300">
      Sudah punya akun?
      <Link
        :href="route('login')"
        class="font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
        >Masuk di sini</Link
      >
    </p>
  </GuestLayout>
</template>
