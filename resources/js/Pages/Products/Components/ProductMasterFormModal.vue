<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  item: { type: Object, default: null },
  master: { type: String, required: true },
  label: { type: String, required: true },
})
const emit = defineEmits(['close', 'saved'])

const page = usePage()
const editing = computed(() => Boolean(props.item))
const namePlaceholder = computed(
  () =>
    ({
      product_categories: 'Contoh: Perawatan Tubuh',
      product_brands: 'Contoh: Lifebuoy',
      units: 'Contoh: Pcs',
    })[props.master] ?? `Contoh: ${props.label}`
)

const form = useForm({
  name: props.item?.name ?? '',
  code: props.item?.code ?? '',
  is_active: props.item?.is_active ?? true,
})
const submit = () => {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      emit('saved', page.props.flash?.[props.master] ?? null)
      emit('close')
    },
  }
  editing.value
    ? form.put(route('products.master.update', [props.master, props.item.id]), options)
    : form.post(route('products.master.store', props.master), options)
}
</script>
<template>
  <Modal
    :model-value="true"
    :title="`${editing ? 'Edit' : 'Tambah'} ${label}`"
    description="Lengkapi data master produk."
    size="md"
    @update:model-value="emit('close')"
    ><form id="master-form" class="space-y-4" @submit.prevent="submit">
      <Input
        v-model="form.name"
        :label="`Nama ${label}`"
        :placeholder="namePlaceholder"
        required
        :error="form.errors.name"
      /><Input
        v-if="master === 'units'"
        v-model="form.code"
        label="Kode Satuan"
        placeholder="Contoh: PCS"
        required
        :error="form.errors.code"
      /><label
        class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200"
        ><input v-model="form.is_active" type="checkbox" class="rounded text-emerald-600" />Status
        aktif</label
      >
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="master-form" :loading="form.processing">{{
        editing ? 'Simpan Perubahan' : `Tambah ${label}`
      }}</Button></template
    ></Modal
  >
</template>
