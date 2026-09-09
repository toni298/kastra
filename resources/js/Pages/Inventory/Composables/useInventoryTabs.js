import { router, usePage } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'

// AOQ agentOptimationQuery.md baris 36-47: kontrak JsonResource::collection($cursorPaginator).
const emptyItems = {
  data: [],
  links: { first: null, last: null, prev: null, next: null },
  meta: {
    per_page: 10,
    total: 0,
    from: null,
    to: null,
    current_page: 1,
    next_cursor: null,
    prev_cursor: null,
  },
}

const tabItemsProp = (tab) =>
  tab === 'stock'
    ? 'stockItems'
    : tab === 'transfer'
      ? 'transferItems'
      : tab === 'opname'
        ? 'opnameItems'
        : tab === 'movements'
          ? 'movementItems'
          : tab === 'overview'
            ? 'overviewBranchStocks'
            : null
const tabFiltersProp = (tab) =>
  tab === 'stock'
    ? 'stockFilters'
    : tab === 'transfer'
      ? 'transferFilters'
      : tab === 'opname'
        ? 'opnameFilters'
        : tab === 'movements'
          ? 'movementFilters'
          : tab === 'overview'
            ? 'overviewFilters'
            : null
const tabOptionsProp = (tab) =>
  tab === 'stock'
    ? 'stockOptions'
    : tab === 'transfer'
      ? 'transferOptions'
      : tab === 'opname'
        ? 'opnameOptions'
        : tab === 'overview'
          ? 'overviewOptions'
          : null

