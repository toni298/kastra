<script setup>
import { computed } from 'vue'
import { CircleDollarSign, FileText, UserCheck, Users } from 'lucide-vue-next'

const props = defineProps({
  suppliers: { type: Array, default: () => [] },
})

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value)
const cards = computed(() => {
  const activeSuppliers = props.suppliers.filter((supplier) => supplier.status === 'Aktif')
  const purchaseValue = props.suppliers.reduce(
    (total, supplier) => total + supplier.purchaseValue,
    0
  )
  const activeInvoices = props.suppliers.reduce(
    (total, supplier) => total + supplier.activeInvoiceCount,
    0
  )

  return [
    {
      label: 'Total Supplier',
      value: String(props.suppliers.length),
      caption: 'Seluruh supplier terdaftar',
      icon: Users,
      tone: 'bg-slate-100 text-slate-700 dark:bg-slate-400/10 dark:text-slate-300',
    },
    {
      label: 'Supplier Aktif',
      value: String(activeSuppliers.length),
      caption: `${props.suppliers.length - activeSuppliers.length} supplier nonaktif`,
      icon: UserCheck,
      tone: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300',
    },
    {
      label: 'Total Nilai Pembelian',
      value: formatCurrency(purchaseValue),
      caption: 'Akumulasi seluruh pembelian',
      icon: CircleDollarSign,
      tone: 'bg-blue-50 text-blue-700 dark:bg-blue-400/10 dark:text-blue-300',
    },
    {
      label: 'Invoice Aktif',
      value: String(activeInvoices),
      caption: 'Masih perlu diselesaikan',
      icon: FileText,
      tone: 'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300',
    },
  ]
})
</script>

<template>
  <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Ringkasan supplier">
    <article
      v-for="card in cards"
      :key="card.label"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-[#29476b] dark:bg-[#102542] sm:p-5"
    >
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
          <p
            class="mt-2 truncate text-lg font-semibold tracking-tight text-slate-950 dark:text-white sm:text-xl"
          >
            {{ card.value }}
          </p>
        </div>
        <span :class="['grid size-9 shrink-0 place-items-center rounded-xl', card.tone]">
          <component :is="card.icon" :size="18" />
        </span>
      </div>
      <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ card.caption }}</p>
    </article>
  </section>
</template>
