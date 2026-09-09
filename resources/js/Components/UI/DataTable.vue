<script setup>
import { computed, ref, useSlots, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import DataTableSortHeader from './DataTableSortHeader.vue'
import EmptyState from './EmptyState.vue'
import SearchInput from './SearchInput.vue'
import Skeleton from './Skeleton.vue'
import { usePaginationContract } from '@/Composables/usePaginationContract'

const props = defineProps({
  items: { type: Array, default: () => [] },
  columns: { type: Array, default: () => [] },
  pagination: { type: Object, default: () => ({}) },
  perPage: { type: Number, default: 10 },
  perPageOptions: { type: Array, default: () => [10, 25, 50, 100] },
  loading: { type: Boolean, default: false },
  loadingMore: { type: Boolean, default: false },
  searchable: { type: Boolean, default: false },
  search: { type: String, default: '' },
  searchPlaceholder: { type: String, default: 'Cari data...' },
  searchClass: { type: String, default: '' },
  sortKey: { type: String, default: '' },
  sortDirection: {
    type: String,
    default: 'asc',
    validator: (value) => ['asc', 'desc'].includes(value),
  },
  showPagination: { type: Boolean, default: true },
  stickyToolbar: { type: Boolean, default: false },
  emptyIcon: { type: String, default: 'inbox' },
  emptyTitle: { type: String, default: 'Tidak ada data' },
  emptyMessage: { type: String, default: 'Belum ada data yang tersedia.' },
})

const emit = defineEmits([
  'navigate',
  'page-change',
  'per-page-change',
  'sort',
  'filter',
  'update:search',
  'update:sortKey',
  'update:sortDirection',
])

const slots = useSlots()

const localSearch = ref(props.search)
const {
  resolvedNextUrl,
  resolvedPrevUrl,
  resolvedNextCursor,
  resolvedPrevCursor,
  hasCursorPagination,
} = usePaginationContract(computed(() => props.pagination))

const hasToolbarExtras = computed(() => Boolean(slots.filters || slots.actions))
const total = computed(() =>
  Number(props.pagination?.meta?.total ?? props.pagination?.total ?? props.items.length)
)
const hasTotalCount = computed(
  () => (props.pagination?.meta?.total ?? props.pagination?.total) != null
)

const cursor = computed(() => ({
  previous: resolvedPrevCursor.value,
  next: resolvedNextCursor.value,
}))

const showPaginationFooter = computed(() => {
  if (hasCursorPagination.value) return true
  return total.value > perPage.value
})

const pagination = computed(() => ({
  currentPage: Number(props.pagination?.meta?.current_page ?? props.pagination?.current_page ?? 1),
  lastPage: Number(props.pagination?.meta?.last_page ?? props.pagination?.last_page ?? 1),
  from: Number(props.pagination?.meta?.from ?? (total.value ? 1 : 0)),
  to: Number(props.pagination?.meta?.to ?? props.items.length),
  total: total.value,
  hasTotal: hasTotalCount.value && !hasCursorPagination.value,
  firstPageUrl: props.pagination?.links?.first ?? props.pagination?.first_page_url ?? null,
  lastPageUrl: props.pagination?.links?.last ?? props.pagination?.last_page_url ?? null,
  previousUrl: resolvedPrevUrl.value,
  nextUrl: resolvedNextUrl.value,
  links: props.pagination?.meta?.links ?? props.pagination?.pageLinks ?? [],
  cursor: { next: resolvedNextCursor.value, previous: resolvedPrevCursor.value },
}))

const emitSearch = useDebounceFn((value) => {
  emit('update:search', value)
  emit('filter', { search: value })
}, 400)

const perPage = computed(() =>
  Number(props.pagination?.meta?.per_page ?? props.pagination?.per_page ?? props.perPage)
)

const handlePerPage = (event) => {
  emit('per-page-change', Number(event.target.value))
}

const resolveValue = (item, key) =>
  String(key)
    .split('.')
    .reduce((value, segment) => value?.[segment], item)

const columnAlign = (column) => {
  if (column.align) return column.align
  if (column.headerClass?.includes('text-center')) return 'center'
  if (column.headerClass?.includes('text-right')) return 'right'
  return 'left'
}

const handleSort = (column) => {
  if (!column.sortable) return
  const direction = props.sortKey === column.key && props.sortDirection === 'asc' ? 'desc' : 'asc'

  emit('update:sortKey', column.key)
  emit('update:sortDirection', direction)
  emit('sort', { key: column.key, direction })
}

watch(
  () => props.search,
  (value) => {
    if (value !== localSearch.value) localSearch.value = value
  }
)
watch(localSearch, (value) => emitSearch(value))
</script>

<template>
  <div class="w-full" :aria-busy="loading">
    <div
      :class="[
        'flex flex-col gap-4 border-b border-slate-100 bg-slate-50 p-4 dark:border-[#29476b] dark:bg-[#0a1b33] xl:flex-row xl:items-center xl:justify-between',
        stickyToolbar && 'sticky top-0 z-10',
      ]"
    >
      <label class="flex flex-wrap items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
        <span class="whitespace-nowrap">Tampilkan</span>
        <select
          :value="perPage"
          class="rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-8 text-sm font-medium text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-[#29476b] dark:bg-[#102542] dark:text-white"
          aria-label="Jumlah data per halaman"
          @change="handlePerPage"
        >
          <option v-for="option in perPageOptions" :key="option" :value="option">
            {{ option }}
          </option>
        </select>
        <span class="whitespace-nowrap">data per halaman</span>
      </label>

      <div
        v-if="searchable || hasToolbarExtras"
        class="flex w-full flex-col gap-3 sm:flex-row sm:items-center xl:w-auto xl:flex-1 xl:justify-end"
      >
        <div v-if="$slots.filters" class="flex flex-wrap items-center gap-3">
          <slot name="filters"></slot>
        </div>
        <SearchInput
          v-if="searchable"
          v-model="localSearch"
          :class="['w-full min-w-0 max-w-none sm:max-w-xs xl:min-w-[240px]', searchClass]"
          :placeholder="searchPlaceholder"
          aria-label="Cari data tabel"
        />
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
          <slot name="actions"></slot>
        </div>
      </div>
    </div>

    <div class="relative w-full overflow-hidden rounded-t-2xl border-b border-slate-100 dark:border-[#29476b]">
      <div v-if="loading" class="space-y-3 p-5" role="status" aria-label="Memuat data">
        <Skeleton v-for="index in 5" :key="index" height="10" />
      </div>

      <div v-else-if="items.length" class="overflow-x-auto">
        <table
          class="min-w-full [&_thead_th]:h-12 [&_thead_th]:px-5 [&_thead_th]:py-0 [&_thead_th]:align-middle [&_tbody>tr]:transition-colors [&_tbody>tr:hover]:!bg-emerald-50/80 dark:[&_tbody>tr:hover]:!bg-emerald-400/10"
        >
          <thead
            v-if="$slots.thead"
            class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-[#0a1b33] dark:text-slate-300"
          >
            <slot
              name="thead"
              :sort-key="sortKey"
              :sort-direction="sortDirection"
              :sort="handleSort"
            ></slot>
          </thead>
          <thead
            v-else-if="columns.length"
            class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-[#0a1b33] dark:text-slate-300"
          >
            <tr>
              <th
                v-for="column in columns"
                :key="column.key"
                scope="col"
                :class="['px-5 py-3.5', column.headerClass]"
                :aria-sort="
                  sortKey === column.key
                    ? sortDirection === 'asc'
                      ? 'ascending'
                      : 'descending'
                    : undefined
                "
              >
                <DataTableSortHeader
                  v-if="column.sortable"
                  :label="column.label"
                  :active="sortKey === column.key"
                  :direction="sortDirection"
                  :align="columnAlign(column)"
                  @sort="handleSort(column)"
                />
                <span
                  v-else
                  :class="[
                    'inline-flex h-12 w-full items-center',
                    columnAlign(column) === 'center'
                      ? 'justify-center'
                      : columnAlign(column) === 'right'
                        ? 'justify-end'
                        : 'justify-start',
                  ]"
                >
                  {{ column.label }}
                </span>
              </th>
            </tr>
          </thead>

          <tbody
            class="divide-y divide-slate-100 [&>tr:nth-child(even)]:bg-slate-50/70 dark:divide-[#29476b] dark:[&>tr:nth-child(even)]:bg-[#0a1b33]/50"
          >
            <template v-if="$slots.default"><slot :items="items"></slot></template>
            <template v-else>
              <tr
                v-for="(item, rowIndex) in items"
                :key="item.id ?? rowIndex"
                class="text-sm text-slate-700 dark:text-slate-200"
              >
                <td
                  v-for="column in columns"
                  :key="column.key"
                  :class="['px-5 py-4', column.class]"
                >
                  <slot
                    :name="`cell-${column.key}`"
                    :item="item"
                    :value="resolveValue(item, column.key)"
                    :index="rowIndex"
                  >
                    {{ resolveValue(item, column.key) }}
                  </slot>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <EmptyState v-else :icon="emptyIcon" :title="emptyTitle" :message="emptyMessage" />
    </div>

    <!-- Pagination Footer -->
    <footer v-if="showPaginationFooter" class="mt-0 flex items-center justify-between border-t border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-600 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-slate-300">
      <!-- Page Info -->
      <div class="flex items-center gap-2">
        <template v-if="hasTotalCount">
          <span>Menampilkan {{ pagination.from }} - {{ pagination.to }} dari {{ pagination.total }}</span>
        </template>
        <template v-else>
          <span>Menampilkan {{ items.length }} baris data per halaman</span>
        </template>
      </div>

      <!-- Cursor Navigation -->
      <div v-if="hasCursorPagination" class="flex items-center gap-2">
        <button
          type="button"
          :disabled="!cursor.previous || loading"
          aria-label="Halaman sebelumnya"
          class="inline-flex min-h-[38px] min-w-[44px] cursor-pointer flex-shrink-0 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium transition-all disabled:opacity-50 hover:bg-slate-50 disabled:pointer-events-none dark:border-[#29476b] dark:bg-[#102542] dark:hover:bg-[#1c3357]"
          @click="emit('navigate', { cursor: cursor.previous, direction: 'prev' })"
        >
          ← Sebelumnya
        </button>
        <button
          type="button"
          :disabled="!cursor.next || loading"
          aria-label="Halaman selanjutnya"
          class="inline-flex min-h-[38px] min-w-[44px] cursor-pointer flex-shrink-0 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium transition-all disabled:opacity-50 hover:bg-slate-50 disabled:pointer-events-none dark:border-[#29476b] dark:bg-[#102542] dark:hover:bg-[#1c3357]"
          @click="emit('navigate', { cursor: cursor.next, direction: 'next' })"
        >
          Selanjutnya →
        </button>
      </div>
    </footer>
  </div>
</template>
