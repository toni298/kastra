<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { Building2 } from 'lucide-vue-next'
import { computed } from 'vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useAuthorization } from '@/Composables/useAuthorization'

defineProps({
  activeTab: { type: String, default: 'information' },
})

const { can } = useAuthorization()
const tabs = computed(() =>
  [
    {
      id: 'information',
      label: 'Informasi',
      route: 'company.edit',
      permission: 'company.view',
    },
    { id: 'branches', label: 'Cabang', route: 'company.branches', permission: 'cabang.view' },
    {
      id: 'warehouses',
      label: 'Gudang',
      route: 'company.warehouses',
      permission: 'gudang.view',
    },
    { id: 'logo', label: 'Logo', route: 'company.logo', permission: 'company.logo' },
    { id: 'features', label: 'Fitur', route: 'company.features', permission: 'company.settings' },
  ].filter((tab) => can(tab.permission))
)
</script>

<template>
  <Head title="Profil Bisnis" />
  <AuthenticatedLayout>
    <template #header>Profil Bisnis</template>
    <div class="mx-auto space-y-5">
      <PageHeader
        title="Profil Bisnis"
        description="Kelola identitas, struktur operasional, dan konfigurasi perusahaan dalam satu tempat."
      >
        <template #icon><Building2 :size="22" /></template>
      </PageHeader>
      <nav
        class="border-b border-slate-200 dark:border-[#29476b]"
        aria-label="Navigasi Profil Bisnis"
      >
        <div class="flex gap-7 px-1">
          <Link
            v-for="tab in tabs"
            :key="tab.id"
            :href="route(tab.route)"
            preserve-scroll
            :class="[
              'relative pb-3 text-sm font-medium transition',
              activeTab === tab.id
                ? 'text-emerald-700 dark:text-emerald-300'
                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400',
            ]"
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
