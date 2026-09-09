<script setup>
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import NumberInput from '@/Components/UI/NumberInput.vue'
import { useToastify } from '@/Composables/useToastify'
import { useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

const props = defineProps({
  stock: { type: Object, default: null },
  warehouses: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])
const toast = useToastify()

const editing = Boolean(props.stock)
const singleWarehouseId = props.warehouses.length === 1 ? (props.warehouses[0]?.id ?? '') : ''
const form = useForm({
  product_id: props.stock?.product?.id ?? '',
  gudang_id: props.stock?.gudang_id ?? singleWarehouseId,
  quantity: props.stock?.quantity ?? 0,
})
const initialProduct = props.stock?.product
  ? [
      {
        id: props.stock.product.id,
        text: `${props.stock.product.name} (${props.stock.product.sku})`,
      },
    ]
  : []
const showWarehouseSelect = props.warehouses.length > 1
const productEndpoint = computed(
  () =>
    `${route('search.products')}?gudang_id=${encodeURIComponent(form.gudang_id || '')}&selection=product`
)
const productPlaceholder = computed(() =>
  showWarehouseSelect ? 'Pilih gudang terlebih dahulu' : 'Pilih Produk'
)
const productDisabled = computed(() => showWarehouseSelect && !form.gudang_id)
const handleProductChange = (option) => {
  if (!editing && option?.already_registered) {
    form.product_id = ''
    toast.warning('Maaf produk ini sudah terdaftar di gudang pilihan anda.')
  }
}
watch(
  () => form.gudang_id,
  () => {
    form.product_id = ''
  }
)

const submit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('close') }
  editing
    ? form.put(route('inventory.products.update', props.stock.id), options)
    : form.post(route('inventory.products.store'), options)
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="editing ? 'Edit Stok Produk' : 'Tambah Produk ke Inventory'"
    description="Pilih produk, Inventory, dan masukkan jumlah stok."
    size="lg"
    @update:model-value="emit('close')"
  >
    <form id="inventory-product-form" class="space-y-5" @submit.prevent="submit">
      <AsyncSelect
        v-if="showWarehouseSelect"
        v-model="form.gudang_id"
        label="Inventory"
        :endpoint="route('search.gudang')"
        :initial-options="
          warehouses.map((warehouse) => ({ id: warehouse.id, text: warehouse.nama }))
        "
        placeholder="Cari dan pilih Inventory..."
        :error="form.errors.gudang_id"
        required
      />
      <input v-else type="hidden" name="gudang_id" :value="form.gudang_id" />

      <AsyncSelect
        v-model="form.product_id"
        label="Produk"
        :placeholder="productPlaceholder"
        :endpoint="productEndpoint"
        :initial-options="initialProduct"
        :disabled="productDisabled"
        :error="form.errors.product_id"
        required
        @change="handleProductChange"
      />

      <NumberInput
        v-model="form.quantity"
        label="Jumlah Stok"
        placeholder="Contoh: 50"
        required
        :error="form.errors.quantity"
      />
    </form>

    <template #footer>
      <Button variant="secondary" @click="emit('close')">Batal</Button>
      <Button type="submit" form="inventory-product-form" :loading="form.processing">
        {{ editing ? 'Simpan Perubahan' : 'Tambah ke Gudang' }}
      </Button>
    </template>
  </Modal>
</template>
