<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import ProductImageField from './ProductImageField.vue'
import ProductMasterFormModal from './ProductMasterFormModal.vue'

const props = defineProps({
  product: { type: Object, default: null },
  suggestions: { type: Object, default: null },
  branches: { type: Array, default: () => [] },
  canManageImages: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'saved'])

const editing = Boolean(props.product)
const productImages = Array.isArray(props.product?.images) ? props.product.images : []
const singleBranch = computed(() => (props.branches.length === 1 ? props.branches[0] : null))
const branchStocks = ref(props.branches.map((branch) => ({ branch_id: branch.id, quantity: 0 })))
const form = useForm({
  name: props.product?.name ?? '',
  sku: editing ? props.product.sku : (props.suggestions?.sku ?? ''),
  barcode: editing ? (props.product.barcode ?? '') : (props.suggestions?.barcode ?? ''),
  category_id: props.product?.category_id ?? '',
  brand_id: props.product?.brand_id ?? '',
  unit_id: props.product?.unit_id ?? '',
  purchase_price: props.product?.purchase_price ?? 0,
  selling_price: props.product?.selling_price ?? 0,
  minimum_stock: props.product?.minimum_stock ?? 0,
  description: props.product?.description ?? '',
  is_active: props.product?.is_active ?? true,
  image_ids: productImages.map((image) => image.id),
  images: [],
})

const selectedOption = (selected) => (selected ? [{ id: selected.id, text: selected.name }] : [])

const categorySelect = ref(null)
const brandSelect = ref(null)
const unitSelect = ref(null)
const masterModal = ref(null)

const masterConfig = {
  product_categories: { ref: categorySelect, formKey: 'category_id', label: 'Kategori' },
  product_brands: { ref: brandSelect, formKey: 'brand_id', label: 'Brand' },
  units: { ref: unitSelect, formKey: 'unit_id', label: 'Satuan' },
}

const openMasterModal = (master) => {
  masterModal.value = master
}

const closeMasterModal = () => {
  masterModal.value = null
}

const handleMasterSaved = (item) => {
  const config = masterConfig[masterModal.value]
  closeMasterModal()
  if (!item?.id || !config) return
  config.ref.value?.addItems({ id: item.id, text: item.name })
  form[config.formKey] = item.id
}

const removeImage = (id) => {
  form.image_ids = form.image_ids.filter((imageId) => imageId !== id)
}

