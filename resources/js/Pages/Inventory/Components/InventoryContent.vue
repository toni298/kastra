<script setup>
import { Boxes } from '@lucide/vue'
import { computed, defineAsyncComponent, markRaw } from 'vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import { useInventoryTabs } from '../Composables/useInventoryTabs'

const props = defineProps({
  activeTab: { type: String, default: 'overview' },
  enabledFeatures: { type: Array, default: () => [] },
  overviewSummary: { type: Object, default: null },
  overviewBranchStocks: { type: Object, default: null },
  overviewFilters: { type: Object, default: () => ({}) },
  overviewOptions: { type: Object, default: null },
  stockItems: { type: Object, default: null },
  stockFilters: { type: Object, default: () => ({}) },
  stockOptions: { type: Object, default: null },
  movementItems: { type: Object, default: null },
  movementFilters: { type: Object, default: () => ({}) },
  movementSummary: { type: Object, default: () => ({}) },
  movementOptions: { type: Object, default: () => ({ branches: [], warehouses: [], users: [] }) },
  hasMultipleWarehouses: { type: Boolean, default: false },
  transferItems: { type: Object, default: null },
  transferFilters: { type: Object, default: () => ({}) },
  transferOptions: { type: Object, default: null },
  opnameItems: { type: Object, default: null },
  opnameFilters: { type: Object, default: () => ({}) },
  opnameOptions: { type: Object, default: null },
  inventoryRoutes: { type: Object, default: () => ({}) },
})

const {
  currentTab,
  modal,
  selectedItem,
  transferDetailLoading,
  transferDetailError,
  opnameDetailLoading,
  opnameDetailError,
  tabs,
  activeComponentProps,
  selectTab,
  requestActiveTab,
  openAction,
  closeModal,
} = useInventoryTabs(props)

const hasInventoryAccess = computed(() => tabs.value.length > 0)
const pageTitle = computed(() =>
  hasInventoryAccess.value ? 'Manajemen Persediaan' : 'Persediaan Toko'
)
const pageDescription = computed(() =>
  hasInventoryAccess.value
    ? 'Kelola stok produk, transfer, stock opname, dan penyesuaian dalam satu tempat.'
    : 'Pantau ketersediaan dan kelola produk yang ditampilkan di toko online.'
)

const components = {
  overview: markRaw(defineAsyncComponent(() => import('./InventoryOverview.vue'))),
  stock: markRaw(defineAsyncComponent(() => import('./InventoryStockTable.vue'))),
  movements: markRaw(defineAsyncComponent(() => import('./InventoryMovementTable.vue'))),
  transfer: markRaw(defineAsyncComponent(() => import('./InventoryTransferPanel.vue'))),
  opname: markRaw(defineAsyncComponent(() => import('./InventoryStockOpnameList.vue'))),
}
const InventoryActionModal = defineAsyncComponent(() => import('./InventoryActionModal.vue'))
const InventoryDeleteDialog = defineAsyncComponent(() => import('./InventoryDeleteDialog.vue'))
const InventoryDetailModal = defineAsyncComponent(() => import('./InventoryDetailModal.vue'))
const InventoryProductFormModal = defineAsyncComponent(
  () => import('./InventoryProductFormModal.vue')
)
const InventoryStockDeleteDialog = defineAsyncComponent(
  () => import('./InventoryStockDeleteDialog.vue')
)
const InventoryStockOpnameFormModal = defineAsyncComponent(
  () => import('./InventoryStockOpnameFormModal.vue')
)
const InventoryStockOpnameDrawer = defineAsyncComponent(
  () => import('./InventoryStockOpnameDrawer.vue')
)
const InventoryTransferDrawer = defineAsyncComponent(() => import('./InventoryTransferDrawer.vue'))
const InventoryTransferReceiveModal = defineAsyncComponent(
  () => import('./InventoryTransferReceiveModal.vue')
)
const activeComponent = computed(() => components[currentTab.value])
const saved = () => closeModal()
</script>

<template>
  <div class="space-y-5">
    <PageHeader :title="pageTitle" :description="pageDescription">
      <template #icon><Boxes :size="22" /></template>
    </PageHeader>

    <nav
      v-if="hasInventoryAccess"
      class="border-b border-slate-200 dark:border-[#29476b]"
      aria-label="Data persediaan"
    >
      <div class="flex flex-wrap gap-x-7 gap-y-3 px-1" role="tablist">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          role="tab"
          :aria-selected="currentTab === tab.id"
          :class="[
            'relative shrink-0 pb-3 text-sm font-medium transition',
            currentTab === tab.id
              ? 'text-emerald-700 dark:text-emerald-300'
              : 'text-slate-500 hover:text-slate-800 dark:text-slate-400',
          ]"
          @click="selectTab(tab)"
        >
          {{ tab.label }}
          <span
            v-if="currentTab === tab.id"
            class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-emerald-600"
          ></span>
        </button>
      </div>
    </nav>

    <KeepAlive :max="5">
      <component
        :is="activeComponent"
        :key="currentTab"
        v-bind="activeComponentProps"
        @request="requestActiveTab"
        @create="openAction({ action: 'create', type: 'stock' })"
        @edit="openAction({ action: 'edit', type: 'stock', item: $event })"
        @delete="openAction({ action: 'delete', type: 'stock', item: $event })"
        @action="openAction"
      />
    </KeepAlive>

    <InventoryActionModal
      v-if="modal === 'transfer'"
      :type="modal"
      :item="selectedItem"
      :warehouses="activeComponentProps.options?.warehouses ?? []"
      :branches="activeComponentProps.options?.branches ?? []"
      @close="closeModal"
      @saved="saved"
    />
    <InventoryStockOpnameFormModal
      v-if="modal === 'opname'"
      :warehouses="activeComponentProps.options?.warehouses ?? []"
      :branches="activeComponentProps.options?.branches ?? []"
      @close="closeModal"
      @saved="saved"
    />
    <InventoryStockOpnameFormModal
      v-if="modal === 'opname-edit'"
      :item="selectedItem"
      :warehouses="activeComponentProps.options?.warehouses ?? []"
      :branches="activeComponentProps.options?.branches ?? []"
      @close="closeModal"
      @saved="saved"
    />
    <InventoryStockOpnameDrawer
      v-if="modal === 'opname-detail'"
      :item="selectedItem"
      :loading="opnameDetailLoading"
      :error="opnameDetailError"
      @close="closeModal"
    />
    <InventoryProductFormModal
      v-if="modal === 'product'"
      :stock="selectedItem"
      :warehouses="activeComponentProps.options?.warehouses ?? []"
      @close="closeModal"
    />
    <InventoryDetailModal
      v-if="modal === 'detail'"
      :type="currentTab"
      :item="selectedItem"
      @close="closeModal"
    />
    <InventoryTransferDrawer
      v-if="modal === 'transfer-drawer'"
      :item="selectedItem"
      :loading="transferDetailLoading"
      :error="transferDetailError"
      :can-receive="activeComponentProps.canReceive ?? false"
      @close="closeModal"
      @receive="openAction({ action: 'receive', type: 'transfer', item: selectedItem })"
    />
    <InventoryTransferReceiveModal
      v-if="modal === 'transfer-receive'"
      :item="selectedItem"
      @close="closeModal"
      @saved="saved"
    />
    <InventoryStockDeleteDialog
      v-if="modal === 'delete' && currentTab === 'stock'"
      :stock="selectedItem"
      @close="closeModal"
    />
    <InventoryDeleteDialog
      v-else-if="modal === 'delete'"
      :item="selectedItem"
      @close="closeModal"
      @confirm="closeModal"
    />
  </div>
</template>
