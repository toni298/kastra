<script setup>
import { useAccessControl } from '@/Composables/useAccessControl'
import { Link, usePage } from '@inertiajs/vue3'
import { ChevronLeft, X } from 'lucide-vue-next'
import { computed, h } from 'vue'
import { useRoute } from '../../../../vendor/tightenco/ziggy/src/js'

const props = defineProps({ open: { type: Boolean, default: false } })
const emit = defineEmits(['close'])

const page = usePage()
const route = useRoute(page.props.ziggy)
const { can, menus } = useAccessControl()

const visibleMenus = computed(() =>
  menus.value.filter(
    (menu) => !menu.section || (Array.isArray(menu.items) && menu.items.length > 0)
  )
)

// Icon mapping
const ICON_MAP = {
  LayoutDashboard: 'LayoutDashboard',
  Package: 'Package',
  Truck: 'Truck',
  Users: 'Users',
  Boxes: 'Boxes',
  ShoppingCart: 'ShoppingCart',
  ShoppingBag: 'ShoppingBag',
  CircleDollarSign: 'CircleDollarSign',
  Landmark: 'Landmark',
  Store: 'Store',
  ShieldCheck: 'ShieldCheck',
}

const routeMatches = (pattern) => Boolean(pattern && route().current(pattern))

const matchesPattern = (patterns) => {
  if (!patterns) return false
  if (Array.isArray(patterns)) {
    return patterns.some((pattern) => {
      if (pattern.endsWith('.*')) {
        return route().current(pattern)
      }
      return route().current(pattern)
    })
  }
  return routeMatches(patterns)
}

const itemActive = (item) => {
  if (!item) return false
  if (item.activePattern) {
    return matchesPattern(item.activePattern)
  }
  if (item.route) {
    return route().current(item.route)
  }
  return false
}

const baseClasses =
  'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-4 focus:ring-emerald-500/10'
const inactiveClasses =
  'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-300 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300'
const activeClasses =
  'bg-emerald-100 text-emerald-800 shadow-sm ring-1 ring-inset ring-emerald-200 dark:bg-emerald-400/15 dark:text-emerald-300 dark:ring-emerald-400/20'

const linkClasses = (item) => [baseClasses, itemActive(item) ? activeClasses : inactiveClasses]
const parentClasses = (active) => [baseClasses, active ? activeClasses : inactiveClasses]

/**
 * Render icon dengan dynamic import dari lucide-vue-next
 */
const renderIcon = (iconName) => {
  if (!iconName) return null
  try {
    // Import dinamis dari lucide-vue-next
    const iconModule = require('lucide-vue-next')
    const IconComponent = iconModule[iconName]
    return IconComponent ? h(IconComponent, { size: 18 }) : null
  } catch {
    return null
  }
}
</script>

<template>
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-200 bg-white transition-transform duration-300 dark:border-[#1d3859] dark:bg-[#0a1b33] lg:translate-x-0',
      props.open ? 'translate-x-0' : '-translate-x-full',
    ]"
  >
    <!-- Header -->
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

    <!-- Navigation -->
    <nav class="sidebar-scroll flex-1 space-y-6 overflow-y-auto px-4 py-5">
      <!-- Dashboard -->
      <Link
        :href="route('dashboard')"
        :class="parentClasses(route().current('dashboard'))"
        @click="emit('close')"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="size-[18px]"
        >
          <rect width="7" height="7" x="3" y="3" />
          <rect width="7" height="7" x="14" y="3" />
          <rect width="7" height="7" x="14" y="14" />
          <rect width="7" height="7" x="3" y="14" />
        </svg>
        Dashboard
      </Link>

      <!-- Dynamic Menus -->
      <template v-for="menu in visibleMenus" :key="menu.id">
        <!-- Section (Group) -->
        <section v-if="menu.section" class="space-y-1">
          <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
            {{ menu.label }}
          </p>

          <!-- Section Items -->
          <template v-for="item in menu.items" :key="item.id">
            <Link
              v-if="item.route"
              :href="route(item.route)"
              :class="linkClasses(item)"
              @click="emit('close')"
            >
              <!-- Inline SVG untuk icon standar -->
              <component v-if="item.icon" :is="`Svg${item.icon}`" class="size-[18px]" />
              {{ item.label }}
            </Link>

            <!-- Sub-menu (children) -->
            <template v-if="item.children && item.children.length > 0">
              <div v-for="child in item.children" :key="`${item.id}-${child.route}`">
                <Link
                  :href="route(child.route, child.params)"
                  class="ml-8 flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-medium text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-slate-400 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-300"
                  @click="emit('close')"
                >
                  <span class="flex-1">{{ child.label }}</span>
                </Link>
              </div>
            </template>
          </template>
        </section>
      </template>
    </nav>

    <!-- Footer -->
    <div class="border-t border-slate-100 p-4 dark:border-[#1d3859]">
      <button
        class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-[#102542] dark:hover:text-white"
      >
        <ChevronLeft :size="18" />
        Sembunyikan
      </button>
    </div>
  </aside>

  <!-- Overlay -->
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
