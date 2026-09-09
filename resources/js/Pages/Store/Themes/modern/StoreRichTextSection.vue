<script setup>
import { computed } from 'vue'

const props = defineProps({
  store: { type: Object, required: true },
  config: { type: Object, default: () => ({}) },
})

const content = computed(() => props.config.content || '')
</script>

<template>
  <section v-if="content" class="mx-auto mt-10 max-w-4xl px-4 sm:px-6 lg:px-8">
    <div
      class="prose prose-sm max-w-none rounded-sm border border-slate-200 bg-white px-6 py-6 text-slate-700"
      v-html="content"
    ></div>
  </section>
</template>
