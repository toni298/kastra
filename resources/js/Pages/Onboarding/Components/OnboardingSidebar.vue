<script setup>
import { Check, Clock3, ShieldCheck } from 'lucide-vue-next'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'

defineProps({
  steps: { type: Array, default: () => [] },
  currentStep: { type: Number, default: 1 },
})
</script>

<template>
  <aside
    class="hidden w-[356px] shrink-0 bg-[radial-gradient(circle_at_28%_24%,#08142b_0%,#020817_48%,#010612_100%)] px-[26px] py-[27px] text-white lg:flex lg:h-screen lg:flex-col"
  >
    <div class="flex items-center gap-3 text-[25px] font-semibold tracking-tight">
      <span
        class="grid h-12 w-12 place-items-center rounded bg-gradient-to-br from-emerald-400 to-emerald-600 text-slate-950"
      >
        <ApplicationLogo class="h-7 w-7 fill-current" />
      </span>
      <span>Kastra <span class="text-emerald-500">ERP</span></span>
    </div>
    <div class="mt-9">
      <p class="text-[14px] font-medium uppercase tracking-[0.13em] text-emerald-400">Onboarding</p>
      <h2 class="mt-3 text-[25px] font-semibold tracking-tight">Setup Bisnis Anda</h2>
      <p class="mt-2 max-w-[280px] text-[17px] leading-7 text-slate-400">
        Lengkapi informasi di setiap langkah untuk menyiapkan bisnis Anda di Kastra ERP.
      </p>
    </div>
    <ol
      class="relative mt-6 space-y-1 before:absolute before:bottom-7 before:left-8 before:top-7 before:border-l before:border-dashed before:border-slate-500"
    >
      <li
        v-for="(step, index) in steps"
        :key="step.title"
        :class="[
          'relative z-10 flex gap-4 rounded-xl px-3 py-2.5',
          currentStep === index + 1
            ? 'bg-[#0b1732] shadow-[inset_0_1px_0_rgba(255,255,255,0.02)]'
            : '',
        ]"
      >
        <div
          :class="[
            'grid h-10 w-10 shrink-0 place-items-center rounded-full border-2 text-sm font-semibold',
            index + 1 <= currentStep
              ? 'border-emerald-500 bg-gradient-to-br from-emerald-400 to-emerald-700 text-white'
              : 'border-slate-600 bg-[#020817] text-slate-200',
          ]"
        >
          <Check v-if="index + 1 < currentStep" class="h-4 w-4" />
          <span v-else>{{ index + 1 }}</span>
        </div>
        <div class="pt-0.5">
          <p class="text-[16px] font-medium text-white">{{ step.title }}</p>
          <p class="mt-1 text-[14px] text-slate-400">{{ step.subtitle }}</p>
        </div>
      </li>
    </ol>
    <div class="mt-auto rounded-2xl border border-slate-700 bg-slate-950/40 px-6 py-5">
      <div class="flex gap-4">
        <span
          class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-emerald-950 text-emerald-400"
          ><Clock3 class="h-5 w-5"
        /></span>
        <div>
          <p class="text-[14px] text-slate-300">Estimasi waktu</p>
          <p class="mt-1 text-sm font-medium">± 3 menit</p>
        </div>
      </div>
      <div class="my-5 border-t border-slate-700"></div>
      <div class="flex gap-4">
        <span
          class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-emerald-950 text-emerald-400"
          ><ShieldCheck class="h-5 w-5"
        /></span>
        <div>
          <p class="text-sm font-medium">Data aman & tersimpan</p>
          <p class="mt-1 text-[14px] leading-6 text-slate-300">
            Data Anda tersimpan otomatis setelah setiap langkah.
          </p>
        </div>
      </div>
    </div>
  </aside>
</template>
