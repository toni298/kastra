<script setup>
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import { useAuthorization } from '@/Composables/useAuthorization'

const props = defineProps({ company: { type: Object, required: true } })
const { can } = useAuthorization()

const companyForm = useForm({
  name: props.company.name ?? '',
  legal_name: props.company.legal_name ?? '',
  npwp: props.company.npwp ?? '',
  nib: props.company.nib ?? '',
  email: props.company.email ?? '',
  phone: props.company.phone ?? '',
  address: props.company.address ?? '',
  city: props.company.city ?? '',
  province: props.company.province ?? '',
  postal_code: props.company.postal_code ?? '',
  country_code: props.company.country_code ?? 'ID',
})
const taxForm = useForm({
  kode: 'PPN',
  nama: 'PPN (Pajak Pertambahan Nilai)',
  jenis: 'penjualan',
  persentase: 11,
  mode: 'inclusive',
  aktif: true,
})

const submitTax = () =>
  taxForm.post(route('tax-configurations.store'), {
    preserveScroll: true,
    onSuccess: () => {
      taxForm.reset('persentase', 'mode')
    },
  })
</script>

<template>
  <div class="grid gap-4 sm:grid-cols-2 p-5">
    <label for="tax-rate" class="block">
      <span class="mb-1.5 block text-sm font-medium dark:text-white text-slate-700">
        Tarif PPN <span class="text-red-500">*</span></span
      >
      <div id="tax-rate">
        <div
          class="flex h-[46px] items-center rounded-xl border bg-white transition focus-within:ring-4 dark:bg-[#0a1b33] border-slate-300 focus-within:border-emerald-500 focus-within:ring-emerald-500/10 dark:border-[#29476b]"
        >
          <select
            v-model.number="taxForm.persentase"
            class="h-full w-full rounded-xl border-0 bg-transparent py-2.5 pl-3 pr-9 text-sm font-medium text-slate-700 focus:border-transparent focus:outline-none focus:ring-0 dark:bg-transparent dark:text-white"
            aria-label="Pilih tarif PPN"
            required
          >
            <option value="0">0%</option>
            <option value="11">11%</option>
            <option value="12">12%</option>
          </select>
        </div>
      </div>
    </label>

    <div class="w-full">
      <label
        for="company-npwp"
        class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
        >NPWP Perusahaan</label
      >
      <div class="relative">
        <input
          autocomplete="off"
          id="company-npwp"
          type="text"
          placeholder="Contoh: 12.345.678.9-012.000"
          class="w-full rounded-xl border px-3.5 py-3 text-sm text-slate-900 outline-none transition duration-150 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:bg-[#0a1b33] dark:text-white dark:placeholder:text-slate-500 border-slate-300 dark:border-[#29476b] bg-white"
          v-model="companyForm.npwp"
        />
      </div>
    </div>

    <fieldset class="sm:col-span-2">
      <legend class="text-sm font-medium text-slate-700 dark:text-white">
        Metode Pencatatan PPN *
      </legend>
      <div class="mt-3 flex flex-wrap gap-x-6 gap-y-3">
        <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
          <input
            v-model="taxForm.mode"
            checked
            value="inclusive"
            type="radio"
            name="tax-recording-mode"
            class="border-slate-300 text-emerald-600 focus:ring-emerald-500"
          />
          <div class="dark:text-white">Gross (Termasuk PPN)</div>
        </label>
        <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
          <input
            v-model="taxForm.mode"
            value="exclusive"
            type="radio"
            name="tax-recording-mode"
            class="border-slate-300 text-emerald-600 focus:ring-emerald-500"
          />
          <div class="dark:text-white">Net (Belum termasuk PPN)</div>
        </label>
      </div>
    </fieldset>
  </div>
  <form @submit.prevent="submitTax">
    <FormActions>
      <Button v-if="can('taxes.create')" type="submit" :loading="taxForm.processing"
        >Simpan Pengaturan Pajak</Button
      >
    </FormActions>
  </form>
</template>

<style scoped></style>
