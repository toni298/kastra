<script setup>
import { Eye, FilePenLine, Plus, Trash2 } from '@lucide/vue'
import { computed, ref } from 'vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import Modal from '@/Components/UI/Modal.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import { useAuthorization } from '@/Composables/useAuthorization'
import { useProductPanelState } from '../Composables/useProductPanelState'
import ProductMasterDeleteModal from './ProductMasterDeleteModal.vue'
import ProductMasterFormModal from './ProductMasterFormModal.vue'

const props = defineProps({
  master: { type: String, required: true },
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['request', 'form', 'detail', 'delete', 'mutated'])

const { can: canPermission } = useAuthorization()

const config = computed(() => {
  const defaults = {
    label: 'Data',
    title: 'Daftar Data',
    description: 'Kelola data yang digunakan produk.',
    createLabel: 'Tambah Data',
    searchPlaceholder: 'Cari data...',
    emptyMessage: 'Belum ada data yang tersedia.',
    detailDescription: 'Informasi data produk.',
    entityLabel: 'data',
    filter: false,
  }

  return (
    {
      product_categories: {
        label: 'Kategori',
        title: 'Daftar Kategori',
        description: 'Kelola kategori produk yang tersedia untuk operasional.',
        createLabel: 'Tambah Kategori',
        searchPlaceholder: 'Cari kategori...',
        emptyMessage: 'Belum ada kategori yang tersedia.',
        detailDescription: 'Informasi kategori produk.',
        entityLabel: 'kategori',
        filter: true,
      },
      product_brands: {
        label: 'Brand',
        title: 'Daftar Brand',
        description: 'Kelola brand produk yang tersedia untuk operasional.',
        createLabel: 'Tambah Brand',
        searchPlaceholder: 'Cari brand...',
        emptyMessage: 'Belum ada brand yang tersedia.',
        detailDescription: 'Informasi brand produk.',
        entityLabel: 'brand',
        filter: false,
      },
      units: {
        label: 'Satuan',
        title: 'Daftar Satuan',
        description: 'Kelola satuan produk yang tersedia untuk operasional.',
        createLabel: 'Tambah Satuan',
        searchPlaceholder: 'Cari satuan...',
        emptyMessage: 'Belum ada satuan yang tersedia.',
        detailDescription: 'Informasi satuan produk.',
        entityLabel: 'satuan',
        filter: false,
      },
    }[props.master] ?? defaults
  )
})
const columns = computed(() => [
  { key: 'name', label: 'Nama', sortable: true },
  ...(props.master === 'units' ? [{ key: 'code', label: 'Kode', sortable: true }] : []),
  { key: 'products_count', label: 'Digunakan' },
  {
    key: 'is_active',
    label: 'Status',
    sortable: true,
    align: 'center',
    headerClass: 'text-center',
    class: 'text-center',
  },
  { key: 'actions', label: 'Aksi', headerClass: 'text-right', class: 'text-right' },
])
const detailRows = computed(() => {
  if (!selected.value) return []

  return [
    ['Status', selected.value.is_active ? 'Aktif' : 'Nonaktif'],
    ['Digunakan', `${selected.value.products_count} produk`],
    ...(props.master === 'units' ? [['Kode', selected.value.code]] : []),
  ]
})

const modal = ref(null)
const selected = ref(null)

const can = (action) => canPermission(`${props.master}.${action}`)

const open = (kind, item = null) => {
  selected.value = item
  modal.value = kind
}

const close = () => {
  modal.value = null
}

const { search, status, sortKey, sortDirection, applyFilters, handleSearch, navigate, handleSort } =
  useProductPanelState({
    props,
    emit,
    tab: props.master,
    defaultSort: 'name',
    defaultSortDirection: 'asc',
    routeResolver: () => route('products.master.index', props.master),
    includeStatus: true,
  })

const handleMutation = () => {
  close()
  emit('mutated')
}
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">
          {{ config.title }}
        </h2>
        <p class="mt-1 text-sm text-slate-500">
          {{ config.description }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button v-if="can('create')" size="sm" @click="open('form')">
          <Plus :size="16" class="mr-2" />
          {{ config.createLabel }}
        </Button>
      </div>
    </div>

    <DataPanel>
      <DataTable
        :items="items.data ?? []"
        :columns="columns"
        :pagination="items"
        :per-page="items.per_page ?? 10"
        :loading="loading"
        searchable
        :search="search"
        :sort-key="sortKey"
        :sort-direction="sortDirection"
        :search-placeholder="config.searchPlaceholder"
        empty-icon="inbox"
        empty-title="Tidak ada data"
        :empty-message="config.emptyMessage"
        @filter="handleSearch"
        @navigate="navigate"
        @per-page-change="applyFilters"
        @sort="handleSort"
      >
        <template v-if="config.filter" #filters>
          <SelectInput v-model="status" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
          </SelectInput>
        </template>

        <template #cell-name="{ item }">
          <Button
            variant="ghost"
            size="sm"
            class="!p-0 font-medium text-slate-900 hover:bg-transparent hover:text-emerald-700 dark:text-white dark:hover:bg-transparent dark:hover:text-emerald-300"
            @click="open('detail', item)"
          >
            {{ item.name }}
          </Button>
        </template>

        <template #cell-products_count="{ item }"> {{ item.products_count }} produk </template>

        <template #cell-is_active="{ item }">
          <Badge :variant="item.is_active ? 'success' : 'error'">
            {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
          </Badge>
        </template>

        <template #cell-actions="{ item }">
          <div class="flex justify-end gap-1">
            <IconButton label="Lihat detail" @click="open('detail', item)">
              <Eye :size="17" />
            </IconButton>
            <IconButton v-if="can('edit')" label="Edit" variant="info" @click="open('form', item)">
              <FilePenLine :size="17" />
            </IconButton>
            <IconButton
              v-if="can('delete')"
              label="Hapus"
              variant="danger"
              @click="open('delete', item)"
            >
              <Trash2 :size="17" />
            </IconButton>
          </div>
        </template>
      </DataTable>
    </DataPanel>

    <Modal
      :model-value="modal === 'detail'"
      :title="selected?.name ?? `Detail ${config.label}`"
      :description="config.detailDescription"
      size="md"
      @update:model-value="close"
    >
      <dl class="grid gap-3 sm:grid-cols-2">
        <div
          v-for="pair in detailRows"
          :key="pair[0]"
          class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]"
        >
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">
            {{ pair[0] }}
          </dt>
          <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ pair[1] }}</dd>
        </div>
      </dl>
    </Modal>

    <ProductMasterFormModal
      v-if="modal === 'form'"
      :item="selected"
      :master="master"
      :label="config.label"
      @close="close"
      @saved="handleMutation"
    />
    <ProductMasterDeleteModal
      v-if="modal === 'delete'"
      :item="selected"
      :master="master"
      :label="config.label"
      @close="close"
      @deleted="handleMutation"
    />
  </div>
</template>
