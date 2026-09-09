<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import axios from 'axios'
import { FileBarChart, FileSpreadsheet, Loader2 } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'
import { useAccessControl } from '@/Composables/useAccessControl'
import { formatCurrency, exportToPdf, exportToExcel } from '@/Utils/reportExport'

const props = defineProps({ report: { type: Object, required: true } })
const emit = defineEmits(['close'])
const { can } = useAccessControl()

const reportKeyMap = {
  Neraca: 'neraca',
  'Laba Rugi': 'laba-rugi',
  'Arus Kas': 'arus-kas',
  'Buku Besar': 'buku-besar',
  'Neraca Saldo': 'neraca-saldo',
  'Perubahan Modal': 'perubahan-modal',
  Pajak: 'pajak',
}

const reportKey = computed(() => reportKeyMap[props.report.name] ?? '')
const isBukuBesar = computed(() => reportKey.value === 'buku-besar')

const toIso = (date) => {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

const now = new Date()
const firstOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
const dateRange = ref([toIso(firstOfMonth), toIso(now)])
const data = ref(null)
const loading = ref(false)
const loadingMore = ref(false)
const error = ref('')

const periodLabel = computed(() => {
  if (!data.value) return ''
  return `${data.value.period.from} - ${data.value.period.to}`
})

const hasMore = computed(() => Boolean(data.value?.pagination?.has_more))
const allRows = computed(() => data.value?.rows ?? [])

const fetchData = async () => {
  if (!dateRange.value || !dateRange.value[0] || !dateRange.value[1]) return

  loading.value = true
  error.value = ''
  data.value = null

  try {
    const [dateFrom, dateTo] = dateRange.value

    const response = await axios.get(
      route('accounting.reports.data', { report: reportKey.value }),
      { params: { date_from: dateFrom, date_to: dateTo } }
    )
    data.value = response.data
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat data laporan.'
  } finally {
    loading.value = false
  }
}

const loadMore = async () => {
  if (!isBukuBesar.value || !hasMore.value || loadingMore.value) return

  loadingMore.value = true

  try {
    const [dateFrom, dateTo] = dateRange.value
    const cursor = data.value?.pagination?.next_cursor

    const response = await axios.get(
      route('accounting.reports.data', { report: reportKey.value }),
      { params: { date_from: dateFrom, date_to: dateTo, cursor } }
    )

    const newData = response.data
    data.value = {
      ...newData,
      rows: [...data.value.rows, ...newData.rows],
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat data selanjutnya.'
  } finally {
    loadingMore.value = false
  }
}

watch(dateRange, fetchData, { deep: true, immediate: true })

const scrollContainer = ref(null)
const sentinel = ref(null)
let observer = null

const setupObserver = () => {
  if (observer) observer.disconnect()
  if (!sentinel.value || !('IntersectionObserver' in window)) return

  // eslint-disable-next-line no-undef
  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0]?.isIntersecting && hasMore.value && !loadingMore.value) {
        loadMore()
      }
    },
    {
      root: scrollContainer.value,
      rootMargin: '100px',
      threshold: 0,
    }
  )
  observer.observe(sentinel.value)
}

const teardownObserver = () => {
  if (observer) {
    observer.disconnect()
    observer = null
  }
}

watch(
  () => data.value?.pagination?.has_more,
  async (hasMoreVal) => {
    await nextTick()
    if (hasMoreVal) {
      setupObserver()
    } else {
      teardownObserver()
    }
  }
)

onBeforeUnmount(teardownObserver)

