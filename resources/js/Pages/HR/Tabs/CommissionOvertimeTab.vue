<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { AlertTriangle, Check, CircleCheck, CircleX, Clock3, FileText, Plus, Receipt, XCircle } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import DateRangePicker from '@/Components/UI/DateRangePicker.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import Badge from '@/Components/UI/Badge.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import { formatCurrency, formatQty } from '@/Utils/helpers'
import { useToastify } from '@/Composables/useToastify'
import CommissionDetailDrawer from '../Components/CommissionDetailDrawer.vue'
import OvertimeFormModal from '../Components/OvertimeFormModal.vue'

defineProps({})
const toast = useToastify()
const activeTab = ref('commission')
const today = new Date()
const range = ref([
  new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10),
  new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().slice(0, 10),
])
const search = ref('')
const commissionType = ref('')
const overtimeStatus = ref('')
const commission = ref({ data: [], links: {}, meta: {} })
const overtime = ref({ data: [], links: {}, meta: {} })
const commissionSummary = ref({})
const overtimeSummary = ref({})
const commissionLoading = ref(true)
const overtimeLoading = ref(false)
const commissionCache = new Map()
const overtimeCache = new Map()
const selectedEmployee = ref(null)
const overtimeModal = ref(false)
const confirmation = ref(null)
const normalize = (payload) => Array.isArray(payload) ? { data: payload, links: {}, meta: {} } : (Array.isArray(payload?.data) ? payload : payload?.data ?? { data: [], links: {}, meta: {} })
const commissionParams = (cursor = '') => ({ from: range.value[0], to: range.value[1], search: search.value || undefined, commission_type: commissionType.value || undefined, cursor: cursor || undefined, per_page: 10 })
const overtimeParams = (cursor = '') => ({ from: range.value[0], to: range.value[1], status: overtimeStatus.value || undefined, cursor: cursor || undefined, per_page: 10 })
const fetchCommission = async (force = false, cursor = '') => {
  const key = JSON.stringify(commissionParams(cursor))
  if (!force && commissionCache.has(key)) { const cached = commissionCache.get(key); commission.value = cached.data; commissionSummary.value = cached.summary; return }
  commissionLoading.value = true
  try { const response = await axios.get(route('api.hr.commissions.index'), { params: commissionParams(cursor) }); const cached = { data: normalize(response.data?.data), summary: response.data?.summary ?? {} }; commissionCache.set(key, cached); commission.value = cached.data; commissionSummary.value = cached.summary }
  catch (error) { toast.error(error.response?.data?.message || 'Rekap komisi gagal dimuat.') }
  finally { commissionLoading.value = false }
}
const fetchOvertime = async (force = false, cursor = '') => {
  const key = JSON.stringify(overtimeParams(cursor))
  if (!force && overtimeCache.has(key)) { const cached = overtimeCache.get(key); overtime.value = cached.data; overtimeSummary.value = cached.summary; return }
  overtimeLoading.value = true
  try { const response = await axios.get(route('api.hr.overtimes.index'), { params: overtimeParams(cursor) }); const cached = { data: normalize(response.data?.data), summary: response.data?.summary ?? {} }; overtimeCache.set(key, cached); overtime.value = cached.data; overtimeSummary.value = cached.summary }
  catch (error) { toast.error(error.response?.data?.message || 'Log lembur gagal dimuat.') }
  finally { overtimeLoading.value = false }
}
const switchTab = (tab) => { activeTab.value = tab; if (tab === 'overtime') fetchOvertime() }
const applyCommission = () => fetchCommission()
const applyOvertime = () => fetchOvertime()
const formatIndonesianDate = (value) => {
  if (!value) return '-'
  const date = String(value).slice(0, 10).split('-')
  return date.length === 3 ? `${date[2]}/${date[1]}/${date[0]}` : value
}
const requestApproval = (row, status) => { confirmation.value = { row, status } }
const approve = async (row, status) => { try { await axios.put(route('api.hr.overtimes.status', { overtime: row.id }), { status }); toast.success('Status lembur berhasil diperbarui.'); overtimeCache.clear(); fetchOvertime(true) } catch (error) { toast.error(error.response?.data?.message || 'Status lembur gagal diperbarui.') } }
const confirmApproval = async () => { if (!confirmation.value) return; const action = confirmation.value; confirmation.value = null; await approve(action.row, action.status) }
const refreshOvertime = () => { overtimeCache.clear(); fetchOvertime(true) }
const commissionCards = computed(() => [{ label: 'Total Komisi Terkumpul', value: formatCurrency(commissionSummary.value.total_commission), icon: Receipt }, { label: 'Komisi Kasir Teratas', value: commissionSummary.value.top_employee || '-', icon: Check }, { label: 'Total Unit Terjual', value: formatQty(commissionSummary.value.total_qty), icon: FileText }, { label: 'Status Komisi Unpaid', value: formatCurrency(commissionSummary.value.unpaid), icon: Clock3 }])
const overtimeCards = computed(() => [{ label: 'Total Jam Lembur Bulan Ini', value: `${overtimeSummary.value.hours ?? 0} jam`, icon: Clock3 }, { label: 'Lembur Pending Approval', value: overtimeSummary.value.pending ?? 0, icon: Clock3 }, { label: 'Total Biaya Lembur', value: formatCurrency(overtimeSummary.value.cost), icon: Receipt }, { label: 'Lembur Disetujui', value: overtimeSummary.value.approved ?? 0, icon: Check }])
const commissionColumns = [{ key: 'employee', label: 'Karyawan' }, { key: 'scheme', label: 'Skema Komisi' }, { key: 'sales', label: 'Total Penjualan / Qty' }, { key: 'estimated_commission', label: 'Estimasi Komisi' }, { key: 'status', label: 'Status' }, { key: 'actions', label: 'Aksi' }]
const overtimeColumns = [{ key: 'date', label: 'Tanggal' }, { key: 'employee', label: 'Karyawan' }, { key: 'hours', label: 'Durasi' }, { key: 'rate', label: 'Tarif / Jam' }, { key: 'total_amount', label: 'Total Nominal' }, { key: 'reason', label: 'Keterangan' }, { key: 'status', label: 'Status' }, { key: 'actions', label: 'Aksi' }]
onMounted(() => fetchCommission())
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><h2 class="text-lg font-semibold text-slate-950 dark:text-white">Komisi & Lembur</h2><p class="mt-1 text-sm text-slate-500">Pantau kompensasi variabel dan persetujuan lembur tim.</p></div><Button v-if="activeTab === 'overtime'" @click="overtimeModal = true"><Plus :size="16" class="mr-2" />Catat Lembur Manual</Button></div>
    <nav class="flex gap-2 border-b border-slate-200 dark:border-[#29476b]" aria-label="Sub navigasi komisi dan lembur"><button v-for="tab in [{ id: 'commission', label: 'Rekap Komisi Penjualan' }, { id: 'overtime', label: 'Pengajuan & Log Lembur' }]" :key="tab.id" type="button" :class="['rounded-t-xl px-4 py-2.5 text-sm font-semibold', activeTab === tab.id ? 'bg-emerald-600 text-white' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-300']" @click="switchTab(tab.id)">{{ tab.label }}</button></nav>
    <template v-if="activeTab === 'commission'"><section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><article v-for="card in commissionCards" :key="card.label" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"><div class="flex items-center justify-between"><div><p class="text-xs text-slate-500">{{ card.label }}</p><p class="mt-2 truncate text-xl font-semibold text-slate-950 dark:text-white">{{ card.value }}</p></div><component :is="card.icon" :size="21" class="text-emerald-600" /></div></article></section><DataPanel><div class="relative"><div v-if="commissionLoading" class="pointer-events-none absolute inset-0 z-10 flex min-h-40 items-center justify-center bg-white/70 dark:bg-[#102542]/70"><Spinner label="Memuat rekap komisi..." /></div><DataTable :items="commission.data || []" :pagination="commission" :loading="commissionLoading" :columns="commissionColumns" searchable :search="search" search-placeholder="Cari nama karyawan..." @update:search="search = $event" @filter="applyCommission" @navigate="({ cursor }) => cursor && fetchCommission(false, cursor)"><template #filters><DateRangePicker v-model="range" @update:model-value="applyCommission" /><select v-model="commissionType" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" @change="applyCommission"><option value="">Semua skema</option><option value="percentage">% Omzet</option><option value="per_quantity">Nominal Qty</option></select></template><template #cell-employee="{ item }"><div class="font-semibold text-slate-900 dark:text-white">{{ item.name }}</div><div class="text-xs text-slate-500">{{ item.nik }}</div></template><template #cell-scheme="{ item }">{{ item.commission_type_label }} · {{ item.commission_value }}</template><template #cell-sales="{ item }">{{ formatCurrency(item.total_sales) }} / {{ formatQty(item.total_qty) }}</template><template #cell-estimated_commission="{ item }">{{ formatCurrency(item.estimated_commission) }}</template><template #cell-status><Badge variant="warning">Unpaid</Badge></template><template #cell-actions="{ item }"><button type="button" class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700" @click="selectedEmployee = item"><FileText :size="15" />Detail Audit</button></template></DataTable></div></DataPanel></template>
    <template v-else><section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><article v-for="card in overtimeCards" :key="card.label" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"><div class="flex items-center justify-between"><div><p class="text-xs text-slate-500">{{ card.label }}</p><p class="mt-2 text-xl font-semibold text-slate-950 dark:text-white">{{ card.value }}</p></div><component :is="card.icon" :size="21" class="text-emerald-600" /></div></article></section><DataPanel><div class="relative"><div v-if="overtimeLoading" class="pointer-events-none absolute inset-0 z-10 flex min-h-40 items-center justify-center bg-white/70 dark:bg-[#102542]/70"><Spinner label="Memuat log lembur..." /></div><DataTable :items="overtime.data || []" :pagination="overtime" :loading="overtimeLoading" :columns="overtimeColumns" @navigate="({ cursor }) => cursor && fetchOvertime(false, cursor)"><template #filters><select v-model="overtimeStatus" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" @change="applyOvertime"><option value="">Semua status</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select><DatePicker v-model="range[0]" aria-label="Tanggal mulai" @update:model-value="applyOvertime" /></template><template #cell-date="{ item }">{{ formatIndonesianDate(item.overtime_date) }}</template><template #cell-employee="{ item }"><div class="font-medium text-slate-900 dark:text-white">{{ item.employee?.name }}</div><div class="text-xs text-slate-500">{{ item.employee?.nik }}</div></template><template #cell-hours="{ item }">{{ item.hours }} jam</template><template #cell-rate="{ item }">{{ formatCurrency(item.hourly_rate) }}</template><template #cell-total_amount="{ item }">{{ formatCurrency(item.total_amount) }}</template><template #cell-reason="{ item }"><span class="line-clamp-2 max-w-xs">{{ item.reason }}</span></template><template #cell-status="{ item }"><Badge :variant="item.status === 'approved' ? 'success' : item.status === 'rejected' ? 'error' : 'warning'">{{ item.status }}</Badge></template><template #cell-actions="{ item }"><div v-if="item.status === 'pending'" class="flex items-center gap-1"><button type="button" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50" title="Setujui lembur" @click="requestApproval(item, 'approved')"><CircleCheck :size="16" />Setujui</button><button type="button" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50" title="Tolak lembur" @click="requestApproval(item, 'rejected')"><CircleX :size="16" />Tolak</button></div><span v-else class="text-xs text-slate-400">Selesai</span></template></DataTable></div></DataPanel></template>
    <Modal v-if="confirmation" :model-value="true" :title="confirmation.status === 'approved' ? 'Setujui Lembur' : 'Tolak Lembur'" :description="confirmation.status === 'approved' ? 'Pengajuan ini akan ditandai sebagai lembur disetujui.' : 'Pengajuan ini akan ditandai sebagai lembur ditolak.'" size="sm" @update:model-value="confirmation = null"><div class="flex items-start gap-3 rounded-xl bg-amber-50 p-4 text-sm text-amber-800"><AlertTriangle :size="20" class="mt-0.5 shrink-0" /><p>Pastikan data karyawan, tanggal, durasi, dan alasan sudah benar sebelum melanjutkan.</p></div><template #footer><Button variant="secondary" @click="confirmation = null">Batal</Button><Button :variant="confirmation.status === 'approved' ? 'primary' : 'danger'" @click="confirmApproval"><CircleCheck v-if="confirmation.status === 'approved'" :size="16" class="mr-2" /><CircleX v-else :size="16" class="mr-2" />{{ confirmation.status === 'approved' ? 'Ya, Setujui' : 'Ya, Tolak' }}</Button></template></Modal>
    <CommissionDetailDrawer v-if="selectedEmployee" :employee="selectedEmployee" :filters="{ from: range[0], to: range[1] }" @close="selectedEmployee = null" /><OvertimeFormModal v-if="overtimeModal" @close="overtimeModal = false" @saved="refreshOvertime" />
  </div>
</template>
