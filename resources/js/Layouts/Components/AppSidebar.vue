<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import {
  Bell,
  Boxes,
  ChartNoAxesCombined,
  ChevronLeft,
  CircleDollarSign,
  Globe,
  Landmark,
  LayoutDashboard,
  Package,
  ShieldCheck,
  ShoppingBag,
  ShoppingCart,
  Store,
  Truck,
  Users,
  X,
} from '@lucide/vue'
import { computed } from 'vue'
import { useRoute } from 'ziggy-js'

const props = defineProps({ open: { type: Boolean, default: false } })
const emit = defineEmits(['close'])

const page = usePage()
const route = useRoute(page.props.ziggy)
const currentRoute = computed(() => {
  const location = new URL(page.url, page.props.ziggy.url)

  return useRoute({ ...page.props.ziggy, location })
})
const permissions = computed(() => page.props.auth?.permissions ?? [])

const can = (permission) => {
  if (!permission) return true
  if (!Array.isArray(permissions.value)) return false
  if (Array.isArray(permission)) return permission.some((item) => permissions.value.includes(item))
  return permissions.value.includes(permission)
}

const PRODUCT_TABS = [
  { id: 'products', route: () => route('products.index'), permission: 'products.view' },
  {
    id: 'product_categories',
    route: () => route('products.master.index', 'product_categories'),
    permission: 'product_categories.view',
  },
  {
    id: 'product_brands',
    route: () => route('products.master.index', 'product_brands'),
    permission: 'product_brands.view',
  },
  { id: 'units', route: () => route('products.master.index', 'units'), permission: 'units.view' },
]

const visibleProductTabs = computed(() => PRODUCT_TABS.filter((tab) => can(tab.permission)))

const productHome = computed(() => visibleProductTabs.value[0]?.route() ?? route('products.index'))

const canViewProducts = computed(() => visibleProductTabs.value.length > 0)

const routeMatches = (pattern) => Boolean(pattern && currentRoute.value().current(pattern))
const canView = (item) => can(item.permission)
const menuRoute = (item) =>
  item.label === 'Persediaan' && !can('inventory.summary.view') ? 'inventory.stock' : item.route

const sections = [
  {
    title: 'Transaksi',
    items: [
      {
        label: 'Persediaan',
        route: 'inventory.index',
        activePattern: 'inventory.*',
        icon: Boxes,
        permission: [
          'inventory.summary.view',
          'inventory.stock.view',
          'inventory.movements.view',
          'inventory.transfers.view',
          'inventory.opname.view',
        ],
      },
      {
        label: 'Penjualan',
        route: 'sales.index',
        activePattern: 'sales.*',
        icon: ShoppingCart,
        permission: 'penjualan.summary.view',
      },
      {
        label: 'Pembelian',
        route: 'purchases.index',
        activePattern: 'purchases.*',
        icon: ShoppingBag,
        permission: 'pembelian.view',
      },
      {
        label: 'Kas & Bank',
        route: 'cash-bank.transactions.index',
        activePattern: 'cash-bank.*',
        icon: CircleDollarSign,
        permission: 'cash_bank.view',
      },
      {
        label: 'Laporan & Analitik',
        route: 'reports.sales',
        activePattern: 'reports.*',
        icon: ChartNoAxesCombined,
        permission: 'laporan.view',
      },
      {
        label: 'Reminder',
        route: 'reminders.index',
        activePattern: 'reminders.*',
        icon: Bell,
        permission: 'reminders.view',
      },
      {
        label: 'Akuntansi',
        route: 'accounting.index',
        activePattern: 'accounting.*',
        icon: Landmark,
        permission: 'accounting.view',
      },
    ],
  },
  {
    title: 'E-Commerce',
    items: [
      {
        label: 'Toko Online',
        route: 'ecommerce.settings.editor',
        activePattern: 'ecommerce.*',
        icon: Globe,
        permission: 'store.settings.view',
      },
    ],
  },
  {
    title: 'Pengaturan',
    items: [
      {
        label: 'Profil Bisnis',
        route: 'company.edit',
        activePattern: 'company.*',
        icon: Store,
        permission: 'company.view',
      },
      {
        label: 'Role',
        route: 'roles.index',
        activePattern: 'roles.*',
        icon: ShieldCheck,
        permission: 'roles.view',
      },
    ],
  },
]

const matchesPattern = (pattern) =>
  Array.isArray(pattern) ? pattern.some(routeMatches) : routeMatches(pattern)