export const useInventoryTabs = (props) => {
  const page = usePage()
  const inventoryRoutes = {
    overview: 'inventory.index',
    stock: 'inventory.stock',
    transfer: 'inventory.transfers',
    opname: 'inventory.opnames',
    movements: 'inventory.movements',
    ...(props.inventoryRoutes ?? {}),
  }
  const currentTab = computed(() => {
    const requestedTab = props.activeTab ?? 'stock'
    const availableTab = tabs.value.find((tab) => tab.id === requestedTab)

    return availableTab?.id ?? tabs.value[0]?.id ?? 'stock'
  })
  const loadingTab = ref(null)
  const loadingMore = ref(false)
  const modal = ref(null)
  const selectedItem = ref(null)
  const transferDetailLoading = ref(false)
  const transferDetailError = ref(null)
  const opnameDetailLoading = ref(false)
  const opnameDetailError = ref(null)
  // AOQ baris 68: gabungkan data lama + baru di state halaman. Reset saat filter/search/perPage berubah.
  const accumulated = reactive({ stock: [], transfer: [], opname: [], movements: [] })
  const pendingMore = ref(null)
  const serverItems = (tab) => {
    if (tab === 'stock') return props.stockItems ?? emptyItems
    if (tab === 'transfer') return props.transferItems ?? emptyItems
    if (tab === 'opname') return props.opnameItems ?? emptyItems
    if (tab === 'movements') return props.movementItems ?? emptyItems
    return emptyItems
  }

  // Saat prop items berubah: jika pendingMore aktif -> append; selain itu -> replace (filter/reset).
  const watchItems = (tab) =>
    watch(
      () => serverItems(tab),
      (server) => {
        const data = server?.data ?? []
        if (pendingMore.value === tab) {
          pendingMore.value = null
          const existingIds = new Set(accumulated[tab].map((row) => row.id))
          accumulated[tab].push(...data.filter((row) => !existingIds.has(row.id)))
        } else {
          accumulated[tab] = data.slice()
        }
      },
      { immediate: true }
    )
  watchItems('stock')
  watchItems('transfer')
  watchItems('opname')
  watchItems('movements')

  const can = (permission) => page.props.auth.permissions?.includes(permission)
  const tabs = computed(() => {
    const allTabs = [
      {
        id: 'overview',
        label: 'Ringkasan',
        route: inventoryRoutes.overview,
        permission: 'inventory.summary.view',
      },
      {
        id: 'stock',
        label: 'Stok Gudang',
        route: inventoryRoutes.stock,
        permission: 'inventory.stock.view',
      },
      {
        id: 'transfer',
        label: 'Transfer',
        route: inventoryRoutes.transfer,
        permission: 'inventory.transfers.view',
      },
      {
        id: 'opname',
        label: 'Stock Opname',
        route: inventoryRoutes.opname,
        permission: 'inventory.opname.view',
      },
      {
        id: 'movements',
        label: 'Mutasi Stok',
        route: inventoryRoutes.movements,
        permission: 'inventory.movements.view',
      },
    ]

    return allTabs.filter((tab) => can(tab.permission))
  })

  // AOQ baris 68: batch awal menjadi dasar accumulated. Saat prop berubah (filter/reset), ganti penuh.
  const itemsFor = (tab) => {
    const base = serverItems(tab)
    return { ...base, data: accumulated[tab] }
  }
  const paginationFor = (tab) => {
    const base = serverItems(tab)
    return { links: base.links ?? {}, meta: base.meta ?? {} }
  }

  const activeComponentProps = computed(() => {
    if (currentTab.value === 'overview') {
      return {
        summary: props.overviewSummary ?? {},
        items: props.overviewBranchStocks ?? emptyItems,
        pagination: props.overviewBranchStocks ?? { links: {}, meta: {} },
        filters: props.overviewFilters ?? {},
        options: props.overviewOptions ?? { categories: [] },
        loading: loadingTab.value === 'overview',
        loadingMore: loadingMore.value && currentTab.value === 'overview',
        canDiscount: can('inventory.summary.discount'),
        canAdjustment:
          !props.enabledFeatures?.includes('purchase') && can('inventory.summary.adjust'),
      }
    }
    if (currentTab.value === 'stock') {
      return {
        items: itemsFor('stock'),
        pagination: paginationFor('stock'),
        filters: props.stockFilters ?? {},
        options: props.stockOptions ?? { warehouses: [], categories: [] },
        hasMultipleWarehouses: props.hasMultipleWarehouses ?? false,
        loading: loadingTab.value === 'stock',
        loadingMore: loadingMore.value && currentTab.value === 'stock',
        canCreate: can('inventory.stock.create'),
        canEdit: can('inventory.stock.edit'),
        canDelete: can('inventory.stock.delete'),
        canDiscount: can('inventory.summary.discount'),
      }
    }
    if (currentTab.value === 'transfer') {
      return {
        items: itemsFor('transfer'),
        pagination: paginationFor('transfer'),
        filters: props.transferFilters ?? {},
        options: props.transferOptions ?? { warehouses: [] },
        loading: loadingTab.value === 'transfer',
        loadingMore: loadingMore.value && currentTab.value === 'transfer',
        canCreate: can('inventory.transfers.create'),
        canEdit: can('inventory.transfers.edit'),
        canDelete: can('inventory.transfers.delete'),
        canReceive: can('inventory.transfers.receive'),
      }
    }
    if (currentTab.value === 'opname') {
      return {
        items: itemsFor('opname'),
        pagination: paginationFor('opname'),
        filters: props.opnameFilters ?? {},
        options: props.opnameOptions ?? { warehouses: [], branches: [] },
        loading: loadingTab.value === 'opname',
        loadingMore: loadingMore.value && currentTab.value === 'opname',
        canCreate: can('inventory.opname.create'),
        canEdit: can('inventory.opname.edit'),
        canDelete: can('inventory.opname.delete'),
        canProcess: can('inventory.opname.process'),
      }
    }
    if (currentTab.value === 'movements') {
      const items = itemsFor('movements')
      return {
        items,
        pagination: paginationFor('movements'),
        filters: props.movementFilters ?? {},
        summary: items?.summary ?? props.movementSummary ?? {},
        options: props.movementOptions ?? { branches: [], warehouses: [], users: [] },
        loading: loadingTab.value === 'movements',
        loadingMore: loadingMore.value && currentTab.value === 'movements',
      }
    }
    return {}
  })

  const requestTab = (tab, url, data = {}, replace = false) => {
    // AOQ baris 68: filter/search/rows per page berubah -> replace penuh (watcher menangani reset).
    loadingTab.value = tab
    router.get(url, data, {
      only: [
        'activeTab',
        tabItemsProp(tab),
        tabFiltersProp(tab),
        tabOptionsProp(tab),
        tab === 'movements' ? 'movementSummary' : null,
        'hasMultipleWarehouses',
      ].filter(Boolean),
      preserveScroll: true,
      preserveState: true,
      replace,
      onFinish: () => {
        if (loadingTab.value === tab) loadingTab.value = null
      },
    })
  }
  const selectTab = (tab) => {
    if (tab.id === currentTab.value) return
    if (loadingTab.value || loadingMore.value) router.cancelAll({ sync: true })
    requestTab(tab.id, route(tab.route), {}, true)
  }
  const requestActiveTab = ({ tab, url, data = {}, replace = false }) => {
    if (tab === currentTab.value) requestTab(tab, url, data, replace)
  }
  // AOQ baris 67-86: partial reload hanya prop daftar terkait, preserve scroll/state, append data lama+baru.
  const loadMore = (url) => {
    if (!url || loadingMore.value || loadingTab.value) return
    const tab = currentTab.value
    const itemsProp = tabItemsProp(tab)
    if (!itemsProp) return

    pendingMore.value = tab
    loadingMore.value = true
    router.get(
      url,
      {},
      {
        only: [itemsProp],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
          loadingMore.value = false
          if (pendingMore.value === tab) pendingMore.value = null
        },
      }
    )
  }

  const openAction = async ({ action, type, item = null }) => {
    if (type === 'opname' && action === 'process') {
      router.visit(route('inventory.opname.show', item.id))
      return
    }
    selectedItem.value = item
    if (type === 'transfer' && action === 'detail') {
      modal.value = 'transfer-drawer'
      transferDetailLoading.value = true
      transferDetailError.value = null
      // ponytail: endpoint detail modal return JsonResponse (bukan Inertia page).
      // Pola axios.get untuk modal detail sudah mapan di modul Purchases/Sales/Returns.
      // Upgrade path: jika ingin Inertia-only, ubah controller share prop + partial reload.
      try {
        const response = await window.axios.get(route('inventory.transfers.show', item.id))
        selectedItem.value = response.data.data
      } catch {
        transferDetailError.value = 'Detail transfer tidak dapat dimuat. Silakan coba lagi.'
      } finally {
        transferDetailLoading.value = false
      }
      return
    }
    if (type === 'opname' && action === 'detail') {
      modal.value = 'opname-detail'
      opnameDetailLoading.value = true
      opnameDetailError.value = null
      try {
        const response = await window.axios.get(route('inventory.opnames.detail', item.id))
        selectedItem.value = response.data.data
      } catch {
        opnameDetailError.value = 'Detail stock opname tidak dapat dimuat. Silakan coba lagi.'
      } finally {
        opnameDetailLoading.value = false
      }
      return
    }
    if (type === 'opname' && action === 'edit') {
      modal.value = 'opname-edit'
      return
    }
    modal.value =
      action === 'detail' && type === 'transfer'
        ? 'transfer-drawer'
        : action === 'receive' && type === 'transfer'
          ? 'transfer-receive'
          : action === 'create' || action === 'edit'
            ? type === 'stock'
              ? 'product'
              : type
            : action
  }
  const closeModal = () => {
    modal.value = null
    selectedItem.value = null
    transferDetailError.value = null
    opnameDetailError.value = null
  }

  return {
    currentTab,
    modal,
    selectedItem,
    transferDetailLoading,
    transferDetailError,
    opnameDetailLoading,
    opnameDetailError,
    tabs,
    activeComponentProps,
    selectTab,
    requestActiveTab,
    loadMore,
    openAction,
    closeModal,
  }
}
