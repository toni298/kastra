<script setup>
import { computed, ref } from 'vue'
import { ArrowLeft, Bot, ChevronRight } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import AccountingDrawer from './AccountingDrawer.vue'
defineProps({ type: { type: String, required: true } })
const emit = defineEmits(['back']),
  selected = ref(null),
  prefix = ref('INV'),
  separator = ref('-'),
  digits = ref(5),
  resetYearly = ref(true)
const titles = {
  tax: 'Pajak',
  fiscal: 'Tahun Buku',
  numbers: 'Nomor Dokumen',
  currency: 'Mata Uang',
  automation: 'Otomatisasi Pembukuan',
}
const months = [
  'Jan ✅',
  'Feb ✅',
  'Mar ✅',
  'Apr ✅',
  'Mei ✅',
  'Jun ✅',
  'Jul 🔵',
  'Ags ⚪',
  'Sep ⚪',
  'Okt ⚪',
  'Nov ⚪',
  'Des ⚪',
]
const currencies = [
  { name: 'Rupiah', code: 'IDR', symbol: 'Rp', decimal: 0, status: 'Aktif' },
  { name: 'US Dollar', code: 'USD', symbol: '$', decimal: 2, status: 'Aktif' },
]
const taxSections = [
  { title: 'Pajak Penjualan', status: 'Aktif', detail: 'PPN 11% · Akun PPN Keluaran' },
  { title: 'Pajak Pembelian', status: 'Aktif', detail: 'PPN 11% · Akun PPN Masukan' },
  {
    title: 'Coretax',
    status: 'Perlu Ditinjau',
    detail: 'NPWP tersedia · Sinkronisasi belum aktif',
  },
]
const taxRates = [
  ['PPN', '11%', 'Penjualan & Pembelian'],
  ['PPh 23', '2%', 'Jasa'],
  ['PPh Final', '0,5%', 'Usaha tertentu'],
]
const fiscalSummary = [
  ['Tahun Aktif', '2026'],
  ['Periode Aktif', 'Juli'],
  ['Status', 'Berjalan'],
]
const documentNumbers = [
  ['Penjualan', 'INV-2026-00012'],
  ['Pembelian', 'PUR-2026-00008'],
  ['Jurnal', 'JRN-2026-00055'],
  ['Retur', 'RET-2026-00003'],
]
const currencySummary = [
  ['Mata Uang Utama', 'IDR'],
  ['Simbol', 'Rp'],
  ['Mata Uang Aktif', '2'],
]
const currencyDetails = computed(() =>
  selected.value
    ? [
        ['Nama', selected.value.name],
        ['Kode', selected.value.code],
        ['Symbol', selected.value.symbol],
        ['Decimal', selected.value.decimal],
        ['Exchange Rate', '1,00'],
        ['Status', selected.value.status],
      ]
    : []
)
const preview = computed(
  () =>
    `${prefix.value}${separator.value}2026${separator.value}${String(12).padStart(digits.value, '0')}`
)
</script>
<template>
  <div>
    <button
      class="mb-4 flex items-center gap-2 text-sm font-medium text-emerald-600"
      @click="emit('back')"
    >
      <ArrowLeft :size="17" />Kembali ke Pengaturan
    </button>
    <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ titles[type] }}</h2>
    <p class="mt-1 text-sm text-slate-500">
      Konfigurasi dipandu dengan bahasa bisnis yang sederhana.
    </p>
    <template v-if="type === 'tax'"
      ><div class="mt-5 grid gap-4 lg:grid-cols-2">
        <article
          v-for="item in taxSections"
          :key="item.title"
          class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
        >
          <div class="flex justify-between">
            <h3 class="font-semibold dark:text-white">{{ item.title }}</h3>
            <Badge :variant="item.status === 'Aktif' ? 'success' : 'warning'">{{
              item.status
            }}</Badge>
          </div>
          <p class="mt-3 text-sm text-slate-500">{{ item.detail }}</p>
        </article>
      </div>
      <section
        class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
      >
        <h3 class="font-semibold dark:text-white">Tarif Pajak</h3>
        <div class="mt-4 space-y-3">
          <div
            v-for="tax in taxRates"
            :key="tax[0]"
            class="grid grid-cols-3 border-b border-slate-100 pb-3 text-sm dark:border-[#29476b]"
          >
            <strong>{{ tax[0] }}</strong
            ><span>{{ tax[1] }}</span
            ><span>{{ tax[2] }}</span>
          </div>
        </div>
      </section></template
    ><template v-else-if="type === 'fiscal'"
      ><section class="mt-5 grid gap-4 sm:grid-cols-3">
        <article
          v-for="item in fiscalSummary"
          :key="item[0]"
          class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
        >
          <p class="text-xs text-slate-400">{{ item[0] }}</p>
          <p class="mt-2 text-xl font-semibold dark:text-white">{{ item[1] }}</p>
        </article>
      </section>
      <div class="mt-5 grid grid-cols-3 gap-3 sm:grid-cols-6 xl:grid-cols-12">
        <button
          v-for="month in months"
          :key="month"
          class="rounded-xl border border-slate-200 p-3 text-sm font-medium dark:border-[#29476b]"
        >
          {{ month }}
        </button>
      </div>
      <div class="mt-5 flex gap-2">
        <Button>Tutup Bulan</Button><Button variant="secondary">Tutup Tahun</Button
        ><Button variant="ghost">Buka Periode</Button>
      </div></template
    ><template v-else-if="type === 'numbers'"
      ><div class="mt-5 grid gap-4 sm:grid-cols-2">
        <button
          v-for="item in documentNumbers"
          :key="item[0]"
          class="rounded-2xl border border-slate-200 bg-white p-5 text-left dark:border-[#29476b] dark:bg-[#102542]"
          @click="selected = item"
        >
          <div class="flex justify-between">
            <h3 class="font-semibold dark:text-white">{{ item[0] }}</h3>
            <ChevronRight :size="18" />
          </div>
          <p class="mt-3 text-emerald-600">{{ item[1] }}</p>
        </button>
      </div>
      <AccountingDrawer
        :open="Boolean(selected)"
        title="Editor Nomor Dokumen"
        description="Preview berubah otomatis."
        @close="selected = null"
        ><div class="space-y-4">
          <label
            >Prefix<input
              v-model="prefix"
              class="mt-1 w-full rounded-xl dark:bg-[#0a1b33]" /></label
          ><label
            >Separator<input
              v-model="separator"
              class="mt-1 w-full rounded-xl dark:bg-[#0a1b33]" /></label
          ><label
            >Digit<input
              v-model="digits"
              type="number"
              class="mt-1 w-full rounded-xl dark:bg-[#0a1b33]" /></label
          ><label class="flex gap-2"
            ><input v-model="resetYearly" type="checkbox" />Reset Tahunan</label
          >
          <div class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-400/10">
            <p class="text-xs text-slate-500">Preview Nomor</p>
            <strong class="mt-1 block text-emerald-700">{{ preview }}</strong>
          </div>
        </div></AccountingDrawer
      ></template
    ><template v-else-if="type === 'currency'"
      ><section class="mt-5 grid gap-4 sm:grid-cols-3">
        <article
          v-for="item in currencySummary"
          :key="item[0]"
          class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-[#29476b] dark:bg-[#102542]"
        >
          <p class="text-xs text-slate-400">{{ item[0] }}</p>
          <p class="mt-2 text-xl font-semibold dark:text-white">{{ item[1] }}</p>
        </article>
      </section>
      <div
        class="mt-5 rounded-2xl border border-slate-200 bg-white dark:border-[#29476b] dark:bg-[#102542]"
      >
        <button
          v-for="item in currencies"
          :key="item.code"
          class="grid w-full grid-cols-5 border-b border-slate-100 p-4 text-left text-sm last:border-0 dark:border-[#29476b]"
          @click="selected = item"
        >
          <strong>{{ item.name }}</strong
          ><span>{{ item.code }}</span
          ><span>{{ item.symbol }}</span
          ><span>{{ item.decimal }} desimal</span><Badge variant="success">{{ item.status }}</Badge>
        </button>
      </div>
      <AccountingDrawer
        :open="Boolean(selected)"
        title="Detail Mata Uang"
        description="Informasi kurs dan status."
        @close="selected = null"
        ><dl v-if="selected" class="space-y-3">
          <div
            v-for="pair in currencyDetails"
            :key="pair[0]"
            class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]"
          >
            <dt class="text-xs text-slate-400">{{ pair[0] }}</dt>
            <dd class="font-medium">{{ pair[1] }}</dd>
          </div>
        </dl></AccountingDrawer
      ></template
    >
    <section
      v-else
      class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-400/20 dark:bg-emerald-400/10"
    >
      <Bot :size="30" class="text-emerald-600" />
      <h3 class="mt-4 text-lg font-semibold text-emerald-900 dark:text-emerald-200">
        Otomatisasi Aktif
      </h3>
      <p class="mt-2 text-sm leading-6 text-emerald-800 dark:text-emerald-300">
        Kastra otomatis membuat jurnal dari Penjualan, Pembelian, Persediaan, Kas & Bank, dan
        penyesuaian bisnis.
      </p>
      <Badge class="mt-4" variant="success">Semua modul terhubung</Badge>
    </section>
  </div>
</template>
