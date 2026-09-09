<script setup>
import { Plus } from '@lucide/vue'
import { computed } from 'vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import { useAuthorization } from '@/Composables/useAuthorization'
import { useProductPanelState } from '../Composables/useProductPanelState'
import ProductTable from './ProductTable.vue'

const props = defineProps({
  items: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
  canCreate: { type: Boolean, default: false },
  canEdit: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
  routeName: { type: String, default: 'products.index' },
})

const emit = defineEmits(['request', 'form', 'detail', 'delete', 'mutated'])

const { can } = useAuthorization()
const canCreate = computed(() => props.canCreate || can('products.create'))
const canEdit = computed(() => props.canEdit || can('products.edit'))
const canDelete = computed(() => props.canDelete || can('products.delete'))

const { search, sortKey, sortDirection, applyFilters, handleSearch, navigate, handleSort } =
  useProductPanelState({
    props,
    emit,
    tab: 'products',
    defaultSort: 'created_at',
    defaultSortDirection: 'desc',
    routeResolver: () => route(props.routeName),
  })
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Daftar Produk</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola produk yang tersedia untuk operasional.</p>
      </div>
      <Button v-if="canCreate" size="sm" @click="emit('form')">
        <Plus :size="17" class="mr-2" />Tambah Produk
      </Button>
    </div>

    <DataPanel>
      <ProductTable
        :items="items"
        :loading="loading"
        :search="search"
        :sort-key="sortKey"
        :sort-direction="sortDirection"
        :can-edit="canEdit"
        :can-delete="canDelete"
        @navigate="navigate"
        @filter="handleSearch"
        @per-page-change="applyFilters"
        @sort="handleSort"
        @detail="emit('detail', $event)"
        @edit="emit('form', $event)"
        @delete="emit('delete', $event)"
      />
    </DataPanel>
  </div>
</template>
