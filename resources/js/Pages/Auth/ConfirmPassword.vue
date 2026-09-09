<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const form = useForm({
  password: '',
})

const submit = () => {
  form.post(route('password.confirm'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <GuestLayout>
    <Head title="Konfirmasi Password" />

    <div class="mb-8">
      <p class="text-sm font-medium uppercase tracking-[0.18em] text-emerald-600">
        Verifikasi keamanan
      </p>
      <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
        Konfirmasi password Anda
      </h1>
      <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
        Anda akan mengakses area sensitif. Masukkan kembali password untuk melanjutkan dengan aman.
      </p>
    </div>

    <form class="space-y-5" @submit.prevent="submit">
      <Input
        id="password"
        v-model="form.password"
        label="Password"
        type="password"
        placeholder="Masukkan password Anda"
        autocomplete="current-password"
        required
        autofocus
        :error="form.errors.password"
      />

      <Button
        type="submit"
        size="lg"
        class="w-full"
        :loading="form.processing"
        :disabled="form.processing"
      >
        Konfirmasi dan lanjutkan
      </Button>
    </form>
  </GuestLayout>
</template>