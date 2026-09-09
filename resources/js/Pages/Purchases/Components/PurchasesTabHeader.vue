<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useAccessControl } from '@/Composables/useAccessControl'

const props = defineProps({
  active: { type: String, required: true },
  cashierLayout: { type: Boolean, default: false },
})
const { can } = useAccessControl()

const tabs = computed(() => [
  {
    id: 'transactions',
    label: 'Transaksi',
    route: 'purchases.transactions.index',
    permission: 'pembelian.view',
  },
  {
    id: 'suppliers',
    label: 'Supplier',
    route: 'purchases.suppliers.index',
    permission: 'suppliers.view',
  },
  {
    id: 'returns',
    label: 'Retur',
    route: 'purchases.returns.index',
    permission: 'pembelian.returns.view',
  },
])
const visibleTabs = computed(() => tabs.value.filter((tab) => can(tab.permission)))
const tabRoute = (tab) => {
  if (!props.cashierLayout) return tab.route

  return {
    transactions: 'cashier.purchases',
    suppliers: 'cashier.purchases.suppliers',
    returns: 'cashier.purchases.returns',
  }[tab.id]
}
</script>
<template>
  <nav class="border-b border-slate-200 dark:border-[#29476b]" aria-label="Navigasi pembelian">
    <div class="flex flex-wrap gap-x-7 gap-y-3 px-1" role="tablist">
      <Link
        v-for="tab in visibleTabs"
        :key="tab.id"
        :href="route(tabRoute(tab))"
        class="relative pb-3 text-sm font-medium transition"
        :class="
          active === tab.id
            ? 'text-emerald-700 dark:text-emerald-300'
            : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'
        "
        :aria-current="active === tab.id ? 'page' : undefined"
        >{{ tab.label
        }}<span
          v-if="active === tab.id"
          class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-emerald-600"
        ></span
      ></Link>
    </div>
  </nav>
</template>
