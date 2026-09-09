<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import {
  Building2,
  CircleDollarSign,
  Clock3,
  FileText,
  Mail,
  MapPin,
  Phone,
  ReceiptText,
  UserRound,
  X,
} from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'

const props = defineProps({
  supplier: { type: Object, required: true },
})
const emit = defineEmits(['close', 'load-invoices', 'filter-invoices'])
const loadingMore = ref(false)
const invoicePanel = ref(null)
const dateFrom = ref('')
const dateTo = ref('')
const invoices = computed(() => props.supplier.invoices?.data ?? [])
const nextCursor = computed(() => props.supplier.invoices?.next_cursor ?? null)
const loadMore = () => { const panel = invoicePanel.value; if (loadingMore.value || !nextCursor.value || !panel || panel.scrollTop + panel.clientHeight < panel.scrollHeight - 80) return; loadingMore.value = true; emit('load-invoices', nextCursor.value, () => { loadingMore.value = false }) }
const applyPeriod = () => emit('filter-invoices', { date_from: dateFrom.value || undefined, date_to: dateTo.value || undefined })

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value)
const closeOnEscape = (event) => {
  if (event.key === 'Escape') emit('close')
}

onMounted(() => {
  document.addEventListener('keydown', closeOnEscape)
  document.body.style.overflow = 'hidden'
})
onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 z-50 bg-slate-950/45 backdrop-blur-[2px]"
      @click="emit('close')"
    ></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 w-full max-w-xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
      aria-label="Detail supplier"
    >
      <header
        class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-100 bg-white/95 p-5 backdrop-blur dark:border-[#29476b] dark:bg-[#102542]/95 sm:p-6"
      >
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">
            Detail Supplier
          </p>
          <h2 class="mt-1 truncate text-xl font-semibold text-slate-950 dark:text-white">
            {{ supplier.name }}
          </h2>
        </div>
        <button
          class="ml-4 rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-[#163354] dark:hover:text-white"
          aria-label="Tutup detail"
          @click="emit('close')"
        >
          <X :size="20" />
        </button>
      </header>
      <div class="space-y-7 p-5 sm:p-6">
        <section>
          <div class="flex items-center gap-2 text-slate-950 dark:text-white">
            <Building2 :size="17" class="text-slate-400" />
            <h3 class="text-xs font-semibold uppercase tracking-wider">Informasi Supplier</h3>
          </div>
          <dl class="mt-3 grid gap-3 sm:grid-cols-2">
             <div
               v-for="item in [
                 ['Nama Supplier', supplier.name],
                 ['Kontak Supplier', supplier.contact_supplier || supplier.phone],
                 ['Email', supplier.email || '-'],
                 ['Alamat', supplier.address || '-'],
               ]"
              :key="item[0]"
              class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]"
            >
              <dt class="text-xs font-medium text-slate-400">{{ item[0] }}</dt>
              <dd class="mt-1.5 text-sm font-semibold text-slate-900 dark:text-white">
                {{ item[1] }}
              </dd>
            </div>
          </dl>
        </section>

        <section><h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Periode Pembelian</h3><DateRangePicker v-model:start="dateFrom" v-model:end="dateTo" class="mt-3 w-full" @update:end="applyPeriod" /></section>

        <section>
          <div class="flex items-center gap-2 text-slate-950 dark:text-white">
            <CircleDollarSign :size="17" class="text-slate-400" />
            <h3 class="text-xs font-semibold uppercase tracking-wider">Ringkasan Pembelian</h3>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div
              v-for="item in [
                [supplier.purchases, 'Total Pembelian', CircleDollarSign],
                [supplier.active, 'Invoice Aktif', FileText],
                [formatCurrency(supplier.activeInvoiceValue), 'Nilai Invoice Aktif', ReceiptText],
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

        <section>
          <div class="flex items-center gap-2 text-slate-950 dark:text-white">
            <FileText :size="17" class="text-slate-400" />
            <h3 class="text-xs font-semibold uppercase tracking-wider">Invoice Terakhir</h3>
          </div>
          <div ref="invoicePanel" class="mt-3 h-80 divide-y divide-slate-100 overflow-y-auto rounded-2xl border border-slate-200 dark:divide-[#29476b] dark:border-[#29476b]" @scroll="loadMore">
            <article
              v-for="invoice in invoices"
              :key="invoice.number"
              class="flex items-center justify-between gap-4 p-4"
            >
              <div>
                <p class="font-medium text-slate-900 dark:text-white">{{ invoice.number }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ invoice.date }}</p>
              </div>
              <div class="text-right">
                <p class="font-bold text-slate-900 dark:text-white">{{ invoice.total }}</p>
                <div class="mt-1.5">
                  <Badge :variant="invoice.variant">{{ invoice.status }}</Badge>
                </div>
              </div>
            </article>
            <p v-if="!invoices.length" class="p-5 text-center text-sm text-slate-500">Belum ada invoice pembelian.</p>
            <p v-else-if="nextCursor" class="p-3 text-center text-xs text-slate-400">{{ loadingMore ? 'Memuat invoice…' : 'Scroll untuk memuat invoice berikutnya.' }}</p>
          </div>
        </section>

      </div>
    </aside>
  </Teleport>
</template>
