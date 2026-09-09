<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { ShoppingCart } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  item: { type: Object, default: null },
  type: { type: String, required: true },
})

const emit = defineEmits(['close'])

const buildFormData = () => ({
  kode: props.type === 'cabang' ? (props.item?.code ?? '') : (props.item?.kode ?? ''),
  nama: props.type === 'cabang' ? (props.item?.name ?? '') : (props.item?.nama ?? ''),
  email: props.item?.email ?? '',
  telepon: props.type === 'cabang' ? (props.item?.phone ?? '') : (props.item?.telepon ?? ''),
  alamat: props.type === 'cabang' ? (props.item?.address ?? '') : (props.item?.alamat ?? ''),
  kota: props.type === 'cabang' ? (props.item?.city ?? '') : (props.item?.kota ?? ''),
  provinsi: props.type === 'cabang' ? (props.item?.province ?? '') : (props.item?.provinsi ?? ''),
  kode_pos:
    props.type === 'cabang' ? (props.item?.postal_code ?? '') : (props.item?.kode_pos ?? ''),
  ...(props.type === 'cabang'
    ? {
        status: props.item?.status ?? 'active',
        is_store_enabled: props.item?.is_store_enabled ?? false,
      }
    : { aktif: props.item?.aktif ?? true }),
  ...(props.type === 'outlet' ? { cabang_id: props.item?.branch_id ?? '' } : {}),
})

const form = useForm(buildFormData())

const resetForm = () => {
  form.reset(buildFormData())
}

watch(() => [props.type, props.item], resetForm, { immediate: true })

const editing = computed(() => Boolean(props.item))
const entityLabel = computed(() => props.type.charAt(0).toUpperCase() + props.type.slice(1))
const description = computed(() =>
  editing.value
    ? `Perbarui informasi ${props.type} perusahaan.`
    : `Tambahkan ${props.type} baru ke struktur perusahaan.`
)
const selectedCabang = computed(() => {
  if (props.type !== 'outlet') {
    return []
  }

  const cabang = props.item?.cabang ?? props.item?.branch
  return cabang ? [{ id: cabang.id, text: cabang.name }] : []
})
const active = computed({
  get: () => (props.type === 'cabang' ? form.status === 'active' : form.aktif),
  set: (value) => {
    if (props.type === 'cabang') {
      form.status = value ? 'active' : 'inactive'
      return
    }

    form.aktif = value
  },
})

const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') }
  editing.value
    ? form.put(route(`${props.type}.update`, props.item.id), options)
    : form.post(route(`${props.type}.store`), options)
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="`${editing ? 'Edit' : 'Tambah'} ${entityLabel}`"
    :description="description"
    size="lg"
    @update:model-value="emit('close')"
  >
    <form id="organization-form" class="space-y-5" @submit.prevent="submit">
      <div class="grid gap-4 sm:grid-cols-2">
        <Input v-model="form.nama" label="Nama" required :error="form.errors.nama" />
        <Input v-model="form.kode" label="Kode (opsional)" :error="form.errors.kode" />
        <Input v-model="form.email" label="Email" type="email" :error="form.errors.email" />
        <Input v-model="form.telepon" label="Telepon" :error="form.errors.telepon" />
      </div>

      <div v-if="type === 'outlet'">
        <AsyncSelect
          v-model="form.cabang_id"
          label="Cabang (opsional)"
          :endpoint="route('search.cabang')"
          :initial-options="selectedCabang"
          :error="form.errors.cabang_id"
          placeholder="Cari dan pilih cabang"
        />
      </div>

      <Input v-model="form.alamat" label="Alamat" :error="form.errors.alamat" />
      <div class="grid gap-4 sm:grid-cols-3">
        <Input v-model="form.kota" label="Kota" :error="form.errors.kota" />
        <Input v-model="form.provinsi" label="Provinsi" :error="form.errors.provinsi" />
        <Input v-model="form.kode_pos" label="Kode Pos" :error="form.errors.kode_pos" />
      </div>
      <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
        <input v-model="active" type="checkbox" class="rounded text-emerald-600" />
        Status aktif
      </label>

      <div
        v-if="type === 'cabang'"
        class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900"
      >
        <div class="flex items-start gap-3">
          <div class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-500 text-white">
            <ShoppingCart class="h-6 w-6" />
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-900 dark:text-white">
              Buka toko online untuk cabang ini?
            </p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              Tampilkan produk cabang di katalog online.
            </p>
            <div class="mt-4 flex gap-3">
              <label
                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
              >
                <input
                  v-model="form.is_store_enabled"
                  type="radio"
                  :value="false"
                  class="h-4 w-4 rounded border-slate-300 text-emerald-600"
                />
                <span>Tidak, nanti saja</span>
              </label>
              <label
                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
              >
                <input
                  v-model="form.is_store_enabled"
                  type="radio"
                  :value="true"
                  class="h-4 w-4 rounded border-slate-300 text-emerald-600"
                />
                <span>Ya, buka toko online</span>
              </label>
            </div>
            <p v-if="form.errors.is_store_enabled" class="mt-3 text-sm text-rose-600">
              {{ form.errors.is_store_enabled }}
            </p>
          </div>
        </div>
      </div>
    </form>
    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button type="submit" form="organization-form" :loading="form.processing">
        {{ editing ? 'Simpan perubahan' : `Tambah ${entityLabel}` }}
      </Button>
    </template>
  </Modal>
</template>
