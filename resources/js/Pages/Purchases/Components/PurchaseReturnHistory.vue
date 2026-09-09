<script setup>
import { computed, ref } from 'vue'
import { Eye, Undo2 } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import IconButton from '@/Components/UI/IconButton.vue'

const props = defineProps({
  items: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['select', 'request'])
const search = ref(props.filters.search ?? '')
const rows = computed(() => props.items?.data ?? [])
const pagination = computed(() => ({
  ...props.items,
  per_page: props.items?.meta?.per_page ?? 10,
}))
const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
const request = (data = {}) =>
  emit('request', {
    url: route('purchases.returns.index'),
    data: { search: search.value || undefined, per_page: pagination.value.per_page, ...data },
  })
const navigate = ({ cursor, direction }) => {
  if (!cursor) return

  const url = new URL(route('purchases.returns.index'), window.location.origin)
  url.searchParams.set('cursor', cursor)

  emit('request', { url: url.toString(), data: { search: search.value || undefined, per_page: pagination.value.per_page } })
}
const filter = ({ search: value }) => {
  search.value = value
  request({ search: value || undefined })
}
</script>

<template>
  <div class="space-y-5">
    <div>
      <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Riwayat Retur</h2>
      <p class="mt-1 text-sm text-slate-500">Kelola riwayat retur pembelian.</p>
    </div>
    <DataPanel>
      <DataTable
        :items="rows"
        :pagination="pagination"
        :loading="loading"
        sticky-toolbar
        searchable
        :search="search"
        search-placeholder="Cari nomor retur, referensi, atau supplier..."
        empty-icon="undo-2"
        empty-title="Belum ada retur"
        empty-message="Riwayat retur pembelian belum tersedia."
        @navigate="navigate"
        @filter="filter"
        @per-page-change="(perPage) => request({ per_page: perPage })"
      >
        <template #filters
          ><span class="text-sm text-slate-500">{{ rows.length }} retur</span></template
        >
        <template #thead
          ><tr>
            <th>Retur</th>
            <th>Referensi</th>
            <th>Supplier</th>
            <th>Status</th>
            <th class="text-right">Nilai</th>
            <th class="text-right">Aksi</th>
          </tr></template
        >
        <tr
          v-for="row in rows"
          :key="row.id"
          class="cursor-pointer text-sm"
          tabindex="0"
          @click="emit('select', row)"
          @keydown.enter="emit('select', row)"
        >
          <td class="px-5 py-4">
            <p class="font-mono font-semibold dark:text-white">
              <span class="mr-2 inline-flex text-amber-600"><Undo2 :size="15" /></span
              >{{ row.number }}
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.date || '-' }}</p>
          </td>
          <td class="px-5 py-4">
            <p class="font-mono font-medium dark:text-white">{{ row.reference || '-' }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ row.reason || '-' }}</p>
          </td>
          <td class="px-5 py-4">
            <p class="font-medium dark:text-white">{{ row.supplier }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              {{ row.details_count }} item
            </p>
          </td>
          <td class="px-5 py-4">
            <Badge :variant="row.statusVariant">{{ row.status }}</Badge>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              {{ row.resolution || '-' }}
            </p>
          </td>
          <td class="px-5 py-4 text-right font-semibold dark:text-white">
            {{ row.value || money(row.totalValue) }}
          </td>
          <td class="px-5 py-4" @click.stop>
            <div class="flex justify-end">
              <IconButton label="Detail retur" @click="emit('select', row)"
                ><Eye :size="16"
              /></IconButton>
            </div>
          </td>
        </tr>
      </DataTable>
    </DataPanel>
  </div>
</template>
