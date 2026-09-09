<script setup>
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/UI/Button.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'

const props = defineProps({
  company: { type: Object, required: true },
  currencies: { type: Array, default: () => [] },
  timezones: { type: Array, default: () => [] },
})

const form = useForm({
  currency_code: props.company.currency_code ?? 'IDR',
  timezone: props.company.timezone ?? 'Asia/Jakarta',
  locale: props.company.locale ?? 'id',
})
const submit = () =>
  form.put(route('company.settings.update', props.company.id), { preserveScroll: true })
</script>

<template>
  <form @submit.prevent="submit">
    <div class="grid gap-4 p-5 sm:grid-cols-3 sm:p-6">
      <label class="space-y-1.5">
        <span class="block text-sm font-medium text-slate-700 dark:text-slate-200">Mata uang</span>
        <SelectInput v-model="form.currency_code" aria-label="Mata uang">
          <option v-for="currency in currencies" :key="currency" :value="currency">
            {{ currency }}
          </option>
        </SelectInput>
      </label>
      <label class="space-y-1.5">
        <span class="block text-sm font-medium text-slate-700 dark:text-slate-200">Zona waktu</span>
        <SelectInput v-model="form.timezone" aria-label="Zona waktu">
          <option v-for="timezone in timezones" :key="timezone" :value="timezone">
            {{ timezone }}
          </option>
        </SelectInput>
      </label>
      <label class="space-y-1.5">
        <span class="block text-sm font-medium text-slate-700 dark:text-slate-200">Bahasa</span>
        <SelectInput v-model="form.locale" aria-label="Bahasa">
          <option value="id">Indonesia</option>
          <option value="en">English</option>
        </SelectInput>
      </label>
    </div>
    <FormActions>
      <Button type="submit" :loading="form.processing">Simpan Pengaturan</Button>
    </FormActions>
  </form>
</template>
