<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const props = defineProps({
  status: { type: String, default: '' },
})

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(route('password.email'))
}
</script>

<template>
  <GuestLayout>
    <Head title="Lupa Password" />

    <div class="mb-8">
      <p class="text-sm font-medium uppercase tracking-[0.18em] text-emerald-600">
        Pemulihan akun
      </p>
      <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
        Lupa password?
      </h1>
      <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
        Masukkan alamat email bisnis Anda. Kami akan mengirimkan tautan untuk membuat password baru.
      </p>
    </div>

    <div
      v-if="props.status"
      class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300"
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

      <Button
        type="submit"
        size="lg"
        class="w-full"
        :loading="form.processing"
        :disabled="form.processing"
      >
        Kirim tautan reset password
      </Button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-300">
      Sudah ingat password?
      <Link
        :href="route('login')"
        class="font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
      >
        Kembali masuk
      </Link>
    </p>
  </GuestLayout>
</template>