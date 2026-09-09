<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { Building2, Plus, Warehouse } from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import OrganizationDeleteModal from '@/Pages/OrganizationDeleteModal.vue'
import OrganizationFormModal from '@/Pages/OrganizationFormModal.vue'
import OrganizationTable from '@/Pages/Organization/Components/OrganizationTable.vue'

const props = defineProps({
  type: { type: String, required: true },
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})
const page = usePage()
const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const sortKey = ref(typeof props.filters.sort === 'string' ? props.filters.sort : 'id')
const sortDirection = ref(
  typeof props.filters.sort_direction === 'string' ? props.filters.sort_direction : 'desc'
)
const selected = ref(null)
const modal = ref(null)
const loading = ref(false)
const title = computed(() => (props.type === 'cabang' ? 'Cabang' : 'Gudang'))
const icon = computed(() => (props.type === 'cabang' ? Building2 : Warehouse))
const routeName = computed(() =>
  props.type === 'cabang' ? 'company.branches' : 'company.warehouses'
)
const can = (action) => page.props.auth.permissions?.includes(`${props.type}.${action}`)
const open = (kind, item = null) => {
  selected.value = item
  modal.value = kind
}
const applyFilters = (perPage = props.items.per_page) => {
  loading.value = true
  router.get(
    route(routeName.value),
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
  if (url) router.visit(url, { preserveState: true, preserveScroll: true })
}
watch(status, () => applyFilters())
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div class="flex items-center gap-3">
        <span
          class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
          ><component :is="icon" :size="21"
        /></span>
        <div>
          <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Daftar {{ title }}</h2>
          <p class="mt-1 text-sm text-slate-500">
            Kelola data {{ title.toLowerCase() }} perusahaan.
          </p>
        </div>
      </div>
      <Button v-if="can('create')" @click="open('form')"
        ><Plus :size="17" class="mr-2" />Tambah {{ title }}</Button
      >
    </div>
    <DataPanel>
      <OrganizationTable
        :items="items"
        :type="type"
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
  </div>
</template>
