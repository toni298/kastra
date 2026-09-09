<script setup>
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import Button from '@/Components/UI/Button.vue'
import DatePicker from '@/Components/UI/DatePicker.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import NumberInput from '@/Components/UI/NumberInput.vue'
import SelectInput from '@/Components/UI/SelectInput.vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowRightLeft, ClipboardCheck, PackagePlus, Warehouse, GitBranch } from 'lucide-vue-next'
import { computed, reactive, ref, watch } from 'vue'

const props = defineProps({
  type: { type: String, required: true },
  item: { type: Object, default: null },
  warehouses: { type: Array, default: () => [] },
  branches: { type: Array, default: () => [] },
})
const emit = defineEmits(['close', 'saved'])
const submitted = ref(false)
const editing = computed(() => props.type === 'product' && Boolean(props.item))
const configs = {
  product: {
    title: editing.value ? 'Edit Produk' : 'Tambah Produk',
    description: 'Lengkapi informasi produk dan pengaturan stok awal.',
    icon: PackagePlus,
  },
  transfer: {
    title: 'Tambah Transfer Gudang',
    description: 'Pindahkan stok produk ke gudang tujuan.',
    icon: ArrowRightLeft,
  },
  opname: {
    title: 'Buat Stock Opname',
    description: 'Tentukan gudang dan jadwal pemeriksaan stok.',
    icon: ClipboardCheck,
  },
}
const config = computed(() => configs[props.type])
const form = reactive({
  name: props.item?.product ?? '',
  sku: props.item?.sku ?? '',
  category: props.item?.category ?? '',
  brand: '',
  unit: 'Pcs',
  warehouse: props.item?.warehouse ?? '',
  initialStock: '',
  minimum: props.item?.minimum?.replace(/\D/g, '') ?? '',
  cost: '',
  price: '',
  source: '',
  destination: '',
  product: props.item?.product ?? '',
  quantity: '',
  note: '',
  date: '20/07/2026',
})
const transferForm = useForm({
  source_gudang_id: '',
  destination_gudang_id: '',
  destination_type: 'gudang',
  destination_branch_id: '',
  transfer_date: new Date().toISOString().slice(0, 10),
  note: '',
  details: [{ source_stock_id: '', quantity: 1, available_quantity: null }],
})
const required = computed(() =>
  props.type === 'product'
    ? [form.name, form.sku, form.category, form.unit, form.warehouse, form.minimum]
    : props.type === 'transfer'
      ? [form.source, form.destination, form.product, form.quantity]
      : props.type === 'opname'
        ? [form.warehouse, form.date]
        : []
)
const valid = computed(
  () =>
    required.value.every((value) => String(value).trim()) &&
    (props.type !== 'transfer' || form.source !== form.destination)
)
const transferValid = computed(
  () =>
    Boolean(transferForm.source_gudang_id) &&
    (transferForm.destination_type === 'branch'
      ? Boolean(transferForm.destination_branch_id)
      : Boolean(transferForm.destination_gudang_id) &&
        transferForm.source_gudang_id !== transferForm.destination_gudang_id) &&
    Boolean(transferForm.transfer_date) &&
    transferForm.details.length > 0 &&
    transferForm.details.every(
      (detail) =>
        Boolean(detail.source_stock_id) &&
        Number(detail.quantity) > 0 &&
        Number(detail.quantity) <= Number(detail.available_quantity)
    )
)
const totalItems = computed(() => transferForm.details.length)
const totalQty = computed(() =>
  transferForm.details.reduce((sum, d) => sum + (Number(d.quantity) || 0), 0)
)
const sourceWarehouse = computed(
  () => props.warehouses.find((w) => String(w.id) === String(transferForm.source_gudang_id)) || null
)
const destinationLabel = computed(() => {
  if (transferForm.destination_type === 'branch')
    return (
      props.branches.find((b) => String(b.id) === String(transferForm.destination_branch_id))
        ?.name || ''
    )
  return (
    props.warehouses.find((w) => String(w.id) === String(transferForm.destination_gudang_id))
      ?.nama || ''
  )
})
const submit = () => {
  submitted.value = true
  if (props.type === 'transfer') {
    if (!transferValid.value) return
    transferForm.post(route('inventory.transfers.store'), {
      onSuccess: () => emit('saved', { type: 'transfer' }),
    })
    return
  }
  if (valid.value) emit('saved', { type: props.type, data: { ...form } })
}
const addDetail = () =>
  transferForm.details.push({ source_stock_id: '', quantity: 1, available_quantity: null })
