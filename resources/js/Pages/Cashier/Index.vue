<script setup>
import { computed, ref, watch } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import CashierLayout from '@/Components/Cashier/CashierLayout.vue'
import ProductCatalog from '@/Components/Products/ProductCatalog.vue'
import CashierCustomerCard from './Components/CashierCustomerCard.vue'
import CashierCart from './Components/CashierCart.vue'
import CashierPaymentModal from './Components/CashierPaymentModal.vue'
import CustomerModal from '@/Pages/Sales/Components/CustomerModal.vue'
import ThermalPaperSetting from './Components/ThermalPaperSetting.vue'
import { autoPrintThermalReceipt } from '@/Utils/thermalReceiptExport'
import { useRoute } from '../../../../vendor/tightenco/ziggy/src/js'

const props = defineProps({
  products: { type: Object, default: () => ({ data: [], links: {} }) },
  categories: { type: Object, default: () => ({ data: [], links: {} }) },
  branches: { type: Array, default: () => [] },
  activeBranchId: { type: [String, Number], default: null },
  taxConfiguration: { type: Object, default: null },
})
const page = usePage()
const route = useRoute(page.props.ziggy)
const search = ref('')
const selectedCategory = ref('all')
const selectedBranch = ref(props.activeBranchId ?? props.branches[0]?.id ?? '')
const customerId = ref(null)
const customerModalOpen = ref(false)
const customerCard = ref(null)
const discount = ref(0)
const taxRate = ref(Number(props.taxConfiguration?.rate ?? 0))
const taxMode = ref(props.taxConfiguration?.mode ?? 'exclusive')
const note = ref('')
const cart = ref([])
const productItems = ref([])
const productsLoading = ref(false)
const loadingMoreProducts = ref(false)
const pendingMoreProducts = ref(false)
const categoryItems = ref([])
const loadingMoreCategories = ref(false)
const pendingMoreCategories = ref(false)
const isSubmitting = ref(false)
const paymentOpen = ref(false)
const categories = computed(() => [
  { id: 'all', name: 'Semua' },
  ...categoryItems.value.map((category) => ({ id: category.id, name: category.name })),
  { id: 'other', name: 'Lainnya' },
])
const subtotal = computed(() =>
  cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0)
)
const tax = computed(() => Math.round((subtotal.value * taxRate.value) / 100))
const total = computed(() =>
  Math.max(
    0,
    subtotal.value - Number(discount.value || 0) + (taxMode.value === 'inclusive' ? 0 : tax.value)
  )
)
const nextProductsUrl = computed(() => props.products?.links?.next ?? null)
const nextCategoriesUrl = computed(() => props.categories?.links?.next ?? null)
const cashierProductsUrl = computed(() =>
  page.url.startsWith('/cashier/product') ? route('cashier.products') : route('cashier')
)
const requestProducts = (url = cashierProductsUrl.value, more = false) => {
  if (more && (loadingMoreProducts.value || !nextProductsUrl.value)) return
  more ? (pendingMoreProducts.value = true) : (productsLoading.value = true)
  if (more) loadingMoreProducts.value = true
  router.get(
    url,
    more
      ? {}
      : {
          search: search.value || undefined,
          category_id: selectedCategory.value === 'all' ? undefined : selectedCategory.value,
          branch_id: selectedBranch.value || undefined,
          per_page: 20,
        },
    {
      only: ['products'],
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        productsLoading.value = false
        loadingMoreProducts.value = false
        pendingMoreProducts.value = false
      },
    }
  )
}
const loadMoreCategories = () => {
  if (!nextCategoriesUrl.value || loadingMoreCategories.value) return

  pendingMoreCategories.value = true
  loadingMoreCategories.value = true
  router.get(
    nextCategoriesUrl.value,
    {},
    {
      only: ['categories'],
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        loadingMoreCategories.value = false
        pendingMoreCategories.value = false
      },
    }
  )
}
const debouncedProductSearch = useDebounceFn(() => requestProducts(), 400)
watch(search, debouncedProductSearch)
watch(selectedBranch, () => requestProducts())
watch(selectedBranch, () => {
  customerId.value = null
})
watch(
  () => props.products,
  (products) => {
    const incoming = products?.data ?? []
    if (pendingMoreProducts.value) {
      const ids = new Set(productItems.value.map((product) => product.id))
      productItems.value.push(...incoming.filter((product) => !ids.has(product.id)))
      return
    }
    productItems.value = incoming.slice()
  },
  { immediate: true }
)
watch(
  () => props.categories,
  (categoriesPage) => {
    const incoming = categoriesPage?.data ?? []
    if (pendingMoreCategories.value) {
      const ids = new Set(categoryItems.value.map((category) => category.id))
      categoryItems.value.push(...incoming.filter((category) => !ids.has(category.id)))
      return
    }
    categoryItems.value = incoming.slice()
  },
  { immediate: true }
)
const selectCategory = (categoryId) => {
  selectedCategory.value = categoryId
  requestProducts()
}
const addToCart = (product) => {
  const existing = cart.value.find((item) => item.id === product.id)
  if (existing) existing.quantity = Math.min(existing.quantity + 1, product.stock)
  else if (product.stock > 0) cart.value.push({ ...product, quantity: 1 })
}
const changeQuantity = (item, amount) => {
  item.quantity = Math.max(1, Math.min(item.quantity + amount, item.stock))
}
const setQuantity = (item, quantity) => {
  item.quantity = Math.max(1, Math.min(Number(quantity) || 1, item.stock))
}
const removeFromCart = (id) => {
  cart.value = cart.value.filter((item) => item.id !== id)
}
const clearCart = () => {
  cart.value = []
  note.value = ''
  discount.value = 0
  customerCard.value?.clearCustomerSelection()
  customerId.value = null
}
const checkout = () => {
  if (!cart.value.length || isSubmitting.value) return
  paymentOpen.value = true
}
const submitCheckout = ({
  paymentMethod,
  paymentAmount,
  paymentReference,
  paymentNote,
  dueDate,
  proofFile,
}) => {
  if (!cart.value.length || isSubmitting.value) return
  isSubmitting.value = true

  // Buka tab kosong SEKARANG (masih dalam user gesture) untuk bypass popup blocker
  const preOpenedTab = window.open('about:blank', '_blank')
  if (preOpenedTab) {
    preOpenedTab.document.open()
    preOpenedTab.document.write(`
      <html>
        <body style="display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:'Courier New',monospace;background:#fff">
          <p style="color:#333;font-size:14px">Memproses struk...</p>
        </body>
      </html>
    `)
    preOpenedTab.document.close()
  }

  // Simpan data pembayaran untuk struk thermal sebelum cart di-clear
  const paymentInfo = {
    method: paymentMethod,
    amount: Number(paymentAmount),
    change: paymentMethod === 'cash' ? Math.max(0, Number(paymentAmount) - total.value) : 0,
  }

  const form = useForm({
    branch_id: selectedBranch.value,
    customer_id: customerId.value,
    document_type: 'invoice',
    transaction_date: new Date().toISOString().slice(0, 10),
    payment_status: Number(paymentAmount) >= total.value ? 'paid' : 'unpaid',
    status: 'completed',
    discount: Number(discount.value || 0),
    tax: tax.value,
    tax_mode: taxMode.value,
    payment_method: paymentMethod,
    payment_amount: Number(paymentAmount),
    payment_reference: paymentReference || null,
    payment_note: paymentNote || null,
    due_date: dueDate || null,
    proof_file: proofFile || null,
    note: note.value || null,
    details: cart.value.map((item) => ({
      product_id: item.id,
      quantity: item.quantity,
      unit_price: item.price,
    })),
  })
  form.post(route('sales.transactions.store'), {
    preserveScroll: true,
    onSuccess: () => {
      paymentOpen.value = false

      // Auto-print struk thermal dari flash invoiceData
      const invoiceData = page.props.flash?.invoiceData

      if (invoiceData?.transaction) {
        // invoiceData struktur: { transaction: {...}, branch: {...}, company: {...} }
        const transactionData = invoiceData.transaction
        const branchData = invoiceData.branch || props.branches.find(
          (b) => String(b.id) === String(transactionData.branch_id)
        )
        const companyData = invoiceData.company || page.props.company || null

        autoPrintThermalReceipt(preOpenedTab, transactionData, branchData, companyData, paymentInfo)
      } else {
        // invoiceData kosong — tutup tab
        if (preOpenedTab && !preOpenedTab.closed) {
          preOpenedTab.close()
        }
      }

      clearCart()
    },
    onError: () => {
      // Tutup tab jika submit gagal
      if (preOpenedTab && !preOpenedTab.closed) {
        preOpenedTab.close()
      }
    },
    onFinish: () => {
      isSubmitting.value = false
    },
  })
}
</script>

