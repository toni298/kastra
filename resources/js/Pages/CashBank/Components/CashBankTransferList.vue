<script setup>
import { ref, watch } from 'vue'
import { Plus } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import { useAccessControl } from '@/Composables/useAccessControl'
import CashBankTransferModal from './CashBankTransferModal.vue'
import CashBankTransferTable from './CashBankTransferTable.vue'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  accounts: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['request'])
const { can } = useAccessControl()
const search = ref(props.filters.search ?? '')
const activeSort = ref(props.filters.sort ?? 'transfer_date')
const activeDirection = ref(props.filters.sort_direction ?? 'desc')
const modalOpen = ref(false)
const applyFilters = (perPage = props.items?.meta?.per_page ?? 10) =>
  emit('request', {
    url: route('cash-bank.transfers.index'),
    data: {
      search: search.value || undefined,
      per_page: perPage,
      sort: activeSort.value,
      sort_direction: activeDirection.value,
    },
  })
const navigate = ({ url }) => {
  emit('request', {
    url,
    data: {
      search: search.value || undefined,
      per_page: props.items?.meta?.per_page ?? 10,
      sort: activeSort.value,
      sort_direction: activeDirection.value,
    },
  })
}
const filter = ({ search: value }) => {
  search.value = value
  applyFilters()
}
const applySort = ({ key, direction }) => {
  activeSort.value = key
  activeDirection.value = direction
  applyFilters()
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
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">
          Transfer Antar Rekening
        </h2>
        <p class="mt-1 text-sm text-slate-500">
          Pantau perpindahan dana antar rekening perusahaan.
        </p>
      </div>
      <Button v-if="can('cash_bank.transfers.create')" size="sm" @click="modalOpen = true"
        ><Plus :size="16" class="mr-2" />Transfer Dana</Button
      >
    </div>
    <DataPanel
      ><CashBankTransferTable
        :items="items"
        :search="search"
        :sort-direction="activeDirection"
        :loading="loading"
        @filter="filter"
        @per-page-change="applyFilters"
        @sort="applySort"
        @navigate="navigate" /></DataPanel
    ><CashBankTransferModal v-model="modalOpen" :accounts="accounts" />
  </div>
</template>
