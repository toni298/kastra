<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useAccessControl } from '@/Composables/useAccessControl'

defineProps({
  active: { type: String, required: true },
  accountingEnabled: { type: Boolean, default: true },
})
const { can } = useAccessControl()
const tabs = computed(() => [
  {
    id: 'transactions',
    label: 'Transaksi',
    route: 'cash-bank.transactions.index',
    permission: 'cash_bank.transactions.view',
  },
  {
    id: 'accounts',
    label: 'Rekening & Kas',
    route: 'cash-bank.accounts.index',
    permission: 'cash_bank.accounts.view',
  },
  {
    id: 'transfers',
    label: 'Transfer',
    route: 'cash-bank.transfers.index',
    permission: 'cash_bank.transfers.view',
  },
])
const visibleTabs = computed(() => tabs.value.filter((tab) => can(tab.permission)))
</script>
<template>
  <nav class="border-b border-slate-200 dark:border-[#29476b]" aria-label="Navigasi Kas dan Bank">
    <div class="flex flex-wrap gap-x-7 gap-y-3 px-1" role="tablist">
      <Link
        v-for="tab in visibleTabs"
        :key="tab.id"
        :href="route(tab.route)"
        preserve-state
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
