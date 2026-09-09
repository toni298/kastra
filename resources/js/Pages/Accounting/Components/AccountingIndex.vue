<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { FilePlus2, Landmark, ReceiptText, Settings2 } from '@lucide/vue'
import { computed, ref } from 'vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AccountingFormModal from './AccountingFormModal.vue'
import AccountingTable from './AccountingTable.vue'

const props = defineProps({
  type: { type: String, required: true },
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  options: { type: Array, default: () => [] },
})
const page = usePage()
const search = ref(props.filters.search ?? '')
const sortKey = ref(props.filters.sort ?? (props.type === 'numbers' ? 'document_type' : 'kode'))
const sortDirection = ref(props.filters.sort_direction ?? 'asc')
const modalOpen = ref(false)
const loading = ref(false)
const config = computed(
  () =>
    ({
      coa: {
        title: 'Chart of Accounts',
        description: 'Kelola struktur akun bertingkat perusahaan.',
        permission: 'coa',
        icon: Landmark,
        index: 'chart-of-accounts.index',
      },
      taxes: {
        title: 'Konfigurasi Pajak',
        description: 'Kelola tarif, jenis, mode, dan akun pajak.',
        permission: 'taxes',
        icon: ReceiptText,
        index: 'tax-configurations.index',
      },
      numbers: {
        title: 'Number Generator',
        description: 'Kelola format nomor dokumen perusahaan.',
        permission: 'number_generators',
        icon: Settings2,
        index: 'number-generators.index',
      },
    })[props.type]
)
const can = (action) =>
  page.props.auth.permissions?.includes(`${config.value.permission}.${action}`)
const applyFilters = (perPage = props.items.per_page) => {
  loading.value = true
  router.get(
    route(config.value.index),
    {
      search: search.value || undefined,
      per_page: perPage,
      sort: sortKey.value,
      sort_direction: sortDirection.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        loading.value = false
      },
    }
  )
}
const handleSearch = ({ search: value }) => {
  search.value = value
  applyFilters()
}
const handleSort = ({ key, direction }) => {
  sortKey.value = key
  sortDirection.value = direction
  applyFilters()
}
const navigate = (url) => url && router.visit(url, { preserveState: true, preserveScroll: true })
</script>
<template>
  <Head :title="config.title" />
  <AuthenticatedLayout>
    <template #header>{{ config.title }}</template>
    <div class="space-y-5">
      <PageHeader :title="config.title" :description="config.description">
        <template #icon><component :is="config.icon" :size="22" /></template>
        <template #actions>
          <Button v-if="can('create')" @click="modalOpen = true"
            ><FilePlus2 :size="17" class="mr-2" />Tambah {{ config.title }}</Button
          >
        </template>
      </PageHeader>
      <DataPanel>
        <AccountingTable
          :type="type"
          :items="items"
          :loading="loading"
          :search="search"
          :sort-key="sortKey"
          :sort-direction="sortDirection"
          @navigate="navigate"
          @filter="handleSearch"
          @per-page-change="applyFilters"
          @sort="handleSort"
        />
      </DataPanel>
    </div>
    <AccountingFormModal
      v-if="modalOpen"
      :type="type"
      :title="config.title"
      :options="options"
      @close="modalOpen = false"
    />
  </AuthenticatedLayout>
</template>
