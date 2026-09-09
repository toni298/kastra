import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

export function useStockReport(itemsSource) {
  const tableLoading = ref(false)
  const items = computed(() => itemsSource.value ?? { data: [] })
  const page = usePage()
  
  const isCashier = computed(() => {
    return page.props.cashierLayout || page.url.startsWith('/cashier')
  })

  const request = (url, data) => {
    tableLoading.value = true
    router.get(url, data, {
      preserveScroll: true,
      preserveState: true,
      replace: true,
      onFinish: () => {
        tableLoading.value = false
      },
    })
  }

  const navigate = ({ cursor, direction }) => {
    if (!cursor) return

    const routeName = isCashier.value ? 'cashier.reports.stock' : 'reports.stock'
    const url = new URL(route(routeName), window.location.origin)
    url.searchParams.set('cursor', cursor)

    tableLoading.value = true
    router.get(url.toString(), {}, {
      replace: true,
      onFinish: () => {
        tableLoading.value = false
      },
    })
  }

  return { items, tableLoading, request, navigate }
}
