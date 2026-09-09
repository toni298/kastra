<script setup>
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'

const props = defineProps({
  type: { type: String, required: true },
  title: { type: String, required: true },
  options: { type: Array, default: () => [] },
})
const emit = defineEmits(['close'])
const form = useForm({
  parent_id: '',
  kode: '',
  nama: '',
  kategori: 'aset',
  is_header: false,
  aktif: true,
  tax_in_account_id: '',
  tax_out_account_id: '',
  jenis: 'penjualan',
  persentase: '',
  mode: 'exclusive',
  document_type: '',
  prefix: '',
  format: '{PREFIX}/{YYYY}/{MM}/{SEQUENCE}',
  reset_period: 'never',
})
const storeRoute = {
  coa: 'chart-of-accounts.store',
  taxes: 'tax-configurations.store',
  numbers: 'number-generators.store',
}
const submit = () =>
  form.post(route(storeRoute[props.type]), { preserveScroll: true, onSuccess: () => emit('close') })
</script>
<template>
  <Modal
    :model-value="true"
    :title="`Tambah ${title}`"
    description="Lengkapi data dengan benar."
    size="lg"
    @update:model-value="emit('close')"
  >
    <form id="accounting-form" class="space-y-4" @submit.prevent="submit">
      <div v-if="type === 'coa'" class="grid gap-4 sm:grid-cols-2">
        <Input v-model="form.kode" label="Kode akun" required :error="form.errors.kode" />
        <Input v-model="form.nama" label="Nama akun" required :error="form.errors.nama" />
        <SelectInput v-model="form.parent_id" aria-label="Akun induk"
          ><option value="">Tanpa induk</option>
          <option v-for="item in options" :key="item.id" :value="item.id">
            {{ item.kode }} - {{ item.nama }}
          </option></SelectInput
        >
        <SelectInput v-model="form.kategori" aria-label="Kategori"
          ><option
            v-for="item in ['aset', 'liabilitas', 'ekuitas', 'pendapatan', 'beban']"
            :key="item"
            :value="item"
          >
            {{ item }}
          </option></SelectInput
        >
      </div>
      <div v-else-if="type === 'taxes'" class="grid gap-4 sm:grid-cols-2">
        <Input v-model="form.kode" label="Kode pajak" required :error="form.errors.kode" />
        <Input v-model="form.nama" label="Nama pajak" required :error="form.errors.nama" />
        <Input
          v-model="form.persentase"
          label="Persentase"
          type="number"
          step="0.0001"
          required
          :error="form.errors.persentase"
        />
        <SelectInput v-model="form.jenis" aria-label="Jenis pajak"
          ><option
            v-for="item in ['penjualan', 'pembelian', 'potong', 'pungut']"
            :key="item"
            :value="item"
          >
            {{ item }}
          </option></SelectInput
        >
        <SelectInput v-model="form.mode" aria-label="Mode pajak"
          ><option value="exclusive">Tax Exclusive</option>
          <option value="inclusive">Tax Inclusive</option></SelectInput
        >
        <SelectInput v-model="form.tax_in_account_id" aria-label="Akun pajak masuk"
          ><option value="">Akun pajak masuk</option>
          <option v-for="item in options" :key="item.id" :value="item.id">
            {{ item.kode }} - {{ item.nama }}
          </option></SelectInput
        >
        <SelectInput v-model="form.tax_out_account_id" aria-label="Akun pajak keluar"
          ><option value="">Akun pajak keluar</option>
          <option v-for="item in options" :key="item.id" :value="item.id">
            {{ item.kode }} - {{ item.nama }}
          </option></SelectInput
        >
      </div>
      <div v-else class="grid gap-4 sm:grid-cols-2">
        <Input
          v-model="form.document_type"
          label="Jenis dokumen"
          required
          :error="form.errors.document_type"
        />
        <Input v-model="form.prefix" label="Prefix" required :error="form.errors.prefix" />
        <Input
          v-model="form.format"
          class="sm:col-span-2"
          label="Format"
          required
          :error="form.errors.format"
        />
        <SelectInput v-model="form.reset_period" aria-label="Periode reset"
          ><option value="never">Tidak pernah</option>
          <option value="yearly">Tahunan</option>
          <option value="monthly">Bulanan</option></SelectInput
        >
      </div>
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="accounting-form" :loading="form.processing"
        >Simpan</Button
      ></template
    >
  </Modal>
</template>
