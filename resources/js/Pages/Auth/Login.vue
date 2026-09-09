<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Checkbox from '@/Components/Checkbox.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const props = defineProps({
  canResetPassword: { type: Boolean, default: false },
  status: { type: String, default: '' },
})

const form = useForm({ email: '', password: '', remember: false })

const submit = () => {
  form.post(route('login'), { onFinish: () => form.reset('password') })
}
</script>

<template>
  <GuestLayout>
    <Head title="Masuk" />
    <div class="mb-8">
      <p class="text-sm font-medium uppercase tracking-[0.18em] text-emerald-600">
        Selamat datang kembali
      </p>
      <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
        Masuk ke Kastra
      </h1>
      <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
        Lanjutkan mengelola bisnis Anda dengan lebih terarah.
      </p>
    </div>
    <div
      v-if="props.status"
      class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
    >
      {{ props.status }}
    </div>
    <form class="space-y-5" @submit.prevent="submit">
      <Input
        id="email"
        v-model="form.email"
        label="Email bisnis"
        type="email"
        placeholder="nama@bisnis.com"
        autocomplete="username"
        required
        autofocus
        :error="form.errors.email"
      />
      <Input
        id="password"
        v-model="form.password"
        label="Password"
        type="password"
        placeholder="Masukkan password Anda"
        autocomplete="current-password"
        required
        :error="form.errors.password"
      />
      <div class="flex items-center justify-between">
        <label
          class="flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-300"
          ><Checkbox v-model:checked="form.remember" name="remember" /> Ingat saya</label
        ><Link
          v-if="props.canResetPassword"
          :href="route('password.request')"
          class="text-sm font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
          >Lupa password?</Link
        >
      </div>
      <Button
        type="submit"
        size="lg"
        class="w-full"
        :loading="form.processing"
        :disabled="form.processing"
        >Masuk ke Kastra</Button
      >
    </form>
    <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-300">
      Belum punya akun?
      <Link
        :href="route('register')"
        class="font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
        >Daftar sekarang</Link
      >
    </p>
  </GuestLayout>
</template>
