<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { Check, FileText, Plus, Send } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import Modal from '@/Components/UI/Modal.vue'
import { formatCurrency } from '@/Utils/helpers'
import { useToastify } from '@/Composables/useToastify'
import PayrollProcessModal from '../Components/PayrollProcessModal.vue'
import PayrollDetailDrawer from '../Components/PayrollDetailDrawer.vue'

const toast = useToastify()
const rows = ref({ data: [], links: {}, meta: {} })
const loading = ref(true)
const processOpen = ref(false)
const selected = ref(null)
const period = ref('')
const status = ref('')
const posting = ref(null)
const formatIndonesianDate = (value) => {
  if (!value) return '-'
  const [year, month, day] = String(value).slice(0, 10).split('-')
  return year && month && day ? `${day}/${month}/${year}` : value
}
const fetchPayrolls = async () => {
  loading.value = true
  try {
    const response = await axios.get(route('api.hr.payrolls.index'), { params: { period: period.value || undefined, status: status.value || undefined, per_page: 10 } })
    rows.value = response.data?.data ?? { data: [], links: {}, meta: {} }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Riwayat payroll gagal dimuat.')
  } finally { loading.value = false }
}
const statusLabel = (value) => ({ draft: 'Draft', approved: 'Approved', posted: 'Posted' })[value] ?? value
const cards = computed(() => {
  const items = rows.value.data || []
  const total = items.reduce((sum, item) => sum + Number(item.total_amount || 0), 0)
  const count = items.reduce((sum, item) => sum + Number(item.employee_count || item.items_count || 0), 0)
  return [
    { label: 'Total Pengeluaran Gaji', value: formatCurrency(total), icon: Send },
    { label: 'Jumlah Karyawan Terproses', value: count, icon: Check },
    { label: 'Rata-rata Gaji Karyawan', value: formatCurrency(count ? total / count : 0), icon: FileText },
    { label: 'Status Posting Jurnal', value: items.some((item) => item.status === 'posted') ? 'Posted' : 'Draft', icon: Send },
  ]
})
const postPayroll = async () => {
  if (!posting.value) return
  try {
    await axios.post(route('api.hr.payrolls.post', { payroll: posting.value.id }))
    toast.success('Payroll berhasil diposting ke jurnal.')
    posting.value = null
    fetchPayrolls()
  } catch (error) { toast.error(error.response?.data?.message || 'Payroll gagal diposting.') }
}
onMounted(fetchPayrolls)
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Penggajian</h2><p class="mt-1 text-sm text-slate-500">Kelola batch payroll dan slip gaji karyawan.</p></div>
      <Button @click="processOpen = true"><Plus :size="16" class="mr-2" />Proses Gaji Periode Baru</Button>
    </div>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><article v-for="card in cards" :key="card.label" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"><div class="flex items-center justify-between"><div><p class="text-xs text-slate-500">{{ card.label }}</p><p class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">{{ card.value }}</p></div><component :is="card.icon" :size="21" class="text-emerald-600" /></div></article></section>
    <DataPanel><div class="relative"><div v-if="loading" class="pointer-events-none absolute inset-0 z-10 flex min-h-40 items-center justify-center bg-white/70 dark:bg-[#102542]/70"><Spinner label="Memuat riwayat payroll..." /></div><DataTable :items="rows.data || []" :pagination="rows" :loading="loading" searchable search-placeholder="Cari periode payroll..." @filter="fetchPayrolls"><template #filters><select v-model="period" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" @change="fetchPayrolls"><option value="">Semua periode</option></select><select v-model="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" @change="fetchPayrolls"><option value="">Semua status</option><option value="draft">Draft</option><option value="approved">Approved</option><option value="posted">Posted</option></select></template><template #thead><tr><th>Periode</th><th>Cut-off</th><th>Karyawan</th><th>Total Payroll</th><th>Status</th><th class="text-right">Aksi</th></tr></template><tr v-for="row in rows.data" :key="row.id"><td class="px-5 py-4 font-semibold dark:text-white">{{ formatIndonesianDate(row.period) }}</td><td class="px-5 py-4 dark:text-slate-200">{{ formatIndonesianDate(row.cutoff_date) }}</td><td class="px-5 py-4 dark:text-slate-200">{{ row.employee_count || row.items_count || 0 }}</td><td class="px-5 py-4 font-semibold dark:text-white">{{ formatCurrency(row.total_amount) }}</td><td class="px-5 py-4"><Badge :variant="row.status === 'posted' ? 'success' : row.status === 'draft' ? 'warning' : 'info'">{{ statusLabel(row.status) }}</Badge></td><td class="px-5 py-4"><div class="flex justify-end gap-2"><button type="button" class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-700" @click="selected = row"><FileText :size="15" />Rincian</button><button v-if="row.status !== 'posted'" type="button" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-700" @click="posting = row"><Send :size="15" />Posting</button></div></td></tr></DataTable></div></DataPanel>
    <PayrollProcessModal v-if="processOpen" @close="processOpen = false" @saved="fetchPayrolls" />
    <PayrollDetailDrawer v-if="selected" :payroll="selected" @close="selected = null" />
    <Modal v-if="posting" :model-value="true" title="Posting Jurnal Payroll" description="Batch payroll akan ditandai posted dan jurnal payroll akan dicatat." size="sm" @update:model-value="posting = null"><p class="text-sm text-slate-600 dark:text-slate-300">Posting periode <strong>{{ formatIndonesianDate(posting.period) }}</strong> sebesar <strong>{{ formatCurrency(posting.total_amount) }}</strong>?</p><template #footer><Button variant="secondary" @click="posting = null">Batal</Button><Button @click="postPayroll"><Send :size="16" class="mr-2" />Posting Jurnal</Button></template></Modal>
  </div>
</template>
