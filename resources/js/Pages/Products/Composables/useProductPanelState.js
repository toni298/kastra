import { computed, ref, watch } from 'vue'

export const useProductPanelState = ({
  props,
  emit,
  tab,
  defaultSort,
  defaultSortDirection,
  routeResolver,
  includeStatus = false,
}) => {
  const filters = computed(() => (Array.isArray(props.filters) ? {} : props.filters))

  const search = ref(filters.value.search ?? '')
  const status = ref(filters.value.status ?? '')
  const sortKey = ref(filters.value.sort ?? defaultSort)
  const sortDirection = ref(filters.value.sort_direction ?? defaultSortDirection)

  const applyFilters = (perPage = props.items?.per_page ?? 10) => {
    const data = {
      search: search.value || undefined,
      per_page: perPage,
      sort: sortKey.value,
      sort_direction: sortDirection.value,
    }

    if (includeStatus && status.value) {
      data.status = status.value
    }

    emit('request', {
      tab,
      url: routeResolver(),
      data,
      replace: true,
    })
  }

  const handleSearch = ({ search: value }) => {
    search.value = value
    applyFilters()
  }

const navigate = ({ cursor, direction }) => {
  if (!cursor) return

  const baseUrl = routeResolver()
  const url = new URL(baseUrl, window.location.origin)
  url.searchParams.set('cursor', cursor)

  emit('request', {
    tab,
    url: url.toString(),
    replace: true,
  })
}

  const handleSort = ({ key, direction }) => {
    sortKey.value = key
    sortDirection.value = direction
    applyFilters()
  }

  watch(
    () => filters.value.search,
    (value) => {
      const nextSearch = value ?? ''
      if (nextSearch !== search.value) {
        search.value = nextSearch
      }
    }
  )

  if (includeStatus) {
    watch(status, () => applyFilters())
  }

  return {
    filters,
    search,
    status,
    sortKey,
    sortDirection,
    applyFilters,
    handleSearch,
    navigate,
    handleSort,
  }
}
