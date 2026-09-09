<script setup>
import Button from '@/Components/UI/Button.vue'
import IconButton from '@/Components/UI/IconButton.vue'
import Input from '@/Components/UI/Input.vue'
import { Check, PackageOpen, Plus, Trash2, Warehouse } from 'lucide-vue-next'

const props = defineProps({
  modelValue: { type: Array, required: true },
  hasWarehouses: { type: Boolean, default: null },
})

const emit = defineEmits(['update:modelValue', 'update:hasWarehouses'])

const update = (index, key, value) => {
  const items = props.modelValue.map((item) => ({ ...item }))
  items[index][key] = value
  emit('update:modelValue', items)
}

const add = () => {
  emit('update:modelValue', [...props.modelValue, { name: '', code: '', address: '' }])
}

const remove = (index) => {
  emit(
    'update:modelValue',
    props.modelValue.filter((_, itemIndex) => itemIndex !== index)
  )
}
</script>

<template>
  <div>
    <div class="text-center">
      <h1 class="text-3xl font-semibold tracking-tight text-slate-950">Gudang</h1>
      <p class="mt-2 text-sm font-medium text-slate-500">
        Tambahkan gudang penyimpanan bisnis Anda.
      </p>
    </div>

    <div class="mx-auto mt-8 max-w-4xl space-y-4">
      <div class="grid gap-4 sm:grid-cols-2">
        <button
          type="button"
          :aria-pressed="hasWarehouses === true"
          :class="[
            'relative rounded-2xl border-2 p-5 text-left transition focus:outline-none focus:ring-4 focus:ring-emerald-500/10',
            hasWarehouses === true
              ? 'border-emerald-500 bg-emerald-50/70'
              : 'border-slate-200 bg-white hover:border-emerald-200',
          ]"
          @click="emit('update:hasWarehouses', true)"
        >
          <span class="flex items-center gap-3">
            <span
              class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-100 text-emerald-700"
            >
              <Warehouse class="h-5 w-5" />
            </span>
            <span>
              <strong class="block text-sm text-slate-900">Ya, punya gudang</strong>
              <small class="mt-1 block text-xs text-slate-500">
                Tambahkan lokasi penyimpanan stok.
              </small>
            </span>
          </span>
          <span
            v-if="hasWarehouses === true"
            class="absolute right-4 top-4 grid h-6 w-6 place-items-center rounded-full bg-emerald-600 text-white"
          >
            <Check class="h-3.5 w-3.5" />
          </span>
        </button>

        <button
          type="button"
          :aria-pressed="hasWarehouses === false"
          :class="[
            'relative rounded-2xl border-2 p-5 text-left transition focus:outline-none focus:ring-4 focus:ring-emerald-500/10',
            hasWarehouses === false
              ? 'border-emerald-500 bg-emerald-50/70'
              : 'border-slate-200 bg-white hover:border-emerald-200',
          ]"
          @click="emit('update:hasWarehouses', false)"
        >
          <span class="flex items-center gap-3">
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-100 text-slate-600">
              <PackageOpen class="h-5 w-5" />
            </span>
            <span>
              <strong class="block text-sm text-slate-900">Tidak punya gudang</strong>
              <small class="mt-1 block text-xs text-slate-500">
                Lewati pengisian data gudang.
              </small>
            </span>
          </span>
          <span
            v-if="hasWarehouses === false"
            class="absolute right-4 top-4 grid h-6 w-6 place-items-center rounded-full bg-emerald-600 text-white"
          >
            <Check class="h-3.5 w-3.5" />
          </span>
        </button>
      </div>

      <template v-if="hasWarehouses === true">
        <div
          v-for="(warehouse, index) in modelValue"
          :key="index"
          class="rounded-2xl border border-slate-200 bg-white p-5"
        >
          <div class="mb-4 flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Gudang {{ index + 1 }}</h2>
            <IconButton
              v-if="modelValue.length > 1"
              :label="`Hapus gudang ${index + 1}`"
              variant="danger"
              @click="remove(index)"
            >
              <Trash2 class="h-5 w-5" />
            </IconButton>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <Input
              :id="`warehouse-name-${index}`"
              :model-value="warehouse.name"
              label="Nama Gudang"
              placeholder="Contoh: Gudang Utama Jakarta"
              required
              @update:model-value="update(index, 'name', $event)"
            />
            <Input
              :id="`warehouse-code-${index}`"
              :model-value="warehouse.code"
              label="Kode Gudang"
              placeholder="Contoh: GDG-JKT"
              required
              @update:model-value="update(index, 'code', $event)"
            />
            <label :for="`warehouse-address-${index}`" class="block sm:col-span-2">
              <span class="mb-1.5 block text-sm font-medium text-slate-700">
                Alamat Gudang <span class="text-red-500">*</span>
              </span>
              <textarea
                :id="`warehouse-address-${index}`"
                :value="warehouse.address"
                rows="2"
                required
                placeholder="Contoh: Jl. Industri No. 8, Jakarta Timur"
                class="w-full resize-none rounded-xl border border-slate-300 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                @input="update(index, 'address', $event.target.value)"
              ></textarea>
            </label>
          </div>
        </div>

        <Button
          variant="secondary"
          class="flex w-full gap-2 border-2 border-dashed py-4 text-emerald-600 hover:border-emerald-400 hover:bg-emerald-50"
          @click="add"
        >
          <Plus class="h-5 w-5" />
          Tambah Gudang
        </Button>
      </template>
    </div>
  </div>
</template>