const handleExportPdf = () => {
  if (data.value) exportToPdf(data.value)
}
const handleExportExcel = () => {
  if (data.value) exportToExcel(data.value)
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="`Preview ${report.name}`"
    :description="`Pilih rentang tanggal untuk melihat data laporan.`"
    size="full"
    @update:model-value="emit('close')"
  >
    <div class="flex max-h-[85vh] flex-col">
      <div class="mb-5 shrink-0">
        <DateRangePicker v-model="dateRange" label="Rentang Tanggal" required />
      </div>

      <div
        v-if="error"
        class="shrink-0 rounded-xl bg-red-50 p-4 text-sm text-red-600 dark:bg-red-400/10 dark:text-red-300"
      >
        {{ error }}
      </div>

      <div v-if="loading" class="flex flex-col items-center justify-center py-16">
        <Loader2 :size="32" class="animate-spin text-emerald-600" />
        <p class="mt-3 text-sm text-slate-500">Memuat data laporan...</p>
      </div>

      <template v-if="data && !loading">
        <div
          class="mb-4 flex shrink-0 items-center gap-3 rounded-xl bg-emerald-50 p-4 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
        >
          <FileBarChart :size="22" />
          <div>
            <p class="font-medium">{{ data.title }}</p>
            <p class="text-xs opacity-80">Periode {{ periodLabel }}</p>
          </div>
        </div>

        <!-- Sections (Neraca, Laba Rugi, Arus Kas, etc) -->
        <div v-if="data.sections" class="space-y-5 overflow-y-auto pr-1">
          <div
            v-for="section in data.sections"
            :key="section.label"
            class="overflow-hidden rounded-xl border border-slate-200 dark:border-[#29476b]"
          >
            <div
              class="border-b border-slate-100 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-700 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-slate-200"
            >
              {{ section.label }}
            </div>
            <div
              v-for="(row, i) in section.rows"
              :key="i"
              class="flex justify-between border-b border-slate-100 px-5 py-3 last:border-0 dark:border-[#29476b]"
            >
              <span class="text-sm font-medium dark:text-white">{{ row[0] }}</span>
              <strong class="text-sm text-emerald-600">{{ formatCurrency(row[1]) }}</strong>
            </div>
            <div class="flex justify-between bg-slate-50 px-5 py-3 dark:bg-[#0a1b33]">
              <span class="text-sm font-bold dark:text-white">Total {{ section.label }}</span>
              <strong class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{
                formatCurrency(section.total)
              }}</strong>
            </div>
          </div>
        </div>

        <!-- Table-based (Buku Besar, Neraca Saldo) -->
        <div
          v-if="data.columns"
          class="min-h-0 flex-1 overflow-hidden rounded-xl border border-slate-200 dark:border-[#29476b]"
        >
          <div ref="scrollContainer" class="max-h-[70vh] overflow-auto">
            <table class="w-full border-collapse text-sm">
              <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-[#0a1b33]">
                <tr>
                  <th
                    v-for="col in data.columns"
                    :key="col"
                    class="border-b border-slate-200 px-5 py-3 text-left font-semibold text-slate-700 dark:border-[#29476b] dark:text-slate-200"
                  >
                    {{ col }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(row, i) in allRows"
                  :key="i"
                  class="border-t border-slate-100 dark:border-[#29476b]"
                >
                  <td
                    v-for="(cell, j) in row"
                    :key="j"
                    class="px-5 py-3 dark:text-white"
                    :class="
                      typeof cell === 'number' ? 'text-right font-medium text-emerald-600' : ''
                    "
                  >
                    {{ typeof cell === 'number' ? formatCurrency(cell) : cell }}
                  </td>
                </tr>
                <tr v-if="allRows.length === 0">
                  <td :colspan="data.columns.length" class="px-5 py-8 text-center text-slate-400">
                    Tidak ada data untuk periode ini.
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Infinite scroll sentinel + loading indicator -->
            <div v-if="isBukuBesar" ref="sentinel" class="flex items-center justify-center py-3">
              <div v-if="loadingMore" class="flex items-center gap-2 text-sm text-slate-500">
                <Loader2 :size="16" class="animate-spin text-emerald-600" />
                Memuat data...
              </div>
              <span v-else-if="!hasMore && allRows.length > 0" class="text-xs text-slate-400">
                {{ allRows.length }} transaksi
              </span>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div
          v-if="data.summary"
          class="mt-5 shrink-0 overflow-hidden rounded-xl border border-emerald-200 dark:border-emerald-400/20"
        >
          <div
            class="bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200"
          >
            Ringkasan
          </div>
          <div
            v-for="(row, i) in data.summary"
            :key="i"
            class="flex justify-between border-b border-emerald-100 px-5 py-3 last:border-0 dark:border-emerald-400/20"
          >
            <span class="text-sm font-medium dark:text-white">{{ row[0] }}</span>
            <strong class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{
              formatCurrency(row[1])
            }}</strong>
          </div>
        </div>
      </template>
    </div>

    <template #footer>
      <Button
        v-if="can('laporan.export')"
        variant="secondary"
        :disabled="!data || loading"
        @click="handleExportPdf"
      >
        <FileBarChart :size="16" class="mr-2" />PDF
      </Button>
      <Button
        v-if="can('laporan.export')"
        variant="secondary"
        :disabled="!data || loading"
        @click="handleExportExcel"
      >
        <FileSpreadsheet :size="16" class="mr-2" />Excel
      </Button>
      <Button @click="emit('close')">Tutup</Button>
    </template>
  </Modal>
</template>
