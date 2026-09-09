<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { BookOpen } from 'lucide-vue-next'
import { computed } from 'vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({
  activeTab: { type: String, default: 'overview' },
})

const page = usePage()
const permissions = computed(() => page.props.auth?.permissions ?? [])
const tabs = [
  { id: 'overview', label: 'Overview', route: 'accounting.index', permission: 'accounting.view' },
  {
    id: 'reports',
    label: 'Laporan',
    route: 'accounting.reports.index',
    permission: 'laporan.view',
  },
  {
    id: 'settings',
    label: 'Pengaturan',
    route: 'accounting.settings.index',
    permission: 'coa.view',
  },
]
const visibleTabs = computed(() => tabs.filter((tab) => permissions.value.includes(tab.permission)))
</script>

<template>
  <Head title="Akuntansi" />
  <AuthenticatedLayout>
    <template #header>Akuntansi</template>
    <div class="space-y-5">
      <PageHeader
        title="Akuntansi"
        description="Pantau kesehatan pembukuan, laporan keuangan, dan aktivitas akuntansi yang dibuat secara otomatis oleh Kastra."
      >
        <template #icon><BookOpen :size="22" /></template>
        <template #actions><slot name="actions"></slot></template>
      </PageHeader>
      <nav
        class="accounting-tabs-scroll overflow-x-auto border-b border-slate-200 dark:border-[#29476b]"
      >
        <div class="flex min-w-max gap-7 px-1" role="tablist">
          <Link
            v-for="tab in visibleTabs"
            :key="tab.id"
            :href="route(tab.route)"
            :class="[
              'relative pb-3 text-sm font-medium transition',
              activeTab === tab.id
                ? 'text-emerald-700 dark:text-emerald-300'
                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400',
            ]"
            :aria-current="activeTab === tab.id ? 'page' : undefined"
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

<style scoped>
.accounting-tabs-scroll {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.accounting-tabs-scroll::-webkit-scrollbar {
  display: none;
}
</style>
