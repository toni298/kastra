<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import { useAccessControl } from '@/Composables/useAccessControl'
import EmployeeFormModal from '../Components/EmployeeFormModal.vue'
import EmployeeTable from '../Components/EmployeeTable.vue'
import ResetPinModal from '../Components/ResetPinModal.vue'

const props = defineProps({
  employees: { type: Object, default: () => ({ data: [] }) },
  branches: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})
const selected = ref(null)
const formOpen = ref(false)
const pinOpen = ref(false)
const deleteOpen = ref(false)
const tableLoading = ref(props.loading)
const { can } = useAccessControl()
const request = (overrides = {}) => {
  tableLoading.value = true
  router.get(
    route('hr.employees.index'),
    { ...props.filters, ...overrides },
    {
      only: ['employees', 'filters', 'activeTab'],
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        tableLoading.value = false
      },
    }
  )
}
const applyFilter = (value) =>
  request({ ...(typeof value === 'string' ? { search: value } : value), cursor: undefined })
const navigate = ({ cursor }) => cursor && request({ cursor })
const openCreate = () => {
  selected.value = null
  formOpen.value = true
}
const openEdit = (employee) => {
  selected.value = employee
  formOpen.value = true
}
const openPin = (employee) => {
  selected.value = employee
  pinOpen.value = true
}
const openDelete = (employee) => {
  selected.value = employee
  deleteOpen.value = true
}
const deleteEmployee = () =>
  router.delete(route('hr.employees.destroy', selected.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      deleteOpen.value = false
      selected.value = null
    },
  })
</script>

<template>
  <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
    <div>
      <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Daftar Karyawan</h2>
      <p class="mt-1 text-sm text-slate-500">
        Kelola identitas, kompensasi, komisi, dan status karyawan.
      </p>
    </div>
    <Button v-if="can('hr.employees.create')" type="button" @click="openCreate">
      <Plus :size="16" class="mr-2" />Tambah Karyawan
    </Button>
  </div>
  <EmployeeTable
    :items="employees"
    :filters="filters"
    :loading="tableLoading"
    @filter="applyFilter"
    @navigate="navigate"
    @create="openCreate"
    @edit="openEdit"
    @reset-pin="openPin"
    @delete="openDelete"
  />
  <EmployeeFormModal
    v-if="formOpen"
    :employee="selected"
    :branches="branches"
    @close="formOpen = false"
  />
  <ResetPinModal v-if="pinOpen && selected" :employee="selected" @close="pinOpen = false" />
  <Modal
    v-if="deleteOpen && selected"
    :model-value="true"
    title="Hapus Karyawan"
    description="Data karyawan akan dihapus secara soft delete."
    size="sm"
    @update:model-value="deleteOpen = false"
  >
    <p class="text-sm text-slate-600 dark:text-slate-300">
      Yakin ingin menghapus {{ selected.name }}?
    </p>
    <template #footer
      ><Button variant="secondary" @click="deleteOpen = false">Batal</Button
      ><Button variant="danger" @click="deleteEmployee">Hapus</Button></template
    >
  </Modal>
</template>
