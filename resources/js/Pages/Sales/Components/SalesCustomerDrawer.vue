<script setup>
import { computed, ref } from 'vue'
import { Banknote, FileText, Mail, MapPin, Phone, ShoppingCart, X } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'

const props = defineProps({
  customer: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
})
const emit = defineEmits(['close', 'request-documents'])
const status = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const documents = computed(() => props.customer.documents?.data ?? [])
const pagination = computed(() => props.customer.documents ?? { data: [] })
const summary = computed(
  () => props.customer.document_summary ?? { total: 0, receivable: 0, active: 0 }
)
const money = (value) => `Rp ${Number(value ?? 0).toLocaleString('id-ID')}`
const statusLabel = (value) =>
  ({
    draft: 'Draft',
    completed: 'Selesai',
    return: 'Retur Sebagian',
    partial_return: 'Retur Sebagian',
    full_return: 'Retur Penuh',
    cancelled: 'Dibatalkan',
  })[value] ?? value
const statusVariant = (value) =>
  value === 'completed'
    ? 'success'
    : value === 'cancelled'
      ? 'error'
      : value === 'full_return'
        ? 'error'
        : value === 'return' || value === 'partial_return'
          ? 'warning'
          : 'neutral'
const requestDocuments = (url = route('sales.customers.show', props.customer.id), extra = {}) =>
  emit('request-documents', {
    url,
    data: {
      status: status.value || undefined,
      date_from: dateFrom.value || undefined,
      date_to: dateTo.value || undefined,
      per_page: pagination.value.per_page ?? 10,
      ...extra,
    },
  })
const applyFilters = () => requestDocuments()
</script>
<template>
  <Teleport to="body"
    ><div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-2xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
    >
      <header
        class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-100 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542] sm:p-6"
      >
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
            Detail Pelanggan
          </p>
          <h2 class="mt-1 text-xl font-semibold text-slate-950 dark:text-white">
            {{ customer.name }}
          </h2>
          <div class="mt-2">
            <Badge :variant="customer.variant">{{ customer.status }}</Badge>
          </div>
        </div>
        <button
          class="rounded-lg p-2 text-slate-400 hover:bg-slate-100"
          aria-label="Tutup detail"
          @click="emit('close')"
        >
          <X :size="20" />
        </button>
      </header>
      <div v-if="loading" class="p-6 text-center text-sm text-slate-500">
        Memuat detail pelanggan…
      </div>
      <div v-else-if="error" class="m-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">
        {{ error }}
      </div>
      <div v-else class="space-y-7 p-5 sm:p-6">
        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kontak</h3>
          <div class="mt-3 space-y-3 rounded-2xl border border-slate-200 p-4 dark:border-[#29476b]">
            <p class="flex items-center gap-3 text-sm font-medium dark:text-white">
              <Phone :size="16" class="text-emerald-600" />{{ customer.phone || '-' }}
            </p>
            <p class="flex items-center gap-3 text-sm font-medium dark:text-white">
              <Mail :size="16" class="text-emerald-600" />{{ customer.email || '-' }}
            </p>
            <p class="flex items-start gap-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
              <MapPin :size="16" class="mt-1 shrink-0 text-emerald-600" />{{
                customer.address || '-'
              }}
            </p>
          </div>
        </section>
        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            Dokumen Penjualan
          </h3>
          <div class="mt-3 grid gap-4 sm:grid-cols-2">
            <label class="text-sm font-medium text-slate-700 dark:text-slate-200"
              >Status<select
                v-model="status"
                class="mt-1.5 w-full rounded-xl border-slate-200 py-2.5 text-sm dark:border-[#29476b] dark:bg-[#0a1b33]"
                @change="applyFilters"
              >
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="completed">Selesai</option>
                <option value="return">Retur</option>
                <option value="cancelled">Dibatalkan</option>
              </select></label
            ><DateRangePicker
              v-model:start="dateFrom"
              v-model:end="dateTo"
              label="Periode Dokumen"
              class="col-span-full"
              @update:end="applyFilters"
            />
          </div>
        </section>
        <section>
          <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            Ringkasan Penjualan
          </h3>
          <div class="mt-3 grid gap-3 sm:grid-cols-3">
            <div
              v-for="item in [
                [money(summary.total), 'Total Penjualan', ShoppingCart],
                [money(summary.receivable), 'Piutang', Banknote],
                [summary.active, 'Invoice Aktif', FileText],
              ]"
              :key="item[1]"
              class="rounded-2xl bg-slate-50 p-4 dark:bg-[#0a1b33]"
            >
              <component :is="item[2]" :size="17" class="text-emerald-600" />
              <p class="mt-3 font-semibold text-slate-950 dark:text-white">{{ item[0] }}</p>
              <p class="mt-1 text-xs text-slate-500">{{ item[1] }}</p>
            </div>
          </div>
        </section>
        <DataTable
          :items="documents"
          :pagination="pagination"
          empty-icon="file-text"
          empty-title="Tidak ada dokumen"
          empty-message="Tidak ada dokumen sesuai filter."
          @navigate="(url) => requestDocuments(url)"
          @per-page-change="
            (perPage) =>
              requestDocuments(route('sales.customers.show', customer.id), { per_page: perPage })
          "
          ><template #thead
            ><tr>
              <th>Nomor</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th class="text-right">Total</th>
            </tr></template
          >
          <tr v-for="document in documents" :key="document.number" class="text-sm">
            <td class="px-5 py-4 font-medium dark:text-white">{{ document.number }}</td>
            <td class="px-5 py-4">{{ document.date }}</td>
            <td class="px-5 py-4">
              <Badge :variant="statusVariant(document.status)">{{
                statusLabel(document.status)
              }}</Badge>
            </td>
            <td class="px-5 py-4 text-right font-semibold dark:text-white">{{ document.total }}</td>
          </tr></DataTable
        >
      </div>
    </aside></Teleport
  >
</template>
