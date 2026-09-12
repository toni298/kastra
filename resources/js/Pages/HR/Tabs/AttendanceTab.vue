<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { Clock3, ExternalLink, Pencil, Plus, UsersRound } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import DataTable from '@/Components/UI/DataTable.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import Modal from '@/Components/UI/Modal.vue'
import Spinner from '@/Components/UI/Spinner.vue'
import { useToastify } from '@/Composables/useToastify'
import ShiftTable from '../Components/ShiftTable.vue'
import ShiftDetailDrawer from '../Components/ShiftDetailDrawer.vue'

defineProps({})
const toast = useToastify()
const activeSubTab = ref('attendance')
const attendanceLoading = ref(true)
const shiftsLoading = ref(false)
const attendanceState = ref({ data: [], links: {}, meta: {} })
const attendanceSummary = ref({})
const shifts = ref([])
const attendanceCache = new Map()
const shiftsLoaded = ref(false)
const correction = ref(null)
const shiftModal = ref(false)
const editingShift = ref(null)
const selectedShift = ref(null)
const shiftForm = ref({
  name: '',
  start_time: '08:00',
  end_time: '17:00',
  grace_period_minutes: 15,
  is_active: true,
})
const correctionForm = ref({
  clock_in: '',
  clock_out: '',
  status: 'present',
  late_minutes: 0,
  notes: '',
})
const date = ref(new Date().toISOString().slice(0, 10))
const status = ref('')
const search = ref('')
const cards = computed(() => [
  {
    label: 'Hadir Tepat Waktu',
    value: attendanceSummary.value.present ?? 0,
    icon: Clock3,
    tone: 'emerald',
  },
  { label: 'Terlambat', value: attendanceSummary.value.late ?? 0, icon: Clock3, tone: 'amber' },
  {
    label: 'Alpa / Belum Absen',
    value: attendanceSummary.value.absent ?? 0,
    icon: UsersRound,
    tone: 'red',
  },
  {
    label: 'Total Jam Lembur',
    value: `${attendanceSummary.value.overtime_hours ?? 0} jam`,
    icon: Clock3,
    tone: 'blue',
  },
])
const columns = [
  { key: 'employee', label: 'Karyawan' },
  { key: 'shift', label: 'Shift' },
  { key: 'clock_in', label: 'Jam Masuk' },
  { key: 'clock_out', label: 'Jam Keluar' },
  { key: 'status', label: 'Status' },
  { key: 'late_minutes', label: 'Keterlambatan' },
  { key: 'actions', label: 'Aksi' },
]
const attendanceKey = (cursor = '') =>
  JSON.stringify({ date: date.value, status: status.value, search: search.value, cursor })
