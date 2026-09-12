<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'
import axios from 'axios'
import { Search, Users, X } from 'lucide-vue-next'
import { useDebounceFn } from '@vueuse/core'
import Skeleton from '@/Components/UI/Skeleton.vue'
import Spinner from '@/Components/UI/Spinner.vue'

const props = defineProps({
  shift: { type: Object, default: null },
})
const emit = defineEmits(['close'])
const employees = ref([])
const search = ref('')
const loading = ref(false)
const error = ref('')
let requestId = 0

const loadEmployees = async () => {
  if (!props.shift?.id) return
  const currentRequest = ++requestId
  loading.value = true
  error.value = ''

  try {
    const response = await axios.get(route('api.hr.shifts.employees', { shift: props.shift.id }), {
      params: { search: search.value, per_page: 50 },
    })
    if (currentRequest === requestId)
      employees.value = response.data?.data?.data ?? response.data?.data ?? []
  } catch (requestError) {
    if (currentRequest === requestId)
      error.value = requestError.response?.data?.message || 'Data karyawan gagal dimuat.'
  } finally {
    if (currentRequest === requestId) loading.value = false
  }
}
const debouncedLoad = useDebounceFn(loadEmployees, 350)
watch(
  () => props.shift?.id,
  () => {
    employees.value = []
    search.value = ''
    loadEmployees()
  },
  { immediate: true }
)
watch(search, debouncedLoad)
onBeforeUnmount(() => {
  requestId += 1
})
const roleLabel = (role) =>
  ({ cashier: 'Kasir', supervisor: 'Supervisor', staff: 'Staff' })[role] ?? role
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 bg-slate-950/45" @click="emit('close')"></div>
    <aside
      class="fixed inset-y-0 right-0 z-50 flex w-full max-w-xl flex-col border-l border-slate-200 bg-white shadow-2xl dark:border-[#29476b] dark:bg-[#102542]"
    >
      <header
        class="flex items-start justify-between border-b border-slate-100 p-5 dark:border-[#29476b]"
      >
        <div class="flex items-center gap-3">
          <span
            class="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"
            ><Users :size="20"
          /></span>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
              Detail Shift
            </p>
            <h2 class="mt-0.5 text-lg font-semibold text-slate-950 dark:text-white">
              {{ shift?.name }}
            </h2>
            <p class="text-sm text-slate-500">
              {{ shift?.start_time?.slice(0, 5) }} - {{ shift?.end_time?.slice(0, 5) }}
            </p>
          </div>
        </div>
        <button
          type="button"
          class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-[#163354]"
          aria-label="Tutup detail shift"
          @click="emit('close')"
        >
          <X :size="20" />
        </button>
      </header>
      <div class="flex-1 overflow-y-auto p-5">
        <div class="relative mb-5">
          <Search
            :size="17"
            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
          /><input
            v-model="search"
            type="search"
            placeholder="Cari nama atau NIK..."
            class="w-full rounded-xl border-slate-200 py-2.5 pl-10 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          />
        </div>
        <div v-if="loading" class="space-y-3" role="status" aria-label="Memuat karyawan">
          <div class="flex justify-center rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
            <Spinner size="sm" label="Memuat karyawan..." />
          </div>
          <Skeleton v-for="index in 5" :key="index" height="16" />
        </div>
        <div v-else-if="error" class="rounded-xl bg-red-50 p-4 text-sm text-red-700">
          {{ error }}
        </div>
        <div
          v-else-if="!employees.length"
          class="rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500 dark:border-[#29476b]"
        >
          Tidak ada karyawan pada shift ini.
        </div>
        <div
          v-else
          class="divide-y divide-slate-100 rounded-xl border border-slate-200 dark:divide-[#29476b] dark:border-[#29476b]"
        >
          <div
            v-for="employee in employees"
            :key="employee.id"
            class="flex items-center justify-between gap-4 p-4"
          >
            <div class="min-w-0">
              <p class="truncate font-semibold text-slate-900 dark:text-white">
                {{ employee.name }}
              </p>
              <p class="mt-1 text-xs text-slate-500">NIK: {{ employee.nik }}</p>
            </div>
            <div class="shrink-0 text-right">
              <p class="text-xs font-medium text-slate-600 dark:text-slate-300">
                {{ roleLabel(employee.role) }}
              </p>
              <span
                :class="[
                  'mt-1 inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold',
                  employee.status === 'active'
                    ? 'bg-emerald-100 text-emerald-700'
                    : 'bg-slate-100 text-slate-600',
                ]"
                >{{ employee.status === 'active' ? 'Aktif' : 'Nonaktif' }}</span
              >
            </div>
          </div>
        </div>
      </div>
    </aside>
  </Teleport>
</template>