<template>
  <Head title="Kasir" />
  <CashierLayout>
    <ProductCatalog
      v-model:search="search"
      :products="productItems"
      :categories="categories"
      :selected-category="selectedCategory"
      :loading="productsLoading"
      :loading-more="loadingMoreProducts"
      :has-next="Boolean(nextProductsUrl)"
      :categories-has-next="Boolean(nextCategoriesUrl)"
      :categories-loading-more="loadingMoreCategories"
      @select-category="selectCategory"
      @add="addToCart"
      @load-more="requestProducts(nextProductsUrl, true)"
      @load-more-categories="loadMoreCategories"
    />
    <div class="space-y-4">
      <div class="flex justify-end">
        <ThermalPaperSetting />
      </div>
      <CashierCustomerCard
        ref="customerCard"
        :customer-id="customerId"
        :customer-endpoint="
          route('search.customers') + '?branch_id=' + encodeURIComponent(selectedBranch)
        "
        :selected-branch="selectedBranch"
        @update:customer-id="customerId = $event"
        @add-customer="customerModalOpen = true"
      />
      <CashierCart
        v-model:selected-branch="selectedBranch"
        v-model:note="note"
        v-model:discount="discount"
        :cart="cart"
        :branches="branches"
        :subtotal="subtotal"
        :tax="tax"
        :tax-rate="taxRate"
        :tax-name="taxConfiguration?.name"
        :tax-mode="taxMode"
        :total="total"
        :is-submitting="isSubmitting"
        @clear="clearCart"
        @change-quantity="changeQuantity"
        @set-quantity="setQuantity"
        @remove="removeFromCart"
        @checkout="checkout"
      />
    </div>
    <CashierPaymentModal
      v-if="paymentOpen"
      :total="total"
      :transaction-date="new Date().toISOString().slice(0, 10)"
      :is-submitting="isSubmitting"
      @close="paymentOpen = false"
      @submit="submitCheckout"
    />
    <CustomerModal
      v-if="customerModalOpen"
      :branch-id="selectedBranch"
      @close="customerModalOpen = false"
      @saved="
        (customer) => {
          customerModalOpen = false
          customerCard?.selectCustomer(customer)
        }
      "
    />
  </CashierLayout>
</template>