const normalizeAttendancePayload = (payload) => {
  if (Array.isArray(payload)) return { data: payload, links: {}, meta: {} }
  if (Array.isArray(payload?.data)) return payload
  if (Array.isArray(payload?.data?.data)) return payload.data
  return { data: [], links: {}, meta: {} }
}
const fetchAttendances = async ({ cursor = '', force = false } = {}) => {
  const key = attendanceKey(cursor)
  if (!force && attendanceCache.has(key)) {
    const cached = attendanceCache.get(key)
    attendanceState.value = cached.data
    attendanceSummary.value = cached.summary
    return
  }
  attendanceLoading.value = true
  try {
    const response = await axios.get(route('api.hr.attendances.index'), {
      params: {
        date: date.value,
        status: status.value,
        search: search.value,
        cursor: cursor || undefined,
        per_page: 10,
      },
    })
    const cached = {
      data: normalizeAttendancePayload(response.data?.data),
      summary: response.data?.summary ?? {},
    }
    attendanceCache.set(key, cached)
    attendanceState.value = cached.data
    attendanceSummary.value = cached.summary
  } catch (error) {
    toast.error(error.response?.data?.message || 'Riwayat absensi gagal dimuat.')
  } finally {
    attendanceLoading.value = false
  }
}
const fetchShifts = async (force = false) => {
  if (shiftsLoaded.value && !force) return
  shiftsLoading.value = true
  try {
    const response = await axios.get(route('api.hr.shifts.index'))
    shifts.value = response.data?.data ?? []
    shiftsLoaded.value = true
  } catch (error) {
    toast.error(error.response?.data?.message || 'Data shift gagal dimuat.')
  } finally {
    shiftsLoading.value = false
  }
}
const switchSubTab = (tab) => {
  activeSubTab.value = tab
  if (tab === 'shifts') fetchShifts()
}
const applyFilters = (filters = {}) => {
  if (typeof filters === 'object' && filters.search !== undefined) search.value = filters.search
  fetchAttendances()
}
const navigate = ({ cursor }) => cursor && fetchAttendances({ cursor })
const openKiosk = () => window.open(route('kiosk.attendance'), '_blank', 'noopener')
const openCorrection = (item) => {
  correction.value = item
  correctionForm.value = {
    clock_in: item.clock_in ? `${item.attendance_date}T${item.clock_in}` : '',
    clock_out: item.clock_out ? `${item.attendance_date}T${item.clock_out}` : '',
    status: item.status,
    late_minutes: item.late_minutes || 0,
    notes: item.notes || '',
  }
}
const saveCorrection = async () => {
  try {
    await axios.put(
      route('api.hr.attendances.update', { attendance: correction.value.id }),
      correctionForm.value
    )
    toast.success('Koreksi absensi berhasil disimpan.')
    correction.value = null
    attendanceCache.clear()
    fetchAttendances({ force: true })
  } catch (error) {
    toast.error(error.response?.data?.message || 'Koreksi absensi gagal disimpan.')
  }
}
const openShift = (shift = null) => {
  editingShift.value = shift
  shiftForm.value = shift
    ? {
        name: shift.name,
        start_time: shift.start_time?.slice(0, 5),
        end_time: shift.end_time?.slice(0, 5),
        grace_period_minutes: shift.grace_period_minutes,
        is_active: shift.is_active,
      }
    : {
        name: '',
        start_time: '08:00',
        end_time: '17:00',
        grace_period_minutes: 15,
        is_active: true,
      }
  shiftModal.value = true
}
const saveShift = async () => {
  try {
    const url = editingShift.value
      ? route('api.hr.shifts.update', { shift: editingShift.value.id })
      : route('api.hr.shifts.store')
    await axios({ method: editingShift.value ? 'put' : 'post', url, data: shiftForm.value })
    toast.success('Shift berhasil disimpan.')
    shiftModal.value = false
    shiftsLoaded.value = false
    fetchShifts(true)
  } catch (error) {
    toast.error(error.response?.data?.message || 'Shift gagal disimpan.')
  }
}
const deleteShift = async (shift) => {
  if (!window.confirm(`Hapus shift ${shift.name}?`)) return
  try {
    await axios.delete(route('api.hr.shifts.destroy', { shift: shift.id }))
    toast.success('Shift berhasil dihapus.')
    shiftsLoaded.value = false
    fetchShifts(true)
  } catch (error) {
    toast.error(
      error.response?.data?.message || error.response?.data?.warning || 'Shift gagal dihapus.'
    )
  }
}
onMounted(() => fetchAttendances())
</script>

