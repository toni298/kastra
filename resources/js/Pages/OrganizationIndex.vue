<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { Building2, MapPin, Plus, Warehouse } from '@lucide/vue'
import { computed, ref, watch } from 'vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import OrganizationDeleteModal from '@/Pages/OrganizationDeleteModal.vue'
import OrganizationFormModal from '@/Pages/OrganizationFormModal.vue'
import OrganizationTable from '@/Pages/Organization/Components/OrganizationTable.vue'

const props = defineProps({
  title: { type: String, required: true },
  type: { type: String, required: true },
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()
const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const sortKey = ref(props.filters.sort ?? 'id')
const sortDirection = ref(props.filters.sort_direction ?? 'desc')
const selected = ref(null)
const modal = ref(null)
const isNavigating = ref(false)
const icon = computed(() => ({ cabang: Building2, outlet: MapPin, gudang: Warehouse })[props.type])
const description = computed(() => `Kelola data ${props.title.toLowerCase()} perusahaan.`)
const can = (action) => page.props.auth.permissions?.includes(`${props.type}.${action}`)

const open = (kind, item = null) => {
  selected.value = item
  modal.value = kind
}

const applyFilters = (perPage = props.items.per_page) => {
  isNavigating.value = true
  router.get(
    route(`${props.type}.index`),
    {
      search: search.value || undefined,
      status: status.value || undefined,
      per_page: perPage,
      sort: sortKey.value,
      sort_direction: sortDirection.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        isNavigating.value = false
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

watch(status, () => applyFilters())

const navigate = (url) => {
  if (!url) return

  isNavigating.value = true
  router.visit(url, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      isNavigating.value = false
    },
  })
}
</script>

<template>
  <Head :title="title" />
  <AuthenticatedLayout>
    <template #header>{{ title }}</template>
    <div class="space-y-5">
      <PageHeader :title="title" :description="description">
        <template #icon><component :is="icon" :size="22" /></template>
        <template #actions>
          <Button v-if="can('create')" @click="open('form')">
            <Plus :size="17" class="mr-2" />Tambah {{ title }}
          </Button>
        </template>
      </PageHeader>

      <DataPanel>
        <OrganizationTable
          :items="items"
          :type="type"
          :loading="isNavigating"
          :search="search"
          :sort-key="sortKey"
          :sort-direction="sortDirection"
          :can-edit="can('edit')"
          :can-delete="can('delete')"
          @navigate="navigate"
          @filter="handleSearch"
          @per-page-change="applyFilters"
          @sort="handleSort"
          @edit="open('form', $event)"
          @delete="open('delete', $event)"
        >
          <template #filters>
            <SelectInput v-model="status" aria-label="Filter berdasarkan status">
              <option value="">Semua status</option>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </SelectInput>
          </template>
        </OrganizationTable>
      </DataPanel>
    </div>

    <OrganizationFormModal
      v-if="modal === 'form'"
      :item="selected"
      :type="type"
      @close="modal = null"
    />
    <OrganizationDeleteModal
      v-if="modal === 'delete'"
      :item="selected"
      :type="type"
      @close="modal = null"
    />
  </AuthenticatedLayout>
</template>
