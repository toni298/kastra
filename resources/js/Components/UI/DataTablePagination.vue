<script setup>
import { computed } from 'vue'
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from '@lucide/vue'

const props = defineProps({
  currentPage: { type: Number, default: 1 },
  lastPage: { type: Number, default: 1 },
  from: { type: Number, default: 0 },
  to: { type: Number, default: 0 },
  total: { type: Number, default: 0 },
  hasTotal: { type: Boolean, default: true },
  firstPageUrl: { type: String, default: null },
  lastPageUrl: { type: String, default: null },
  previousUrl: { type: String, default: null },
  nextUrl: { type: String, default: null },
  links: { type: Array, default: () => [] },
  cursor: {
    type: Object,
    default: () => ({ next: null, previous: null }),
  },
  cursorMode: { type: Boolean, default: false },
})

const emit = defineEmits(['navigate', 'page-change'])

const generatePageLinks = (currentPage, lastPage) => {
  const maxVisible = 7
  const half = Math.floor(maxVisible / 2)

  let start = Math.max(1, currentPage - half)
  let end = Math.min(lastPage, start + maxVisible - 1)

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }

  return Array.from({ length: end - start + 1 }, (_, index) => {
    const page = start + index
    return {
      label: String(page),
      page,
      url: null,
      active: page === currentPage,
    }
  })
}

const normalizePageLinks = (links = []) => {
  const seenPages = new Set()

  return links
    .map((link) => {
      const rawPage = Number(link?.page ?? link?.label)
      const numericPage = Number.isInteger(rawPage) && rawPage > 0 ? rawPage : null

      if (!numericPage || seenPages.has(numericPage)) {
        return null
      }

      seenPages.add(numericPage)

      return {
        ...link,
        page: numericPage,
        label: String(numericPage),
        url: link?.url ?? null,
        active: Boolean(link?.active),
      }
    })
    .filter(Boolean)
    .sort((a, b) => a.page - b.page)
}

const pageLinks = computed(() => {
  const baseLinks =
    Array.isArray(props.links) && props.links.length
      ? props.links
      : props.lastPage > 1
        ? generatePageLinks(props.currentPage, props.lastPage)
        : []

  return normalizePageLinks(baseLinks)
})

const showPageLinks = computed(() => pageLinks.value.length > 0)
const hasPrevious = computed(() => Boolean(props.previousUrl || props.cursor.previous))
const hasNext = computed(() => Boolean(props.nextUrl || props.cursor.next))
const hasFirstPage = computed(() => Boolean(props.firstPageUrl))

const navigate = (url, page) => {
  if (url) {
    emit('navigate', url)
    return
  }

  if (page < 1 || page > props.lastPage || page === props.currentPage) return
  emit('page-change', page)
}
</script>

<template>
  <div
    class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 dark:border-[#29476b] lg:flex-row lg:items-center lg:justify-between"
  >
    <p class="text-sm text-slate-500 dark:text-slate-300">
      Menampilkan
      <span class="font-semibold text-slate-700 dark:text-white">{{ from }}</span> sampai
      <span class="font-semibold text-slate-700 dark:text-white">{{ to }}</span>
      <span v-if="hasTotal"
        >dari
        <span class="font-semibold text-slate-700 dark:text-white">{{ total }}</span> data</span
      >
      <span v-else>data</span>
    </p>

    <nav class="flex flex-wrap items-center gap-1" aria-label="Navigasi halaman tabel">
      <button
        type="button"
        class="grid size-9 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 disabled:cursor-not-allowed disabled:opacity-40 dark:border-[#29476b] dark:text-slate-300 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
        :disabled="!hasFirstPage"
        aria-label="Halaman pertama"
        @click="navigate(firstPageUrl, 1)"
      >
        <ChevronsLeft :size="16" />
      </button>
      <button
        type="button"
        class="grid size-9 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 disabled:cursor-not-allowed disabled:opacity-40 dark:border-[#29476b] dark:text-slate-300 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
        :disabled="!hasPrevious"
        aria-label="Halaman sebelumnya"
        @click="navigate(previousUrl, currentPage - 1)"
      >
        <ChevronLeft :size="16" />
      </button>

      <button
        v-if="showPageLinks"
        v-for="link in pageLinks"
        :key="link.page"
        type="button"
        :class="[
          'min-w-9 rounded-lg border px-3 py-2 text-sm font-semibold transition',
          link.active
            ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm shadow-emerald-600/20'
            : 'border-slate-200 text-slate-600 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 dark:border-[#29476b] dark:text-slate-300 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300',
        ]"
        :aria-current="link.active ? 'page' : undefined"
        @click="navigate(link.url, link.page)"
      >
        {{ link.page }}
      </button>

      <button
        type="button"
        class="grid size-9 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 disabled:cursor-not-allowed disabled:opacity-40 dark:border-[#29476b] dark:text-slate-300 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
        :disabled="!hasNext"
        aria-label="Halaman berikutnya"
        @click="navigate(nextUrl, currentPage + 1)"
      >
        <ChevronRight :size="16" />
      </button>
      <button
        v-if="!props.cursorMode"
        type="button"
        class="grid size-9 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 disabled:cursor-not-allowed disabled:opacity-40 dark:border-[#29476b] dark:text-slate-300 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
        :disabled="!lastPageUrl"
        aria-label="Halaman terakhir"
        @click="navigate(lastPageUrl, lastPage)"
      >
        <ChevronsRight :size="16" />
      </button>
    </nav>
  </div>
</template>
