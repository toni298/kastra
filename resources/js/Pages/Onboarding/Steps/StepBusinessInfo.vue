<script setup>
import FileUpload from '@/Components/UI/FileUpload.vue'
import Input from '@/Components/UI/Input.vue'

const props = defineProps({
  modelValue: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:modelValue'])

const update = (key, value) => {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}
</script>

<template>
  <div>
    <div class="text-center">
      <h1 class="text-3xl font-semibold tracking-tight text-slate-950">Informasi Bisnis</h1>
      <p class="mt-2 text-sm font-medium text-slate-500">Lengkapi identitas utama bisnis Anda.</p>
    </div>

    <div
      class="mx-auto mt-6 grid max-w-5xl gap-5 rounded-2xl border border-slate-200 bg-white p-5 lg:grid-cols-[minmax(250px,0.8fr)_minmax(0,1.5fr)]"
    >
      <div class="space-y-4">
        <FileUpload
          id="business-logo"
          :model-value="modelValue.logo"
          accept=".jpg,.jpeg,.png,.webp"
          label="Logo Bisnis"
          hint="JPG, PNG, atau WebP. Maks. 2 MB."
          :error="errors['business.logo']"
          :max-size="2"
          @update:model-value="update('logo', $event)"
        />

        <label for="business-address" class="block">
          <span class="mb-1.5 block text-sm font-medium text-slate-700">
            Alamat <span class="text-red-500">*</span>
          </span>
          <textarea
            id="business-address"
            :value="modelValue.address"
            rows="4"
            required
            placeholder="Contoh: Jl. Merdeka No. 10, Kecamatan Gambir"
            :aria-invalid="Boolean(errors['business.address'])"
            class="w-full resize-none rounded-xl border border-slate-300 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
            @input="update('address', $event.target.value)"
          ></textarea>
          <p v-if="errors['business.address']" class="mt-1.5 text-sm font-medium text-red-500">
            {{ errors['business.address'] }}
          </p>
        </label>
      </div>

      <div class="grid content-start gap-4 sm:grid-cols-2">
        <Input
          id="business-name"
          :model-value="modelValue.name"
          label="Nama Bisnis"
          placeholder="Contoh: Kastra Nusantara"
          :error="errors['business.name']"
          required
          autocomplete="organization"
          class="sm:col-span-2"
          @update:model-value="update('name', $event)"
        />
        <Input
          id="business-legal-name"
          :model-value="modelValue.legal_name"
          label="Nama Legal"
          placeholder="Contoh: PT Kastra Nusantara Indonesia"
          :error="errors['business.legal_name']"
          autocomplete="organization"
          @update:model-value="update('legal_name', $event)"
        />
        <Input
          id="business-phone"
          :model-value="modelValue.phone"
          type="tel"
          label="Telepon"
          placeholder="Contoh: 0812 3456 7890"
          :error="errors['business.phone']"
          required
          autocomplete="tel"
          @update:model-value="update('phone', $event)"
        />
        <Input
          id="business-city"
          :model-value="modelValue.city"
          label="Kota"
          placeholder="Contoh: Jakarta Pusat"
          :error="errors['business.city']"
          required
          autocomplete="address-level2"
          @update:model-value="update('city', $event)"
        />
        <Input
          id="business-province"
          :model-value="modelValue.province"
          label="Provinsi (Opsional)"
          placeholder="Contoh: DKI Jakarta"
          :error="errors['business.province']"
          autocomplete="address-level1"
          @update:model-value="update('province', $event)"
        />
        <Input
          id="business-postal-code"
          :model-value="modelValue.postal_code"
          label="Kode Pos (Opsional)"
          placeholder="Contoh: 10110"
          :error="errors['business.postal_code']"
          inputmode="numeric"
          autocomplete="postal-code"
          @update:model-value="update('postal_code', $event)"
        />
      </div>
    </div>
  </div>
</template>