const submit = () => {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      emit('saved')
      emit('close')
    },
  }

  if (editing) {
    form
      .transform((data) => ({ ...data, _method: 'put' }))
      .post(route('products.update', props.product.id), options)
    return
  }

  form
    .transform(() => ({
      ...form.data(),
      branch_stocks: branchStocks.value,
    }))
    .post(route('products.store'), options)
}
</script>
<template>
  <Modal
    :model-value="true"
    :title="editing ? 'Edit Produk' : 'Tambah Produk'"
    description="Kelola seluruh informasi produk dalam satu form."
    size="full"
    @update:model-value="emit('close')"
    ><form id="product-form" class="space-y-7" @submit.prevent="submit">
      <section>
        <h3 class="mb-4 font-semibold text-slate-900 dark:text-white">Informasi Dasar</h3>
        <div class="grid gap-4 sm:grid-cols-2">
          <Input
            v-model="form.name"
            label="Nama Produk"
            placeholder="Contoh: Sabun mandi"
            required
            :error="form.errors.name"
          /><Input
            v-model="form.sku"
            label="SKU"
            placeholder="Contoh: SBN-MND-001"
            required
            :error="form.errors.sku"
          /><Input
            v-model="form.barcode"
            label="Barcode (opsional)"
            placeholder="Contoh: 8991234567890"
            :error="form.errors.barcode"
          /><AsyncSelect
            ref="categorySelect"
            v-model="form.category_id"
            label="Kategori (opsional)"
            placeholder="Cari kategori..."
            :endpoint="route('search.product-categories')"
            :initial-options="selectedOption(product?.category)"
            :error="form.errors.category_id"
            addable
            add-label="Tambah Kategori"
            @add="openMasterModal('product_categories')"
          /><AsyncSelect
            ref="brandSelect"
            v-model="form.brand_id"
            label="Brand (opsional)"
            placeholder="Cari brand..."
            :endpoint="route('search.product-brands')"
            :initial-options="selectedOption(product?.brand)"
            :error="form.errors.brand_id"
            addable
            add-label="Tambah Brand"
            @add="openMasterModal('product_brands')"
          /><AsyncSelect
            ref="unitSelect"
            v-model="form.unit_id"
            label="Satuan"
            placeholder="Cari satuan..."
            :endpoint="route('search.units')"
            :initial-options="selectedOption(product?.unit)"
            :error="form.errors.unit_id"
            required
            addable
            add-label="Tambah Satuan"
            @add="openMasterModal('units')"
          />
        </div>
      </section>
      <section>
        <h3 class="mb-4 font-semibold text-slate-900 dark:text-white">Harga</h3>
        <div class="grid gap-4 sm:grid-cols-2">
          <CurrencyInput
            v-model="form.purchase_price"
            label="Harga Beli"
            placeholder="Contoh: 10.000"
            :error="form.errors.purchase_price"
          /><CurrencyInput
            v-model="form.selling_price"
            label="Harga Jual"
            placeholder="Contoh: 15.000"
            :error="form.errors.selling_price"
          />
        </div>
      </section>
      <section>
        <h3 class="mb-4 font-semibold text-slate-900 dark:text-white">Persediaan</h3>
        <div class="grid gap-4 sm:grid-cols-2">
          <Input
            v-model="form.minimum_stock"
            type="number"
            min="0"
            label="Minimum Stok"
            placeholder="Contoh: 10"
            required
            :error="form.errors.minimum_stock"
          />
          <Input
            v-if="!editing && singleBranch"
            :model-value="branchStocks[0]?.quantity ?? 0"
            type="number"
            min="0"
            label="Stok Awal"
            placeholder="Contoh: 50"
            @update:model-value="branchStocks[0].quantity = Number($event) || 0"
          />
        </div>
        <div v-if="!editing && !singleBranch && branches.length > 1" class="mt-4 space-y-3">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Stok Awal per Cabang</p>
          <div
            v-for="(stock, index) in branchStocks"
            :key="stock.branch_id"
            class="grid gap-4 sm:grid-cols-2"
          >
            <div class="flex items-center rounded-xl bg-slate-50 px-3.5 py-3 dark:bg-[#0a1b33]">
              <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{
                branches[index]?.name
              }}</span>
            </div>
            <Input
              :model-value="stock.quantity"
              type="number"
              min="0"
              label="Stok Awal"
              placeholder="0"
              @update:model-value="branchStocks[index].quantity = Number($event) || 0"
            />
          </div>
        </div>
      </section>
      <section v-if="canManageImages">
        <h3 class="mb-4 font-semibold text-slate-900 dark:text-white">Foto Produk</h3>
        <ProductImageField
          v-model="form.images"
          :initial-images="productImages"
          :error="form.errors.images"
          @removed="removeImage"
        />
      </section>
      <section>
        <h3 class="mb-4 font-semibold text-slate-900 dark:text-white">Informasi Tambahan</h3>
        <label class="block"
          ><span class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
            >Deskripsi</span
          ><textarea
            v-model="form.description"
            rows="4"
            placeholder="Contoh: Sabun mandi antibakteri ukuran 100 gram"
            class="w-full rounded-xl border-slate-300 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
          ></textarea
          ><span v-if="form.errors.description" class="mt-1 block text-sm text-red-500">{{
            form.errors.description
          }}</span></label
        ><label
          class="mt-4 flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200"
          ><input v-model="form.is_active" type="checkbox" class="rounded text-emerald-600" />Status
          Aktif</label
        >
      </section>
    </form>
    <template #footer
      ><Button variant="secondary" @click="emit('close')">Batal</Button
      ><Button type="submit" form="product-form" :loading="form.processing">{{
        editing ? 'Simpan Perubahan' : 'Simpan Produk'
      }}</Button></template
    ><ProductMasterFormModal
      v-if="masterModal"
      :master="masterModal"
      :label="masterConfig[masterModal]?.label"
      @close="closeMasterModal"
      @saved="handleMasterSaved"
  /></Modal>
</template>