const removeDetail = (index) => {
  if (transferForm.details.length > 1) transferForm.details.splice(index, 1)
}
const productEndpoint = computed(() =>
  transferForm.source_gudang_id
    ? `${route('search.products')}?gudang_id=${encodeURIComponent(transferForm.source_gudang_id)}`
    : route('search.products')
)
const setStock = (detail, option) => {
  detail.available_quantity = option?.available_quantity ?? null
}
const stockError = (detail) =>
  detail.source_stock_id &&
  detail.available_quantity !== null &&
  Number(detail.quantity) > Number(detail.available_quantity)
    ? `Stok tersedia hanya ${detail.available_quantity}. Jumlah transfer tidak mencukupi.`
    : ''
const selectClass =
  'mt-1.5 w-full rounded-xl border-slate-300 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white focus:border-emerald-500 focus:ring-emerald-500/20'
watch(
  () => transferForm.source_gudang_id,
  () => {
    transferForm.details = [{ source_stock_id: '', quantity: 1, available_quantity: null }]
  }
)
watch(
  () => transferForm.destination_type,
  () => {
    transferForm.destination_gudang_id = ''
    transferForm.destination_branch_id = ''
  }
)
</script>

<template>
  <Modal
    :model-value="true"
    :title="config.title"
    :description="config.description"
    size="full"
    @update:model-value="emit('close')"
  >
    <form id="inventory-action-form" class="space-y-5" @submit.prevent="submit">
      <div v-if="type === 'transfer'" class="space-y-6">
        <!-- Top info card -->
        <div
          class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-[#29476b] dark:bg-[#0f1f35]"
        >
          <!-- Header -->
          <div class="p-6">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                  Informasi Transfer
                </p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                  Isi detail transfer sebelum menambahkan produk.
                </p>
              </div>
              <div class="w-48">
                <DatePicker
                  v-model="transferForm.transfer_date"
                  label="Tanggal Transfer"
                  required
                />
              </div>
            </div>

            <!-- Divider -->
            <div class="my-5 border-t border-slate-100 dark:border-[#29476b]" />

            <!-- Jenis Transfer -->
            <div>
              <label class="mb-3 block text-sm font-semibold text-slate-800 dark:text-slate-100">
                Jenis Transfer
              </label>

              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <!-- Antar Gudang -->
                <button
                  type="button"
                  :class="[
                    'group relative flex min-h-[88px] items-center gap-4 rounded-2xl border p-4 text-left transition-all',
                    'focus:outline-none focus:ring-2 focus:ring-emerald-500/20',
                    transferForm.destination_type === 'gudang'
                      ? 'border-emerald-500 bg-emerald-50/70 dark:border-emerald-500 dark:bg-emerald-400/10'
                      : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 dark:border-[#29476b] dark:bg-[#0f1f35] dark:hover:bg-[#142943]',
                  ]"
                  @click="transferForm.destination_type = 'gudang'"
                >
                  <!-- Radio -->
                  <div
                    :class="[
                      'flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-colors',
                      transferForm.destination_type === 'gudang'
                        ? 'border-emerald-500'
                        : 'border-slate-300 dark:border-slate-500',
                    ]"
                  >
                    <div
                      v-if="transferForm.destination_type === 'gudang'"
                      class="h-2.5 w-2.5 rounded-full bg-emerald-500"
                    />
                  </div>
                  <!-- Icon -->
                  <div
                    :class="[
                      'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-colors',
                      transferForm.destination_type === 'gudang'
                        ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400'
                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                    ]"
                  >
                    <Warehouse class="h-5 w-5" stroke-width="1.8" />
                  </div>

                  <!-- Content -->
                  <div class="min-w-0">
                    <p
                      :class="[
                        'text-sm font-semibold',
                        transferForm.destination_type === 'gudang'
                          ? 'text-emerald-700 dark:text-emerald-400'
                          : 'text-slate-800 dark:text-slate-100',
                      ]"
                    >
                      Antar Gudang
                    </p>

                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                      Transfer stok antar gudang
                    </p>
                  </div>
                </button>

                <!-- Antar Cabang -->
                <button
                  type="button"
                  :class="[
                    'group relative flex min-h-[88px] items-center gap-4 rounded-2xl border p-4 text-left transition-all',
                    'focus:outline-none focus:ring-2 focus:ring-emerald-500/20',
                    transferForm.destination_type === 'branch'
                      ? 'border-emerald-500 bg-emerald-50/70 dark:border-emerald-500 dark:bg-emerald-400/10'
                      : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 dark:border-[#29476b] dark:bg-[#0f1f35] dark:hover:bg-[#142943]',
                  ]"
                  @click="transferForm.destination_type = 'branch'"
                >
                  <!-- Radio -->
                  <div
                    :class="[
                      'flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-colors',
                      transferForm.destination_type === 'branch'
                        ? 'border-emerald-500'
                        : 'border-slate-300 dark:border-slate-500',
                    ]"
                  >
                    <div
                      v-if="transferForm.destination_type === 'branch'"
                      class="h-2.5 w-2.5 rounded-full bg-emerald-500"
                    />
                  </div>

                  <!-- Icon -->
                  <div
                    :class="[
                      'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-colors',
                      transferForm.destination_type === 'branch'
                        ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400'
                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                    ]"
                  >
                    <GitBranch class="h-5 w-5" stroke-width="1.8" />
                  </div>

                  <!-- Content -->
                  <div class="min-w-0">
                    <p
                      :class="[
                        'text-sm font-semibold',
                        transferForm.destination_type === 'branch'
                          ? 'text-emerald-700 dark:text-emerald-400'
                          : 'text-slate-800 dark:text-slate-100',
                      ]"
                    >
                      Antar Cabang
                    </p>

                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                      Transfer stok antar cabang
                    </p>
                  </div>
                </button>
              </div>
            </div>

            <!-- Lokasi Transfer -->
            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
              <!-- Gudang Asal -->
              <div>
                <label class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-100">
                  Gudang Asal
                  <span class="text-red-500">*</span>
                </label>

                <SelectInput
                  v-model="transferForm.source_gudang_id"
                  :class="selectClass"
                  aria-label="Gudang Asal"
                  required
                >
                  <option value="">Pilih gudang asal</option>

                  <option v-for="w in warehouses" :key="w.id" :value="w.id">
                    {{ w.nama }}{{ w.kode ? ' (' + w.kode + ')' : '' }}
                  </option>
                </SelectInput>
              </div>

              <!-- Gudang / Cabang Tujuan -->
              <div>
                <label class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-100">
                  {{
                    transferForm.destination_type === 'gudang' ? 'Gudang Tujuan' : 'Cabang Tujuan'
                  }}
                  <span class="text-red-500">*</span>
                </label>

                <template v-if="transferForm.destination_type === 'gudang'">
                  <SelectInput
                    v-model="transferForm.destination_gudang_id"
                    :class="selectClass"
                    aria-label="Gudang Tujuan"
                    required
                  >
                    <option value="">Pilih gudang tujuan</option>

                    <option v-for="w in warehouses" :key="w.id" :value="w.id">
                      {{ w.nama }}{{ w.kode ? ' (' + w.kode + ')' : '' }}
                    </option>
                  </SelectInput>
                </template>

                <template v-else>
                  <SelectInput
                    v-model="transferForm.destination_branch_id"
                    :class="selectClass"
                    aria-label="Cabang Tujuan"
                    required
                  >
                    <option value="">Pilih cabang tujuan</option>

                    <option v-for="b in branches" :key="b.id" :value="b.id">
                      {{ b.name }}
                    </option>
                  </SelectInput>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Main content: products -->
        <div class="grid gap-4 items-start">
          <!-- Products list -->
          <div
            class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-[#29476b] dark:bg-[#0f1720]"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">Daftar Produk</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                  Tambah produk transfer dan jumlahnya di sini.
                </p>
              </div>
              <Button
                type="button"
                variant="primary"
                size="sm"
                class="inline-flex items-center gap-2"
                @click="addDetail"
                ><PackagePlus class="h-4 w-4" />Tambah Produk</Button
              >
            </div>

            <div class="mt-4 overflow-hidden rounded-lg border">
              <div
                class="grid grid-cols-[48px_1fr_120px_64px] items-center gap-4 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-600"
              >
                <div>No.</div>
                <div>Produk</div>
                <div>Jumlah</div>
                <div>Aksi</div>
              </div>
              <div class="divide-y bg-white">
                <div
                  v-for="(detail, index) in transferForm.details"
                  :key="index"
                  class="grid grid-cols-[48px_1fr_120px_64px] items-center gap-4 px-4 py-3 text-sm"
                >
                  <div class="text-slate-600">{{ index + 1 }}</div>
                  <div>
                    <AsyncSelect
                      v-model="detail.source_stock_id"
                      :endpoint="productEndpoint"
                      :disabled="!transferForm.source_gudang_id"
                      placeholder="Cari produk"
                      @change="setStock(detail, $event)"
                    />
                    <div
                      v-if="detail.available_quantity !== null"
                      class="mt-1 text-xs text-slate-400"
                    >
                      Stok tersedia: {{ detail.available_quantity }}
                    </div>
                  </div>
                  <div>
                    <NumberInput v-model="detail.quantity" min="1" />
                  </div>
                  <div>
                    <Button
                      type="button"
                      class="inline-flex items-center justify-center rounded-xl font-medium transition duration-150 focus:outline-none focus:ring-4 px-4 py-2.5 text-sm bg-red-600 text-white hover:bg-red-700 focus:ring-red-500/20"
                      @click="removeDetail(index)"
                      >Hapus</Button
                    >
                  </div>
                </div>
                <div
                  v-if="transferForm.details.length === 0"
                  class="p-4 text-center text-sm text-slate-500"
                >
                  Belum ada produk ditambahkan.
                </div>
              </div>
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-200"
                >Catatan (opsional)</label
              >
              <textarea
                v-model="transferForm.note"
                rows="3"
                class="mt-1.5 w-full rounded-xl border-slate-300 px-3.5 py-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
                placeholder="Catatan transaksi (opsional)"
              ></textarea>
            </div>
          </div>
        </div>
      </div>
      <p
        v-if="submitted && (type === 'transfer' ? !transferValid : !valid)"
        class="rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-600 dark:bg-red-400/10 dark:text-red-300"
      >
        Lengkapi semua field wajib dengan data yang valid.
      </p>
    </form>
    <template #footer>
      <Button variant="secondary" size="sm" @click="emit('close')">Batal</Button>
      <Button type="submit" form="inventory-action-form" size="sm" variant="primary">
        {{
          type === 'transfer'
            ? editing
              ? 'Update'
              : 'Simpan Transfer'
            : editing
              ? 'Update'
              : 'Simpan'
        }}
      </Button>
    </template>
  </Modal>
</template>
