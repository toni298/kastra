<script setup>
import { Building2, Check, UserRound } from 'lucide-vue-next'

defineProps({
  modelValue: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const options = [
  {
    value: 'perorangan',
    label: 'Usaha perorangan',
    title: 'Perorangan',
    description: 'Usaha dimiliki dan dijalankan atas nama pribadi tanpa badan hukum terpisah.',
    recommendation: 'Praktis untuk usaha mandiri',
    examples: 'UMKM, toko, jasa, dan usaha rumahan',
    benefit: 'Setup ringkas dengan identitas usaha sederhana.',
    requirements: ['Identitas pemilik usaha', 'Nama dan alamat usaha'],
    icon: UserRound,
  },
  {
    value: 'perusahaan',
    label: 'Usaha berbadan hukum',
    title: 'Perusahaan',
    description: 'Usaha berbadan hukum dengan identitas legal yang terpisah dari pemilik.',
    recommendation: 'Tepat untuk organisasi formal',
    examples: 'PT, CV, koperasi, dan yayasan',
    benefit: 'Siap mendukung identitas legal dan struktur organisasi.',
    requirements: ['Dokumen legal badan usaha', 'Nama dan alamat perusahaan'],
    icon: Building2,
  },
]
</script>

<template>
  <section class="mx-auto max-w-[960px] lg:flex lg:h-full lg:min-h-0 lg:flex-col">
    <header class="max-w-2xl">
      <span
        class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium uppercase tracking-[0.12em] text-emerald-700"
      >
        Fondasi Profil Bisnis
      </span>
      <h1
        class="mt-3 text-3xl font-semibold leading-tight tracking-[-0.03em] text-slate-950 sm:text-[35px]"
      >
        Pilih Jenis Usaha
      </h1>
      <p class="mt-2 max-w-[680px] text-[16px] leading-6 text-slate-500">
        Pilih berdasarkan status legal usaha saat ini. Kastra akan menyesuaikan struktur profil dan
        informasi bisnis pada langkah berikutnya.
      </p>
    </header>

    <fieldset class="mt-6 lg:flex lg:min-h-0 lg:flex-1 lg:flex-col">
      <legend class="sr-only">Jenis usaha</legend>
      <div class="grid gap-4 md:grid-cols-2">
        <label
          v-for="option in options"
          :key="option.value"
          class="group relative cursor-pointer rounded-[32px] focus-within:outline-none"
        >
          <input
            class="peer sr-only"
            type="radio"
            name="business_type"
            :value="option.value"
            :checked="modelValue === option.value"
            @change="emit('update:modelValue', option.value)"
          />

          <span
            :class="[
              'relative flex h-full flex-col overflow-hidden rounded-[32px] border p-6 transition-all duration-300 peer-focus-visible:ring-4 peer-focus-visible:ring-emerald-500/20 lg:p-5 xl:p-6',
              modelValue === option.value
                ? 'border-emerald-500 bg-gradient-to-br from-emerald-50 via-white to-emerald-50/60 shadow-[0_18px_45px_rgba(5,150,105,0.14)] ring-1 ring-emerald-500'
                : 'border-slate-200 bg-white shadow-[0_8px_24px_rgba(15,23,42,0.05)] hover:-translate-y-1 hover:border-emerald-300 hover:shadow-[0_18px_40px_rgba(15,23,42,0.10)]',
            ]"
          >
            <span
              class="pointer-events-none absolute -right-16 -top-20 h-44 w-44 rounded-full bg-emerald-100/60 blur-2xl transition-opacity duration-300"
              :class="
                modelValue === option.value ? 'opacity-100' : 'opacity-0 group-hover:opacity-70'
              "
            ></span>

            <span class="relative flex items-start gap-3.5">
              <span class="flex min-w-0 flex-1 items-center gap-3.5">
                <span
                  :class="[
                    'grid h-12 w-12 shrink-0 place-items-center rounded-2xl border transition-all duration-300',
                    modelValue === option.value
                      ? 'border-emerald-600 bg-emerald-600 text-white shadow-lg shadow-emerald-600/20'
                      : 'border-emerald-100 bg-emerald-50 text-emerald-700 group-hover:border-emerald-200 group-hover:bg-emerald-100',
                  ]"
                >
                  <component :is="option.icon" class="h-6 w-6" stroke-width="1.8" />
                </span>
                <span class="min-w-0">
                  <span
                    class="block text-[12px] font-medium uppercase tracking-[0.1em] text-emerald-700"
                  >
                    {{ option.label }}
                  </span>
                  <span class="mt-0.5 block text-2xl font-semibold tracking-tight text-slate-950">
                    {{ option.title }}
                  </span>
                </span>
              </span>

              <span
                :class="[
                  'grid h-7 w-7 place-items-center rounded-full border-2 transition-all duration-200',
                  modelValue === option.value
                    ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                    : 'border-slate-300 bg-white group-hover:border-emerald-400',
                ]"
                aria-hidden="true"
              >
                <Check v-if="modelValue === option.value" class="h-3.5 w-3.5" stroke-width="3" />
              </span>
            </span>

            <span class="relative mt-3 block text-sm leading-5 text-slate-600">
              {{ option.description }}
            </span>

            <span
              class="relative mt-3 flex items-start gap-2.5 text-sm font-semibold text-slate-700"
            >
              <span
                class="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700"
              >
                <Check class="h-3 w-3" stroke-width="3" />
              </span>
              {{ option.benefit }}
            </span>

            <span class="relative mt-3 block rounded-2xl bg-slate-50 px-3.5 py-3">
              <span
                class="block text-[12px] font-medium uppercase tracking-[0.08em] text-slate-500"
              >
                Syarat pengajuan
              </span>
              <span class="mt-2 grid gap-1.5 sm:grid-cols-2">
                <span
                  v-for="requirement in option.requirements"
                  :key="requirement"
                  class="flex items-start gap-2 text-xs font-semibold leading-4 text-slate-700"
                >
                  <Check class="mt-0.5 h-3 w-3 shrink-0 text-emerald-600" stroke-width="3" />
                  {{ requirement }}
                </span>
              </span>
            </span>

            <span class="relative mt-3 block border-t border-slate-200/80 pt-1.5">
              <span class="block text-xs font-medium text-emerald-700">
                {{ option.recommendation }}
              </span>
              <span class="mt-1 block text-xs leading-5 text-slate-500">
                Contoh: {{ option.examples }}
              </span>
            </span>
          </span>
        </label>
      </div>
    </fieldset>

    <p class="mt-3 text-center text-xs leading-5 text-slate-500 lg:hidden">
      Masih ragu? Pilih sesuai dokumen legal usaha. Pengaturan ini dapat diperbarui kembali nanti.
    </p>
  </section>
</template>
