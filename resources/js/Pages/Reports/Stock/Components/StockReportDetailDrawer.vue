<script setup>
import { CalendarDays, ClipboardList, Package, X } from 'lucide-vue-next'
import { computed } from 'vue'
import Badge from '@/Components/UI/Badge.vue'
import { formatQty } from '@/Utils/helpers'

const props = defineProps({
  product: { type: Object, default: () => ({}) },
  ledger: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
})
const emit = defineEmits(['close'])
const movementLabel = (value) =>
  ({
    'Stok Masuk': 'Masuk',
    'Stok Keluar': 'Keluar',
    'Penyesuaian Opname': 'Adjustment',
    'Transfer Masuk': 'Transfer',
    'Transfer Keluar': 'Transfer',
  })[value] ??
  value ??
  '-'
const movementVariant = (value) =>
  value === 'Stok Keluar' ? 'error' : value === 'Penyesuaian Opname' ? 'warning' : 'success'
const entries = computed(() => props.ledger ?? [])
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-3xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      aria-label="Riwayat ledger stok"
    >
      <header
        class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-200 bg-white px-5 py-5 dark:border-[#29476b] dark:bg-[#102542] sm:px-6"
      >
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
            Riwayat Ledger Stok
          </p>
          <h2 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">
            {{ product.name ?? '-' }}
          </h2>
          <p class="mt-1 font-mono text-sm text-slate-500">{{ product.sku ?? '-' }}</p>
        </div>
        <button
          type="button"
          class="rounded-lg p-2 text-slate-400 hover:bg-slate-100"
          aria-label="Tutup riwayat"
          @click="emit('close')"
        >
          <X :size="20" />
        </button>
      </header>
      <div v-if="loading" class="p-6 text-center text-sm text-slate-500">
        Memuat riwayat ledger...
      </div>
      <div v-else-if="error" class="m-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
        {{ error }}
      </div>
      <div v-else class="space-y-5 p-5 sm:p-6">
        <div class="grid gap-3 sm:grid-cols-3">
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <Package :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Kategori</p>
            <p class="mt-1 text-sm font-semibold dark:text-white">{{ product.category ?? '-' }}</p>
          </div>
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <ClipboardList :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Brand</p>
            <p class="mt-1 text-sm font-semibold dark:text-white">{{ product.brand ?? '-' }}</p>
          </div>
          <div class="rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
            <CalendarDays :size="17" class="text-emerald-600" />
            <p class="mt-3 text-xs text-slate-400">Jumlah Ledger</p>
            <p class="mt-1 text-sm font-semibold dark:text-white">{{ entries.length }}</p>
          </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-[#29476b]">
          <table class="min-w-full text-sm">
            <thead
              class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 dark:bg-[#0a1b33]"
            >
              <tr>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Tipe</th>
                <th class="px-4 py-3 text-right">Qty</th>
                <th class="px-4 py-3">Referensi</th>
                <th class="px-4 py-3">Lokasi</th>
                <th class="px-4 py-3">User</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-[#29476b]">
              <tr v-for="entry in entries" :key="entry.id">
                <td class="whitespace-nowrap px-4 py-3 dark:text-slate-200">
                  {{
                    entry.created_at ? new Date(entry.created_at).toLocaleDateString('id-ID') : '-'
                  }}
                </td>
                <td class="px-4 py-3">
                  <Badge :variant="movementVariant(entry.movement_type)">{{
                    movementLabel(entry.movement_type)
                  }}</Badge>
                </td>
                <td class="px-4 py-3 text-right font-semibold tabular-nums dark:text-white">
                  {{ formatQty(entry.qty) }}
                </td>
                <td class="px-4 py-3 font-mono text-xs dark:text-slate-200">
                  {{ entry.reference_number ?? '-' }}
                </td>
                <td class="px-4 py-3 dark:text-slate-200">{{ entry.location_name ?? '-' }}</td>
                <td class="px-4 py-3 dark:text-slate-200">{{ entry.user_name ?? '-' }}</td>
              </tr>
              <tr v-if="!entries.length">
                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                  Tidak ada riwayat ledger pada periode ini.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </aside>
  </Teleport>
</template>
