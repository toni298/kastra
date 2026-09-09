<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
})
const emit = defineEmits(['close'])
</script>
<template>
  <Teleport to="body"
    ><Transition name="fade"
      ><div
        v-if="open"
        class="fixed inset-0 z-50 bg-slate-950/45"
        @click="emit('close')"
      ></div></Transition
    ><Transition name="drawer"
      ><aside
        v-if="open"
        class="fixed inset-y-0 right-0 z-50 w-full max-w-md overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      >
        <header
          class="sticky top-0 flex items-start justify-between border-b border-slate-100 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
        >
          <div>
            <h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ title }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ description }}</p>
          </div>
          <button
            class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-[#163354]"
            @click="emit('close')"
          >
            ×
          </button>
        </header>
        <div class="p-5"><slot></slot></div></aside></Transition
  ></Teleport>
</template>
<style scoped>
.fade-enter-active,
.fade-leave-active,
.drawer-enter-active,
.drawer-leave-active {
  transition: all 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.drawer-enter-from,
.drawer-leave-to {
  transform: translateX(100%);
}
</style>
