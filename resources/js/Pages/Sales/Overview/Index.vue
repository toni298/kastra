<script setup>
import { Head } from '@inertiajs/vue3'
import { ShoppingCart } from 'lucide-vue-next'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SalesTabHeader from '../Components/SalesTabHeader.vue'
import SalesOverview from '../Components/SalesOverview.vue'
</script>

<template>
  <Head title="Ringkasan Penjualan" />
  <AuthenticatedLayout>
    <template #header>Penjualan</template>
    <div class="space-y-5">
      <PageHeader title="Penjualan" description="Ringkasan aktivitas dan performa penjualan.">
        <template #icon><ShoppingCart :size="22" /></template>
      </PageHeader>
      <SalesTabHeader active="overview" />
      <SalesOverview />
    </div>
  </AuthenticatedLayout>
</template>
