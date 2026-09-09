<script setup>
import { ref, watch } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

const props = defineProps({
  title: { type: String, required: true },
  icon: { type: Object, default: null },
  defaultOpen: { type: Boolean, default: false },
  active: { type: Boolean, default: false },
})

const open = ref(props.defaultOpen || props.active)
watch(() => props.active, (v) => { if (v) open.value = true })
</script>

<template>
  <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-[#29476b] dark:bg-[#0d1e36]">
    <button
      type="button"
      class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-slate-50 dark:hover:bg-[#0a1b33]"
      :class="active ? 'bg-emerald-50/60 dark:bg-emerald-500/5' : ''"
      @click="open = !open"
    >
      <span class="flex items-center gap-2.5">
        <component v-if="icon" :is="icon" :size="16" class="text-emerald-600 dark:text-emerald-400" />
        <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ title }}</span>
      </span>
      <ChevronDown :size="16" class="text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" />
    </button>
    <div v-show="open" class="border-t border-slate-100 px-4 py-4 dark:border-[#1d3859]">
      <slot />
    </div>
  </div>
</template>
