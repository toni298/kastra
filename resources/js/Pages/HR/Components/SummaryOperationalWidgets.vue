<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Check, Clock3, X } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import Skeleton from '@/Components/UI/Skeleton.vue'
import { formatCurrency } from '@/Utils/helpers'
import { useToastify } from '@/Composables/useToastify'

const props = defineProps({
  summary: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})
const toast = useToastify()
const actionId = ref(null)
const attendance = computed(() => props.summary.today_attendance ?? {})
const totalAttendance = computed(() =>
  ['present', 'late', 'izin', 'absent'].reduce((total, key) => total + Number(attendance.value[key] ?? 0), 0),
)
const attendanceRows = computed(() => [
  { label: 'Hadir', value: attendance.value.present ?? 0, class: 'bg-emerald-500' },
  { label: 'Terlambat', value: attendance.value.late ?? 0, class: 'bg-amber-500' },
  { label: 'Izin', value: attendance.value.izin ?? 0, class: 'bg-blue-500' },
  { label: 'Alpa', value: attendance.value.absent ?? 0, class: 'bg-red-500' },
])
const approvalAction = (item, status) => {
  actionId.value = `${item.id}:${status}`
  router.put(route('hr.overtimes.status', { overtime: item.id }), { status }, {
    preserveScroll: true,
    onSuccess: () => toast.success(`Lembur berhasil ${status === 'approved' ? 'disetujui' : 'ditolak'}.`),
    onError: () => toast.error('Status lembur gagal diperbarui.'),
    onFinish: () => { actionId.value = null },
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-2">
      <DataPanel>
        <div class="space-y-5 p-6">
          <div class="flex items-center justify-between">
          <div><h3 class="font-semibold text-slate-900 dark:text-white">Kehadiran & Shift Hari Ini</h3><p class="mt-1 text-xs text-slate-500">Ringkasan status kehadiran dan kasir yang sedang bertugas.</p></div>
          <Clock3 :size="20" class="text-emerald-600" />
          </div>
        <div v-if="loading" class="space-y-3"><Skeleton v-for="i in 4" :key="i" height="4" /><Skeleton height="12" /></div>
        <template v-else>
          <div class="space-y-2">
            <div v-for="row in attendanceRows" :key="row.label" class="flex items-center gap-4 py-2.5 text-sm">
              <span class="w-20 text-slate-500">{{ row.label }}</span><div class="h-2 flex-1 rounded-full bg-slate-100 dark:bg-[#0a1b33]"><div class="h-2 rounded-full" :class="row.class" :style="{ width: `${totalAttendance ? (row.value / totalAttendance) * 100 : 0}%` }"></div></div><strong class="w-8 text-right text-slate-900 dark:text-white">{{ row.value }}</strong>
            </div>
          </div>
          <div class="border-t border-slate-100 pt-5 dark:border-[#29476b]"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kasir On-duty</p><div v-if="attendance.on_duty?.length" class="mt-4 grid gap-3 sm:grid-cols-2"><div v-for="item in attendance.on_duty" :key="item.id" class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]"><p class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.employee }}</p><p class="mt-1 text-xs text-slate-500">{{ item.shift || 'Tanpa shift' }} · {{ item.clock_in || '-' }}</p></div></div><p v-else class="py-10 text-sm text-slate-500">Belum ada kasir on-duty.</p></div>
        </template>
        </div>
      </DataPanel>
      <DataPanel>
        <div class="space-y-5 p-6"><div class="flex items-center justify-between"><div><h3 class="font-semibold text-slate-900 dark:text-white">Pusat Persetujuan</h3><p class="mt-1 text-xs text-slate-500">Pengajuan lembur yang menunggu tindakan.</p></div><Badge variant="warning">{{ summary.pending_approvals?.length ?? 0 }} Pending</Badge></div>
        <div v-if="loading" class="space-y-3"><Skeleton v-for="i in 3" :key="i" height="12" /></div>
        <div v-else-if="summary.pending_approvals?.length" class="space-y-3"><div v-for="item in summary.pending_approvals" :key="item.id" class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 p-4 dark:border-[#29476b]"><div><p class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.employee }}</p><p class="mt-1 text-xs text-slate-500">{{ item.date }} · {{ item.hours }} jam · {{ formatCurrency(item.amount) }}</p></div><div class="flex gap-1"><Button size="sm" :disabled="actionId !== null" :loading="actionId === `${item.id}:approved`" @click="approvalAction(item, 'approved')"><Check :size="14" /></Button><Button size="sm" variant="danger" :disabled="actionId !== null" :loading="actionId === `${item.id}:rejected`" @click="approvalAction(item, 'rejected')"><X :size="14" /></Button></div></div></div><p v-else class="py-10 text-sm text-slate-500">Tidak ada pengajuan pending.</p></div>
      </DataPanel>
    </div>
    <div class="grid gap-6 xl:grid-cols-2">
      <DataPanel><div class="space-y-5 p-6"><div><h3 class="font-semibold text-slate-900 dark:text-white">Proyeksi Kompensasi Bulan Ini</h3><p class="mt-1 text-xs text-slate-500">Breakdown payroll dari data payroll yang sudah diproses.</p></div><div v-if="loading" class="space-y-3"><Skeleton v-for="i in 3" :key="i" height="8" /></div><div v-else class="grid gap-4 sm:grid-cols-3"><div v-for="item in [{ label: 'Gaji Pokok', value: summary.payroll_projection?.basic }, { label: 'Komisi', value: summary.payroll_projection?.commission }, { label: 'Lembur', value: summary.payroll_projection?.overtime } ]" :key="item.label" class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]"><p class="text-xs text-slate-500">{{ item.label }}</p><p class="mt-2 font-semibold text-slate-900 dark:text-white">{{ formatCurrency(item.value) }}</p></div></div></div></DataPanel>
      <DataPanel><div class="space-y-5 p-6"><div><h3 class="font-semibold text-slate-900 dark:text-white">Top Performer & Komisi Kasir</h3><p class="mt-1 text-xs text-slate-500">Lima kasir dengan komisi payroll terbesar bulan ini.</p></div><div v-if="loading" class="space-y-3"><Skeleton v-for="i in 5" :key="i" height="6" /></div><div v-else-if="summary.top_performers?.length" class="divide-y divide-slate-100 dark:divide-[#29476b]"><div v-for="(item, index) in summary.top_performers" :key="`${item.employee}-${index}`" class="flex items-center justify-between border-b border-slate-100 px-2 py-3.5 last:border-b-0 dark:border-[#29476b]"><div><span class="mr-2 text-xs font-bold text-emerald-600">#{{ index + 1 }}</span><span class="text-sm font-semibold text-slate-900 dark:text-white">{{ item.employee }}</span></div><strong class="text-sm text-emerald-700">{{ formatCurrency(item.commission) }}</strong></div></div><p v-else class="py-10 text-sm text-slate-500">Belum ada data komisi bulan ini.</p></div></DataPanel>
    </div>
    <DataPanel v-if="!loading && summary.contract_expiring_soon?.length"><div class="space-y-5 p-6"><h3 class="font-semibold text-slate-900 dark:text-white">Kontrak Berakhir Dalam 30 Hari</h3><div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3"><div v-for="item in summary.contract_expiring_soon" :key="item.id" class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900"><strong>{{ item.name }}</strong><p class="mt-1 text-xs">{{ item.role }} · {{ item.contract_ends_at }}</p></div></div></div></DataPanel>
  </div>
</template>
