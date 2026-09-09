<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  ChartNoAxesColumn,
  ClipboardList,
  Grid2X2,
  Landmark,
  Package,
  Receipt,
  Settings,
  ShoppingBag,
  ShoppingCart,
} from 'lucide-vue-next'
import { useRoute } from 'ziggy-js'
import { useAccessControl } from '@/Composables/useAccessControl'

const page = usePage()
const route = useRoute(page.props.ziggy)
const currentRoute = computed(() => {
  const location = new URL(page.url, page.props.ziggy.url)
  return useRoute({ ...page.props.ziggy, location })
})
const { can, canAny } = useAccessControl()

const items = [
  { label: 'Kasir', route: 'cashier', icon: ShoppingCart, permission: 'cashier.access' },
  { label: 'Dashboard', route: 'cashier.dashboard', icon: Grid2X2, permission: 'cashier.access' },
  { label: 'Produk', route: 'cashier.products', icon: Package, permission: 'products.view' },
  {
    label: 'Inventory',
    route: 'cashier.inventory',
    icon: ClipboardList,
    permission: [
      'inventory.summary.view',
      'inventory.stock.view',
      'inventory.transfers.view',
      'inventory.opname.view',
    ],
  },
  {
    label: 'Pembelian',
    route: 'cashier.purchases',
    icon: ShoppingBag,
    permission: 'pembelian.view',
  },
  {
    label: 'Penjualan',
    route: 'cashier.sales',
    icon: Receipt,
    permission: [
      'penjualan.summary.view',
      'penjualan.transactions.view',
      'penjualan.returns.view',
      'penjualan.customers.view',
    ],
  },
  {
    label: 'Laporan',
    route: 'cashier.reports',
    icon: ChartNoAxesColumn,
    permission: 'laporan.view',
  },
  { label: 'Akuntansi', route: 'accounting.index', icon: Landmark, permission: 'accounting.view' },
  { label: 'Pengaturan', route: 'company.edit', icon: Settings, permission: 'company.view' },
]
const visibleItems = computed(() =>
  items.filter((item) =>
    Array.isArray(item.permission) ? canAny(item.permission) : can(item.permission)
  )
)
const isActive = (item) => {
  const pathname = new URL(page.url, page.props.ziggy.url).pathname
  const activePrefixes = {
    'cashier.products': '/cashier/product',
    'cashier.inventory': '/cashier/inventory',
    'cashier.purchases': '/cashier/purchases',
    'cashier.sales': '/cashier/sales',
    'cashier.reports': '/cashier/reports',
    'cashier.dashboard': '/cashier/dashboard',
  }

  if (activePrefixes[item.route]) {
    return pathname.startsWith(activePrefixes[item.route])
  }

  return Boolean(currentRoute.value().current(item.route))
}
</script>

<template>
  <nav class="hidden min-w-0 flex-1 items-stretch gap-1 lg:flex" aria-label="Navigasi kasir">
    <Link
      v-for="item in visibleItems"
      :key="item.route"
      :href="route(item.route)"
      :class="[
        'flex shrink-0 flex-col items-center justify-center gap-1 rounded-xl px-3 py-2 text-[11px] font-medium transition',
        isActive(item)
          ? 'bg-blue-50 text-blue-700'
          : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900',
      ]"
      :aria-current="isActive(item) ? 'page' : undefined"
    >
      <component :is="item.icon" :size="18" />{{ item.label }}
    </Link>
  </nav>
</template>