const itemActive = (item) => matchesPattern(item.activePattern ?? item.route)
const visibleSections = computed(() =>
  sections
    .map((section) => ({ ...section, items: section.items.filter(canView) }))
    .filter((section) => section.items.length > 0)
)
const canViewMasterData = computed(
  () => canViewProducts.value || can('suppliers.view') || can('users.view')
)

const baseClasses =
  'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-4 focus:ring-emerald-500/10'
const inactiveClasses =
  'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-300 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300'
const activeClasses =
  'bg-emerald-100 text-emerald-800 shadow-sm ring-1 ring-inset ring-emerald-200 dark:bg-emerald-400/15 dark:text-emerald-300 dark:ring-emerald-400/20'
const linkClasses = (item) => [baseClasses, itemActive(item) ? activeClasses : inactiveClasses]
const parentClasses = (active) => [baseClasses, active ? activeClasses : inactiveClasses]
</script>

<template>
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-200 bg-white transition-transform duration-300 dark:border-[#1d3859] dark:bg-[#0a1b33] lg:translate-x-0',
      props.open ? 'translate-x-0' : '-translate-x-full',
    ]"
  >
    <div
      class="flex h-[72px] items-center justify-between border-b border-slate-100 px-5 dark:border-[#1d3859]"
    >
      <Link :href="route('dashboard')" class="flex items-center gap-2.5">
        <img
          src="/images/logo.webp"
          alt="Kastra"
          class="h-9 w-9 rounded-lg object-cover dark:hidden"
        />
        <img
          src="/images/logo-white.webp"
          alt="Kastra"
          class="hidden h-9 w-9 rounded-lg object-cover dark:block"
        />
        <span class="text-xl font-semibold text-emerald-600"
          >Kastra <span class="font-semibold text-slate-500 dark:text-slate-300">ERP</span></span
        >
      </Link>
      <button
        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-[#102542] lg:hidden"
        aria-label="Tutup navigasi"
        @click="emit('close')"
      >
        <X :size="20" />
      </button>
    </div>

    <nav class="sidebar-scroll flex-1 space-y-6 overflow-y-auto px-4 py-5">
      <Link
        :href="route('dashboard')"
        :class="parentClasses(routeMatches('dashboard'))"
        @click="emit('close')"
        ><LayoutDashboard :size="18" />Dashboard</Link
      >

      <section v-if="canViewMasterData" class="space-y-1">
        <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
          Data Master
        </p>
        <Link
          v-if="canViewProducts"
          :href="productHome"
          :class="linkClasses({ route: 'products.index', activePattern: 'products.*' })"
          @click="emit('close')"
          ><Package :size="18" />Produk</Link
        >
        <Link
          v-if="can('suppliers.view')"
          :href="route('suppliers.index')"
          :class="linkClasses({ route: 'suppliers.index', activePattern: 'suppliers.*' })"
          @click="emit('close')"
          ><Truck :size="18" />Supplier</Link
        >
        <Link
          v-if="can('users.view')"
          :href="route('users.index')"
          :class="linkClasses({ route: 'users.index', activePattern: 'users.*' })"
          @click="emit('close')"
          ><Users :size="18" />Pengguna</Link
        >
      </section>

      <section v-for="section in visibleSections" :key="section.title" class="space-y-1">
        <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
          {{ section.title }}
        </p>
        <template v-for="item in section.items" :key="item.label">
          <Link
            v-if="item.route"
            :href="route(menuRoute(item))"
            :class="linkClasses(item)"
            @click="emit('close')"
            ><component :is="item.icon" :size="18" />{{ item.label }}</Link
          >
          <div
            v-else
            :class="[baseClasses, 'cursor-not-allowed text-slate-400 dark:text-slate-600']"
            :title="`${item.label} segera tersedia`"
          >
            <component :is="item.icon" :size="18" />{{ item.label
            }}<span
              class="ml-auto rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:bg-[#102542]"
              >Segera</span
            >
          </div>
        </template>
      </section>
    </nav>

    <div class="border-t border-slate-100 p-4 dark:border-[#1d3859]">
      <button
        class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-[#102542] dark:hover:text-white"
      >
        <ChevronLeft :size="18" />Sembunyikan
      </button>
    </div>
  </aside>
  <button
    v-if="props.open"
    class="fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-sm lg:hidden"
    aria-label="Tutup navigasi"
    @click="emit('close')"
  ></button>
</template>

<style scoped>
.sidebar-scroll {
  scrollbar-width: none;
}

.sidebar-scroll::-webkit-scrollbar {
  display: none;
}
</style>
