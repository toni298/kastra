<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { Eye, Plus, Search } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import { useAccessControl } from '@/Composables/useAccessControl'
import CashBankAccountEmptyState from './CashBankAccountEmptyState.vue'
import { reconciliations, transfers } from '../Data/cashBankData'
const props = defineProps({
    type: { type: String, required: true },
    accounts: { type: Object, default: () => ({ data: [], next_cursor: null }) },
  }),
  emit = defineEmits(['action']),
  { can } = useAccessControl(),
  search = ref(''),
  status = ref('Semua Status')
const accountItems = ref([])
const nextCursor = ref(null)
const loadingAccounts = ref(false)
const sentinel = ref(null)
let observer
const setAccounts = (value) => {
  accountItems.value = value?.data ?? []
  nextCursor.value = value?.next_cursor ?? null
}
watch(() => props.accounts, setAccounts, { immediate: true, deep: true })
const loadMoreAccounts = async () => {
  if (!nextCursor.value || loadingAccounts.value) return
  loadingAccounts.value = true
  try {
    const response = await window.axios.get(route('cash-bank.accounts.list'), {
      params: { cursor: nextCursor.value },
    })
    accountItems.value.push(...response.data.data)
    nextCursor.value = response.data.next_cursor
  } finally {
    loadingAccounts.value = false
  }
}
const usageLabel = (setting) =>
  setting.can_receive_money && setting.can_send_money
    ? 'Kas Masuk & Keluar'
    : setting.can_receive_money
      ? 'Kas Masuk'
      : 'Kas Keluar'
