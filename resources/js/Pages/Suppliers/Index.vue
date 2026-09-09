<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { Plus, Truck } from '@lucide/vue'
import { ref, watch } from 'vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SupplierDeleteModal from './Components/SupplierDeleteModal.vue'
import SupplierDetailModal from './Components/SupplierDetailModal.vue'
import SupplierFormModal from './Components/SupplierFormModal.vue'
import SupplierMasterTable from './Components/SupplierMasterTable.vue'

const props = defineProps({
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()
const search = ref(props.filters.search ?? '')
const sortKey = ref(typeof props.filters.sort === 'string' ? props.filters.sort : 'created_at')
const sortDirection = ref(['asc', 'desc'].includes(props.filters.sort_direction) ? props.filters.sort_direction : 'desc')
const selected = ref(null)
const modal = ref(null)
const loading = ref(false)

const can = (action) => page.props.auth.permissions?.includes(`suppliers.${action}`)

const open = (kind, supplier = null) => {
  selected.value = supplier
  modal.value = kind
}

const applyFilters = (perPage = props.items.per_page) => {
  loading.value = true
  router.get(
    route('suppliers.index'),
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

const navigate = (url) => {
  if (!url) return
  loading.value = true
  router.visit(url, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      loading.value = false
    },
  })
}

watch(
  () => props.filters.search,
  (value) => {
    const nextSearch = value ?? ''
    if (nextSearch !== search.value) search.value = nextSearch
  }
)
</script>

<template>
  <Head title="Supplier" />
  <AuthenticatedLayout>
    <template #header>Supplier</template>

    <div class="space-y-5">
      <PageHeader
        title="Manajemen Supplier"
        description="Kelola data supplier perusahaan dalam satu tempat."
      >
        <template #icon><Truck :size="22" /></template>
      </PageHeader>

      <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Daftar Supplier</h2>
          <p class="mt-1 text-sm text-slate-500">Kelola supplier yang digunakan untuk pembelian.</p>
        </div>
        <Button v-if="can('create')" size="sm" @click="open('form')">
          <Plus :size="17" class="mr-2" />Tambah Supplier
        </Button>
      </div>

      <DataPanel>
        <SupplierMasterTable
          :items="items"
          :loading="loading"
          :search="search"
          :sort-key="sortKey"
          :sort-direction="sortDirection"
          :can-edit="can('edit')"
          :can-delete="can('delete')"
          @navigate="navigate"
          @filter="handleSearch"
          @per-page-change="applyFilters"
          @sort="handleSort"
          @detail="open('detail', $event)"
          @edit="open('form', $event)"
          @delete="open('delete', $event)"
        />
      </DataPanel>
    </div>

    <SupplierFormModal v-if="modal === 'form'" :supplier="selected" @close="modal = null" />
    <SupplierDetailModal v-if="modal === 'detail'" :supplier="selected" @close="modal = null" />
    <SupplierDeleteModal v-if="modal === 'delete'" :supplier="selected" @close="modal = null" />
  </AuthenticatedLayout>
</template>
