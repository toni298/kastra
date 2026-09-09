import { computed, ref, watch } from 'vue'

// AOQ agentOptimationQuery.md baris 36-57:
// Kontrak JsonResource::collection($cursorPaginator) -> links.{next,prev} + meta.per_page.
// Normalisasi format paginator mentah (next_page_url, per_page di root) sebagai fallback.
export const usePaginationContract = (paginationRef) => {
  const resolvedNextUrl = computed(
    () => paginationRef.value?.links?.next ?? paginationRef.value?.next_page_url ?? null
  )
  const resolvedPrevUrl = computed(
    () => paginationRef.value?.links?.prev ?? paginationRef.value?.prev_page_url ?? null
  )
  const resolvedPerPage = computed(() =>
    Number(
      paginationRef.value?.meta?.per_page ?? paginationRef.value?.per_page ?? localPerPage.value
    )
  )
  const resolvedNextCursor = computed(
    () => paginationRef.value?.meta?.next_cursor ?? paginationRef.value?.next_cursor ?? null
  )
  const resolvedPrevCursor = computed(
    () => paginationRef.value?.meta?.prev_cursor ?? paginationRef.value?.prev_cursor ?? null
  )
  const hasCursorPagination = computed(() =>
    Boolean(resolvedNextCursor.value || resolvedPrevCursor.value)
  )

  const localPerPage = ref(resolvedPerPage.value)
  watch(
    () => resolvedPerPage.value,
    (value) => {
      if (value && Number(value) !== localPerPage.value) localPerPage.value = Number(value)
    }
  )

  return {
    localPerPage,
    resolvedNextUrl,
    resolvedPrevUrl,
    resolvedPerPage,
    resolvedNextCursor,
    resolvedPrevCursor,
    hasCursorPagination,
  }
}
