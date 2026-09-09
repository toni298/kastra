<script setup>
defineProps({
  variant: {
    type: String,
    default: 'dots',
    validator: (value) => ['dots', 'orbit', 'circuit', 'workflow', 'cta'].includes(value),
  },
})
</script>

<template>
  <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
    <template v-if="variant === 'dots'">
      <div class="decor-dot-grid absolute -right-16 top-14 h-44 w-56 opacity-60"></div>
      <div
        class="decor-float absolute right-[12%] top-10 hidden h-12 w-12 rotate-12 rounded-2xl border border-emerald-200/80 bg-white/60 shadow-lg shadow-emerald-950/5 backdrop-blur-sm dark:border-emerald-400/20 dark:bg-[#102542]/60 lg:block"
      ></div>
      <span
        class="absolute right-[9%] top-16 hidden h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-[0_0_0_6px_rgba(16,185,129,0.12)] lg:block"
      ></span>
    </template>

    <template v-else-if="variant === 'orbit'">
      <div
        class="decor-orbit decor-spin absolute -right-20 top-12 hidden h-64 w-64 rounded-full border border-emerald-200/70 dark:border-emerald-400/15 md:block"
      >
        <span
          class="absolute left-1/2 top-0 h-3 w-3 -translate-x-1/2 -translate-y-1/2 rounded-full bg-emerald-500 shadow-[0_0_0_8px_rgba(16,185,129,0.10)]"
        ></span>
      </div>
      <div
        class="absolute -left-24 bottom-20 h-56 w-56 rounded-full bg-blue-100/50 blur-3xl dark:bg-blue-500/10"
      ></div>
      <div class="decor-dot-grid absolute -left-10 bottom-12 h-32 w-44 opacity-45"></div>
    </template>

    <template v-else-if="variant === 'circuit'">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_12%_20%,rgba(16,185,129,0.13),transparent_24%),radial-gradient(circle_at_86%_78%,rgba(59,130,246,0.11),transparent_24%)]"
      ></div>
      <div class="decor-circuit absolute -left-8 top-1/2 hidden h-px w-48 md:block">
        <span class="absolute -top-1 left-0 h-2 w-2 rounded-full bg-emerald-400"></span>
        <span class="absolute -top-1 right-0 h-2 w-2 rounded-full bg-blue-400"></span>
      </div>
      <div class="decor-circuit absolute -right-6 top-1/3 hidden h-px w-40 md:block">
        <span class="absolute -top-1 left-0 h-2 w-2 rounded-full bg-blue-400"></span>
        <span class="absolute -top-1 right-0 h-2 w-2 rounded-full bg-emerald-400"></span>
      </div>
      <div
        class="decor-float-slow absolute right-[7%] top-12 hidden h-16 w-12 -rotate-6 rounded-2xl border border-white/10 bg-white/[0.04] shadow-2xl shadow-black/20 lg:block"
      >
        <span class="mx-auto mt-4 block h-1.5 w-7 rounded-full bg-emerald-400/70"></span>
        <span class="mx-auto mt-2 block h-1.5 w-5 rounded-full bg-white/20"></span>
      </div>
    </template>

    <template v-else-if="variant === 'workflow'">
      <div
        class="absolute -left-24 top-1/2 h-64 w-64 -translate-y-1/2 rounded-full border border-emerald-400/10"
      ></div>
      <div
        class="absolute -left-12 top-1/2 h-40 w-40 -translate-y-1/2 rounded-full border border-blue-400/10"
      ></div>
      <div
        class="decor-pulse absolute left-10 top-1/2 hidden h-3 w-3 -translate-y-1/2 rounded-full bg-emerald-400 shadow-[0_0_0_10px_rgba(52,211,153,0.10)] lg:block"
      ></div>
      <div class="decor-dark-grid absolute inset-y-0 right-0 w-1/2 opacity-30"></div>
    </template>

    <template v-else>
      <div class="decor-dot-grid absolute -left-10 top-8 h-32 w-44 opacity-45"></div>
      <div
        class="decor-float absolute -right-8 bottom-8 hidden h-20 w-20 rotate-12 rounded-[28px] border border-emerald-200/70 bg-white/50 shadow-xl shadow-emerald-950/5 dark:border-blue-400/15 dark:bg-blue-500/5 md:block"
      ></div>
      <span
        class="absolute right-[12%] top-12 hidden h-3 w-3 rounded-full bg-blue-400 shadow-[22px_32px_0_rgba(16,185,129,0.65),-18px_54px_0_rgba(59,130,246,0.35)] md:block"
      ></span>
    </template>
  </div>
</template>