<template>
  <div class="space-y-5">
  <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Absensi & Shift</h2>
        <p class="mt-1 text-sm text-slate-500">Pantau kehadiran dan koreksi log operasional tim.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <Button v-if="activeSubTab === 'shifts'" @click="openShift()"><Plus :size="16" class="mr-2" />Tambah Shift</Button>
        <Button v-else @click="openKiosk"><ExternalLink :size="16" class="mr-2" />Buka Kiosk Absensi</Button>
      </div>
    </div>
    <nav
      class="flex gap-2 border-b border-slate-200 dark:border-[#29476b]"
      aria-label="Sub navigasi absensi"
    >
      <button
        v-for="tab in [
          { id: 'attendance', label: 'Riwayat Absensi' },
          { id: 'shifts', label: 'Data Shift' },
        ]"
        :key="tab.id"
        type="button"
        :class="[
          'rounded-t-xl px-4 py-2.5 text-sm font-semibold transition',
          activeSubTab === tab.id
            ? 'bg-emerald-600 text-white'
            : 'text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#102542]',
        ]"
        @click="switchSubTab(tab.id)"
      >
        {{ tab.label }}
      </button>
    </nav>
    <template v-if="activeSubTab === 'attendance'">
      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article
          v-for="card in cards"
          :key="card.label"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#102542]"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500">{{ card.label }}</p>
              <p class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">
                {{ card.value }}
              </p>
            </div>
            <component :is="card.icon" :class="`text-${card.tone}-600`" :size="22" />
          </div>
        </article>
      </section>
      <DataPanel>
        <div class="relative">
          <div v-if="attendanceLoading" class="pointer-events-none absolute inset-0 z-10 flex min-h-40 items-center justify-center bg-white/70 dark:bg-[#102542]/70">
            <Spinner label="Memuat riwayat absensi..." />
          </div>
          <DataTable
            :items="attendanceState.data || []"
            :pagination="attendanceState"
            :loading="attendanceLoading"
            searchable
            :search="search"
            search-placeholder="Cari nama atau NIK..."
            :columns="columns"
            @update:search="search = $event"
            @filter="applyFilters"
            @navigate="navigate"
          >
          <template #filters>
            <DatePicker v-model="date" aria-label="Tanggal absensi" @update:model-value="applyFilters" />
            <select v-model="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" @change="applyFilters">
              <option value="">Semua status</option><option value="present">Tepat waktu</option><option value="late">Terlambat</option><option value="absent">Alpa</option><option value="incomplete">Belum lengkap</option>
            </select>
          </template>
          <template #cell-employee="{ item }"><div class="font-medium text-slate-900 dark:text-white">{{ item.employee?.name }}</div><div class="text-xs text-slate-500">{{ item.employee?.nik }}</div></template>
          <template #cell-shift="{ item }">{{ item.shift?.name || '-' }}</template>
          <template #cell-clock_in="{ item }">{{ item.clock_in || '-' }}</template>
          <template #cell-clock_out="{ item }">{{ item.clock_out || '-' }}</template>
          <template #cell-status="{ item }"><span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', item.status === 'late' ? 'bg-amber-100 text-amber-700' : item.status === 'incomplete' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-700']">{{ item.status === 'late' ? 'Terlambat' : item.status === 'incomplete' ? 'Belum lengkap' : item.status === 'absent' ? 'Alpa' : 'Tepat waktu' }}</span></template>
          <template #cell-late_minutes="{ item }">{{ item.late_minutes || 0 }} menit</template>
          <template #cell-actions="{ item }"><button class="inline-flex items-center text-sm font-medium text-emerald-700" @click="openCorrection(item)"><Pencil :size="15" class="mr-1" />Koreksi</button></template>
          </DataTable>
        </div>
      </DataPanel>
    </template>
    <template v-else>
      <ShiftTable
        :items="shifts"
        :loading="shiftsLoading"
        @detail="selectedShift = $event"
        @edit="openShift"
        @delete="deleteShift"
      />
    </template>
    <ShiftDetailDrawer v-if="selectedShift" :shift="selectedShift" @close="selectedShift = null" />
    <Modal v-if="correction" :model-value="true" title="Koreksi Absensi" description="Perubahan akan tercatat sebagai adjustment admin." size="lg" @update:model-value="correction = null">
      <div class="grid gap-4 sm:grid-cols-2">
        <label class="text-sm">Jam masuk<input v-model="correctionForm.clock_in" type="datetime-local" class="mt-1 w-full rounded-xl border-slate-200" /></label>
        <label class="text-sm">Jam keluar<input v-model="correctionForm.clock_out" type="datetime-local" class="mt-1 w-full rounded-xl border-slate-200" /></label>
        <label class="text-sm">Status<select v-model="correctionForm.status" class="mt-1 w-full rounded-xl border-slate-200"><option value="present">Tepat waktu</option><option value="late">Terlambat</option><option value="incomplete">Belum lengkap</option><option value="absent">Alpa</option></select></label>
        <label class="text-sm">Keterlambatan (menit)<input v-model.number="correctionForm.late_minutes" type="number" min="0" class="mt-1 w-full rounded-xl border-slate-200" /></label>
      </div>
      <template #footer><Button variant="secondary" @click="correction = null">Batal</Button><Button @click="saveCorrection">Simpan Koreksi</Button></template>
    </Modal>
    <Modal v-if="shiftModal" :model-value="true" :title="editingShift ? 'Ubah Shift' : 'Tambah Shift'" size="lg" @update:model-value="shiftModal = false">
      <div class="grid gap-4 sm:grid-cols-2">
        <label class="text-sm sm:col-span-2">Nama shift<input v-model="shiftForm.name" class="mt-1 w-full rounded-xl border-slate-200" /></label>
        <label class="text-sm">Jam masuk<input v-model="shiftForm.start_time" type="time" class="mt-1 w-full rounded-xl border-slate-200" /></label>
        <label class="text-sm">Jam keluar<input v-model="shiftForm.end_time" type="time" class="mt-1 w-full rounded-xl border-slate-200" /></label>
        <label class="text-sm">Grace period (menit)<input v-model.number="shiftForm.grace_period_minutes" type="number" min="0" class="mt-1 w-full rounded-xl border-slate-200" /></label>
      </div>
      <template #footer><Button variant="secondary" @click="shiftModal = false">Batal</Button><Button @click="saveShift">Simpan Shift</Button></template>
    </Modal>
  </div>
</template>
