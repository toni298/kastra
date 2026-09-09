<script setup>
import { Link } from '@inertiajs/vue3'
import { ShoppingCart } from 'lucide-vue-next'
import { computed } from 'vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useAuthorization } from '@/Composables/useAuthorization'

defineProps({
  activeTab: { type: String, default: 'appearance' },
  title: { type: String, default: 'E-Commerce' },
  description: { type: String, default: 'Kelola pengaturan toko online Anda.' },
})

const { can } = useAuthorization()
const tabs = computed(() =>
  [
    { id: 'appearance', label: 'Tampilan Toko', route: 'ecommerce.settings.appearance', permission: 'store.settings.view' },
    { id: 'payment', label: 'Pembayaran', route: 'ecommerce.settings.payment', permission: 'store.settings.view' },
    { id: 'shipping', label: 'Pengiriman', route: 'ecommerce.settings.shipping', permission: 'store.settings.view' },
    { id: 'domain', label: 'Domain', route: 'ecommerce.settings.domain', permission: 'store.settings.view' },
  ].filter((tab) => can(tab.permission))
)
</script>

<template>
  <AuthenticatedLayout>
    <div class="space-y-5">
      <PageHeader :title="title" :description="description">
        <template #icon><ShoppingCart :size="20" /></template>
      </PageHeader>

      <nav class="border-b border-slate-200 dark:border-[#29476b]">
        <div class="-mb-px flex flex-wrap gap-x-6 gap-y-2">
          <Link
            v-for="tab in tabs"
            :key="tab.id"
            :href="route(tab.route)"
            class="relative px-1 pb-3 text-sm font-medium transition"
            :class="
              activeTab === tab.id
                ? 'text-emerald-600 dark:text-emerald-400'
                : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
            "
          >
            {{ tab.label }}
            <span
              v-if="activeTab === tab.id"
              class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-emerald-600"
            ></span>
          </Link>
        </div>
      </nav>
      <slot></slot>
    </div>
  </AuthenticatedLayout>
</template>
