<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const props = defineProps({
  status: { type: String, default: '' },
})

const form = useForm({})

const verificationLinkSent = computed(() => props.status === 'verification-link-sent')

const submit = () => {
  form.post(route('verification.send'))
}
</script>

<template>
  <GuestLayout>
    <Head title="Verifikasi Email" />

    <div class="mb-8">
      <p class="text-sm font-medium uppercase tracking-[0.18em] text-emerald-600">
        Satu langkah lagi
      </p>
      <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
        Verifikasi email Anda
      </h1>
      <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
        Kami telah mengirimkan tautan verifikasi ke alamat email Anda. Buka tautan tersebut untuk
        mengaktifkan akun dan mulai menggunakan Kastra.
      </p>
    </div>

    <div
      v-if="verificationLinkSent"
      class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300"
    >
      Tautan verifikasi baru telah dikirim ke alamat email yang Anda gunakan saat mendaftar.
    </div>

    <form class="space-y-5" @submit.prevent="submit">
      <Button
        type="submit"
        size="lg"
        class="w-full"
        :loading="form.processing"
        :disabled="form.processing"
      >
        Kirim ulang email verifikasi
      </Button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-300">
      Ingin menggunakan akun lain?
      <Link
        :href="route('logout')"
        method="post"
        as="button"
        class="font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
      >
        Keluar
      </Link>
    </p>
  </GuestLayout>
</template>