onMounted(() => {
  observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) loadMoreAccounts()
    },
    { rootMargin: '160px' }
  )
  if (sentinel.value) observer.observe(sentinel.value)
})
onBeforeUnmount(() => observer?.disconnect())
const configs = {
  transfers: {
    title: 'Transfer Antar Rekening',
    description: 'Pantau perpindahan dana perusahaan.',
    rows: transfers,
    columns: [
      'Nomor Transfer',
      'Rekening Asal',
      'Rekening Tujuan',
      'Nominal',
      'Tanggal',
      'Status',
      'Aksi',
    ],
  },
  reconciliation: {
    title: 'Rekonsiliasi',
    description: 'Cocokkan saldo sistem dengan saldo rekening.',
    rows: reconciliations,
    columns: ['Rekening', 'Periode', 'Saldo Sistem', 'Saldo Bank', 'Selisih', 'Status', 'Aksi'],
  },
}
const config = computed(() => configs[props.type])
const rows = computed(() =>
  config.value.rows.filter((row) =>
    Object.values(row).some((v) => String(v).toLowerCase().includes(search.value.toLowerCase()))
  )
)
</script>
<template>
  <section v-if="type === 'accounts'">
    <div class="mb-4 flex justify-end">
      <Button v-if="can('cash_bank.accounts.create')" @click="emit('action', { type: 'account' })"
        ><Plus :size="16" class="mr-2" />Tambah Rekening</Button
      >
    </div>
    <CashBankAccountEmptyState v-if="!accountItems.length" />
    <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <article
        v-for="item in accountItems"
        :key="item.id"
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
      >
        <div class="flex justify-between">
          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
              {{ item.type }}
            </p>
            <h2 class="mt-1 font-semibold text-slate-950 dark:text-white">{{ item.name }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ item.number }} · {{ item.currency }}</p>
          </div>
          <Badge :variant="item.variant">{{ item.status }}</Badge>
        </div>
        <p class="mt-6 text-2xl font-semibold text-emerald-700 dark:text-emerald-300">
          {{ item.balance }}
        </p>
        <div
          v-if="item.settings?.length"
          class="mt-4 space-y-2 border-t border-slate-100 pt-4 dark:border-[#29476b]"
        >
          <div
            v-for="(setting, index) in item.settings"
            :key="`${item.id}-${index}`"
            class="rounded-xl bg-slate-50 px-3 py-2.5 dark:bg-[#0a1b33]"
          >
            <div class="flex items-center justify-between gap-3">
              <span class="truncate text-xs font-medium text-slate-600 dark:text-slate-300">{{
                setting.scope
              }}</span
              ><Badge :variant="setting.is_active ? 'info' : 'warning'">{{
                usageLabel(setting)
              }}</Badge>
            </div>
            <div
              v-if="setting.is_default_receive || setting.is_default_payment"
              class="mt-2 flex flex-wrap gap-1.5"
            >
              <Badge v-if="setting.is_default_receive" variant="success">Default Kas Masuk</Badge
              ><Badge v-if="setting.is_default_payment" variant="success">Default Kas Keluar</Badge>
            </div>
          </div>
        </div>
        <div
          v-else
          class="mt-4 rounded-xl border border-dashed border-amber-300 bg-amber-50 px-3 py-2.5 text-xs font-medium text-amber-800 dark:border-amber-400/40 dark:bg-amber-400/10 dark:text-amber-200"
        >
          Belum Dikonfigurasi
        </div>
        <div
          v-if="item.account_type !== 'cash'"
          class="mt-5 flex gap-2 border-t border-slate-100 pt-4 dark:border-[#29476b]"
        >
          <Button
            variant="secondary"
            size="sm"
            @click="emit('action', { type: 'accountDetail', item })"
            >Detail</Button
          ><Button
            variant="secondary"
            size="sm"
            v-if="can('cash_bank.accounts.edit')"
            @click="emit('action', { type: 'accountEdit', item })"
            >Edit</Button
          ><Button
            v-if="item.status === 'Aktif' && can('cash_bank.accounts.deactivate')"
            variant="danger"
            size="sm"
            @click="emit('action', { type: 'deactivate', item })"
            >Nonaktifkan</Button
          >
        </div>
      </article>
    </div>
    <div
      v-if="accountItems.length && nextCursor"
      ref="sentinel"
      class="py-6 text-center text-sm text-slate-500"
    >
      {{ loadingAccounts ? 'Memuat rekening…' : 'Memuat rekening berikutnya…' }}
    </div>
  </section>
  <article
    v-else
    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
  >
    <header
      class="flex flex-col justify-between gap-4 border-b border-slate-100 p-5 dark:border-[#29476b] sm:flex-row sm:items-center"
    >
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ config.title }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ config.description }}</p>
      </div>
      <div class="flex gap-2">
        <Button
          v-if="type === 'transfers' && can('cash_bank.transfers.create')"
          size="sm"
          @click="emit('action', { type: 'transfer' })"
          ><Plus :size="16" class="mr-2" />Transfer Dana</Button
        >
      </div>
    </header>
    <div
      class="flex flex-wrap gap-3 border-b border-slate-100 bg-slate-50/50 p-4 dark:border-[#29476b] dark:bg-[#0a1b33]/60"
    >
      <label class="relative min-w-[220px] flex-1"
        ><Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" /><input
          v-model="search"
          class="w-full rounded-xl border-slate-200 py-2 pl-9 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          placeholder="Cari data..." /></label
      ><select
        v-model="status"
        class="rounded-xl border-slate-200 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
      >
        <option>Semua Status</option>
        <option>Berhasil</option>
        <option>Pending</option></select
      ><select
        class="rounded-xl border-slate-200 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
      >
        <option>Semua Rekening</option>
        <option>Bank BCA Operasional</option>
        <option>Kas Utama</option></select
      ><input
        class="rounded-xl border-slate-200 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
        placeholder="Tanggal"
      />
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-left">
        <thead
          class="bg-slate-50 text-[12px] font-semibold uppercase tracking-wide text-slate-400 dark:bg-[#0a1b33]"
        >
          <tr>
            <th v-for="col in config.columns" :key="col" class="whitespace-nowrap px-5 py-3.5">
              {{ col }}
            </th>
          </tr>
        </thead>
        <tbody
          class="divide-y divide-slate-100 text-sm text-slate-700 dark:divide-[#29476b] dark:text-slate-200"
        >
          <tr
            v-for="row in rows"
            :key="row.reference || row.number || row.account"
            class="hover:bg-slate-50/70 dark:hover:bg-[#163354]/60"
          >
            <template v-if="type === 'transfers'"
              ><td class="px-5 py-4 font-medium">{{ row.number }}</td>
              <td class="px-5 py-4">{{ row.source }}</td>
              <td class="px-5 py-4">{{ row.destination }}</td>
              <td class="px-5 py-4 font-bold">{{ row.amount }}</td>
              <td class="px-5 py-4">{{ row.date }}</td>
              <td class="px-5 py-4">
                <Badge :variant="row.variant">{{ row.status }}</Badge>
              </td></template
            ><template v-else
              ><td class="px-5 py-4 font-medium">{{ row.account }}</td>
              <td class="px-5 py-4">{{ row.period }}</td>
              <td class="px-5 py-4">{{ row.system }}</td>
              <td class="px-5 py-4">{{ row.bank }}</td>
              <td class="px-5 py-4 font-bold">{{ row.difference }}</td>
              <td class="px-5 py-4">
                <Badge :variant="row.variant">{{ row.status }}</Badge>
              </td></template
            >
            <td class="px-5 py-4">
              <button
                class="rounded-lg p-2 hover:bg-slate-100 dark:hover:bg-[#163354]"
                @click="
                  emit('action', {
                    type: type === 'transfers' ? 'transferDetail' : 'reconciliationDetail',
                    item: row,
                  })
                "
              >
                <Eye :size="17" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </article>
</template>
