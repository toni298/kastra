import { router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useAuthorization } from '../../../Composables/useAuthorization'

export const useProductTabs = (props) => {
  const productModal = ref(null)
  const selectedProduct = ref(null)
  const productFormData = ref({ ready: false, suggestions: props.suggestions })

  const { can } = useAuthorization()
  const productRoute = props.productRoute ?? 'products.index'
  const masterRoutes = {
    product_categories: 'products.master.index',
    product_brands: 'products.master.index',
    units: 'products.master.index',
    ...(props.productMasterRoutes ?? {}),
  }

  const ALL_TABS = [
    {
      id: 'products',
      label: 'Produk',
      route: route(productRoute),
      permission: 'products.view',
    },
    {
      id: 'product_categories',
      label: 'Kategori',
      route: route(masterRoutes.product_categories, 'product_categories'),
      permission: 'product_categories.view',
    },
    {
      id: 'product_brands',
      label: 'Brand',
      route: route(masterRoutes.product_brands, 'product_brands'),
      permission: 'product_brands.view',
    },
    {
      id: 'units',
      label: 'Satuan',
      route: route(masterRoutes.units, 'units'),
      permission: 'units.view',
    },
  ]

  const tabs = computed(() => ALL_TABS.filter((tab) => can(tab.permission)))

  const activeTab = computed(() => {
    const requested = props.activeTab
    const visible = tabs.value
    if (visible.some((tab) => tab.id === requested)) return requested
    return visible[0]?.id ?? null
  })

  const ensureAccessibleTab = () => {
    if (activeTab.value && props.activeTab === activeTab.value) return
    const first = tabs.value[0]
    if (!first) return
    router.get(
      first.route,
      {},
      { only: [first.id, 'activeTab'], preserveState: true, preserveScroll: true, replace: true }
    )
  }

  watch(
    () => props.activeTab,
    () => ensureAccessibleTab(),
    { immediate: true }
  )

  const openProductModal = (kind, product = null) => {
    selectedProduct.value = product
    productModal.value = kind
  }

  const openProductForm = (product = null) => {
    if (!product && !productFormData.value.suggestions) return
    productFormData.value.ready = true
    openProductModal('form', product)
  }

  const handleProductMutation = () => {
    router.reload({ preserveState: true, preserveScroll: true })
  }

  const handleRequest = ({ url, data = {}, replace = false }) => {
    router.get(url, data, {
      preserveState: true,
      preserveScroll: true,
      replace,
    })
  }

  const switchTab = (tab) => {
    if (tab.id === activeTab.value) return

    router.get(
      tab.route,
      {},
      {
        only: [tab.id, 'activeTab'],
        preserveState: true,
        preserveScroll: true,
      }
    )
  }

  return {
    activeTab,
    tabs,
    can,
    productModal,
    selectedProduct,
    productFormData,
    openProductModal,
    openProductForm,
    handleRequest,
    switchTab,
    handleProductMutation,
    handleMutation: handleProductMutation,
  }
}
