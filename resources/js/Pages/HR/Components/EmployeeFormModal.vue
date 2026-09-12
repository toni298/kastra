<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  employee: { type: Object, default: null },
  branches: { type: Array, default: () => [] },
  shifts: { type: Array, default: () => [] },
})
const emit = defineEmits(['close'])
const editing = computed(() => Boolean(props.employee))
const form = useForm({
  branch_id: props.employee?.branch?.id ?? '',
  shift_id: props.employee?.shift?.id ?? '',
  nik: props.employee?.nik ?? '',
  name: props.employee?.name ?? '',
  phone: props.employee?.phone ?? '',
  email: props.employee?.email ?? '',
  address: props.employee?.address ?? '',
  role: props.employee?.role ?? 'staff',
  base_salary: props.employee?.base_salary ?? 0,
  allowance: props.employee?.allowance ?? 0,
  commission_type: props.employee?.commission_type ?? 'percentage',
  commission_value: props.employee?.commission_value ?? 0,
  status: props.employee?.status ?? 'active',
  hired_at: props.employee?.hired_at ?? '',
  pin: '',
})
const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') }
  editing.value
    ? form.put(route('hr.employees.update', props.employee.id), options)
    : form.post(route('hr.employees.store'), options)
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="editing ? 'Edit Karyawan' : 'Tambah Karyawan'"
    :description="
      editing
        ? 'Perbarui data karyawan dan skema kompensasinya.'
        : 'Tambahkan data karyawan baru ke perusahaan.'
    "
    size="xl"
    @update:model-value="emit('close')"
  >
    <form id="employee-form" class="space-y-5" @submit.prevent="submit">
      <div class="grid gap-4 sm:grid-cols-2">
        <Input v-model="form.name" label="Nama lengkap" required :error="form.errors.name" />
        <Input v-model="form.nik" label="NIK" required :error="form.errors.nik" />
        <Input v-model="form.phone" label="Nomor telepon" :error="form.errors.phone" />
        <Input v-model="form.email" label="Email" type="email" :error="form.errors.email" />
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
            >Cabang</label
          >
          <select
            v-model="form.branch_id"
            class="w-full rounded-xl border-slate-300 py-3 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Semua cabang</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
              {{ branch.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
            >Shift absensi</label
          >
          <select
            v-model="form.shift_id"
            class="w-full rounded-xl border-slate-300 py-3 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="">Tanpa shift</option>
            <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
              {{ shift.name }} ({{ shift.start_time?.slice(0, 5) }} -
              {{ shift.end_time?.slice(0, 5) }})
            </option>
          </select>
        </div>
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
            >Role</label
          >
          <select
            v-model="form.role"
            class="w-full rounded-xl border-slate-300 py-3 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="cashier">Kasir</option>
            <option value="supervisor">Supervisor</option>
            <option value="staff">Staff</option>
          </select>
        </div>
        <Input
          v-model="form.base_salary"
          label="Gaji pokok"
          type="number"
          min="0"
          required
          :error="form.errors.base_salary"
        />
        <Input
          v-model="form.allowance"
          label="Tunjangan"
          type="number"
          min="0"
          :error="form.errors.allowance"
        />
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
            >Skema komisi</label
          >
          <select
            v-model="form.commission_type"
            class="w-full rounded-xl border-slate-300 py-3 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="percentage">Persentase omzet (%)</option>
            <option value="per_quantity">Nominal per qty (Rp)</option>
          </select>
        </div>
        <Input
          v-model="form.commission_value"
          label="Nilai komisi"
          type="number"
          min="0"
          step="0.01"
          required
          :error="form.errors.commission_value"
        />
        <Input
          v-model="form.hired_at"
          label="Tanggal bergabung"
          type="date"
          :error="form.errors.hired_at"
        />
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
            >Status</label
          >
          <select
            v-model="form.status"
            class="w-full rounded-xl border-slate-300 py-3 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          >
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
          </select>
        </div>
      </div>
      <Input
        v-model="form.pin"
        label="PIN 6 digit"
        type="password"
        inputmode="numeric"
        maxlength="6"
        :required="!editing"
        :placeholder="editing ? 'Kosongkan jika tetap' : 'Masukkan PIN'"
        :error="form.errors.pin"
      />
      <Input v-model="form.address" label="Alamat" :error="form.errors.address" />
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="employee-form" :loading="form.processing">{{
        editing ? 'Simpan perubahan' : 'Tambah karyawan'
      }}</Button></template
    >
  </Modal>
</template>
