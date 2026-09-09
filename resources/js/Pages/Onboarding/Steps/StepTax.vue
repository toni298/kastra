<script setup>
import Input from '@/Components/UI/Input.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import { BadgePercent, Check, ReceiptText } from 'lucide-vue-next'

const props = defineProps({
  modelValue: { type: Object, required: true },
})

const emit = defineEmits(['update:modelValue'])

const update = (key, value) => {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}
</script>

<template>
  <div>
    <div class="text-center">
      <h1 class="text-3xl font-semibold tracking-tight text-slate-950">Pengaturan Pajak</h1>
      <p class="mt-2 text-sm font-medium leading-6 text-slate-500">
        Apakah bisnis Anda menggunakan pajak?<br />
        Pengaturan ini dapat diubah kapan saja.
      </p>
    </div>

    <div class="mx-auto mt-8 max-w-4xl space-y-5">
      <div class="grid gap-4 sm:grid-cols-2">
        <button
          type="button"
          :aria-pressed="modelValue.enabled === true"
          :class="[
            'relative rounded-2xl border-2 p-5 text-left transition focus:outline-none focus:ring-4 focus:ring-emerald-500/10',
            modelValue.enabled === true
              ? 'border-emerald-500 bg-emerald-50/70'
              : 'border-slate-200 bg-white hover:border-emerald-200',
          ]"
          @click="update('enabled', true)"
        >
          <span class="flex items-center gap-3">
            <span
              class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-100 text-emerald-700"
            >
              <BadgePercent class="h-5 w-5" />
            </span>
            <span>
              <strong class="block text-sm text-slate-900">Ya, menggunakan pajak</strong>
              <small class="mt-1 block text-xs text-slate-500">
                Atur pajak transaksi bisnis.
              </small>
            </span>
          </span>
          <span
            v-if="modelValue.enabled === true"
            class="absolute right-4 top-4 grid h-6 w-6 place-items-center rounded-full bg-emerald-600 text-white"
          >
            <Check class="h-3.5 w-3.5" />
          </span>
        </button>

        <button
          type="button"
          :aria-pressed="modelValue.enabled === false"
          :class="[
            'relative rounded-2xl border-2 p-5 text-left transition focus:outline-none focus:ring-4 focus:ring-emerald-500/10',
            modelValue.enabled === false
              ? 'border-emerald-500 bg-emerald-50/70'
              : 'border-slate-200 bg-white hover:border-emerald-200',
          ]"
          @click="update('enabled', false)"
        >
          <span class="flex items-center gap-3">
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-100 text-slate-600">
              <ReceiptText class="h-5 w-5" />
            </span>
            <span>
              <strong class="block text-sm text-slate-900">Tidak menggunakan pajak</strong>
              <small class="mt-1 block text-xs text-slate-500"> Lewati pengaturan pajak. </small>
            </span>
          </span>
          <span
            v-if="modelValue.enabled === false"
            class="absolute right-4 top-4 grid h-6 w-6 place-items-center rounded-full bg-emerald-600 text-white"
          >
            <Check class="h-3.5 w-3.5" />
          </span>
        </button>
      </div>

      <div
        v-if="modelValue.enabled === true"
        class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2"
      >
        <label for="tax-rate" class="block">
          <span class="mb-1.5 block text-sm font-medium text-slate-700">
            Tarif PPN <span class="text-red-500">*</span>
          </span>
          <SelectInput
            id="tax-rate"
            :model-value="modelValue.rate"
            aria-label="Pilih tarif PPN"
            required
            @update:model-value="update('rate', Number($event))"
          >
            <option :value="11">11%</option>
            <option :value="12">12%</option>
          </SelectInput>
        </label>

        <Input
          id="company-npwp"
          :model-value="modelValue.npwp"
          label="NPWP Perusahaan"
          placeholder="Contoh: 12.345.678.9-012.000"
          autocomplete="off"
          @update:model-value="update('npwp', $event)"
        />

        <fieldset class="sm:col-span-2">
          <legend class="text-sm font-medium text-slate-700">Metode Pencatatan PPN *</legend>
          <div class="mt-3 flex flex-wrap gap-x-6 gap-y-3">
            <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
              <input
                :checked="modelValue.mode === 'inclusive'"
                type="radio"
                name="tax-recording-mode"
                value="inclusive"
                class="border-slate-300 text-emerald-600 focus:ring-emerald-500"
                @change="update('mode', 'inclusive')"
              />
              Gross (Termasuk PPN)
            </label>
            <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
              <input
                :checked="modelValue.mode === 'exclusive'"
                type="radio"
                name="tax-recording-mode"
                value="exclusive"
                class="border-slate-300 text-emerald-600 focus:ring-emerald-500"
                @change="update('mode', 'exclusive')"
              />
              Net (Belum termasuk PPN)
            </label>
          </div>
        </fieldset>
      </div>
    </div>
  </div>
</template>
