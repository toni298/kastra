<script setup>
import { computed, ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { ArrowDownCircle, ArrowUpCircle, X } from 'lucide-vue-next'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
  branches: { type: Array, default: () => [] },
})
const emit = defineEmits(['close', 'saved'])
const page = usePage()

const form = useForm({
  branch_id: props.branches.length === 1 ? props.branches[0].id : null,
  product_id: null,
  type: 'in',
  quantity: 1,
  note: '',
})

const productKey = ref(0) // untuk reset AsyncSelect produk saat cabang berubah

// selection=product -> option.id = product_id (bukan stock id)
const productEndpoint = computed(() =>
  route('search.products', {
    selection: 'product',
    branch_id: form.branch_id || undefined,
  })
)

watch(
  () => form.branch_id,
  () => {
    form.product_id = null
    productKey.value++
  }
)

const canSubmit = computed(() =>
  Boolean(form.branch_id && form.product_id && form.type && Number(form.quantity) >= 1)
)

function setType(type) {
  form.type = type
}

function submit() {
  if (!canSubmit.value || form.processing) return
  form.post(route('inventory.adjustments.store'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('saved')
      emit('close')
    },
  })
}

const successMessage = computed(() => page.props.flash?.success || '')
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
      @click.self="emit('close')"
    >
      <div
        class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-[#0a1b33]"
      >
        <!-- Header -->
        <div
          class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-[#1c3350]"
        >
          <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Penyesuaian Stok</h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-300">
              Tambah atau kurangi stok produk di cabang (POS / storefront)
            </p>
          </div>
          <button
            type="button"
            class="grid size-9 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-[#163354]"
            aria-label="Tutup"
            @click="emit('close')"
          >
            <X :size="18" />
          </button>
        </div>

        <form class="space-y-5 px-6 py-5" @submit.prevent="submit">
          <!-- Cabang -->
          <AsyncSelect
            v-if="props.branches.length !== 1"
            v-model="form.branch_id"
            label="Cabang"
            :endpoint="route('search.cabang')"
            placeholder="Cari cabang..."
            :error="form.errors.branch_id"
            required
          />

          <div
            v-else
            class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 dark:border-[#29476b] dark:bg-[#102542]"
          >
            <p class="text-xs text-slate-500 dark:text-slate-400">Cabang</p>
            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-white">
              {{ props.branches[0].name }}
            </p>
          </div>

          <!-- Produk -->
          <AsyncSelect
            :key="productKey"
            v-model="form.product_id"
            label="Produk"
            :endpoint="productEndpoint"
            placeholder="Cari produk (nama / SKU)..."
            :error="form.errors.product_id"
            :disabled="!form.branch_id"
            :hint="form.branch_id ? '' : 'Pilih cabang terlebih dahulu'"
            required
          />

          <!-- Jenis penyesuaian -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
              Jenis Penyesuaian <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
              <button
                type="button"
                :class="[
                  'flex items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition',
                  form.type === 'in'
                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700 ring-2 ring-emerald-500/20 dark:bg-emerald-400/10 dark:text-emerald-300'
                    : 'border-slate-200 text-slate-600 hover:border-emerald-300 hover:bg-slate-50 dark:border-[#29476b] dark:text-slate-300',
                ]"
                @click="setType('in')"
              >
                <ArrowDownCircle :size="18" />
                Stok Masuk
              </button>
              <button
                type="button"
                :class="[
                  'flex items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition',
                  form.type === 'out'
                    ? 'border-red-500 bg-red-50 text-red-700 ring-2 ring-red-500/20 dark:bg-red-400/10 dark:text-red-300'
                    : 'border-slate-200 text-slate-600 hover:border-red-300 hover:bg-slate-50 dark:border-[#29476b] dark:text-slate-300',
                ]"
                @click="setType('out')"
              >
                <ArrowUpCircle :size="18" />
                Stok Keluar
              </button>
            </div>
            <p v-if="form.errors.type" class="mt-1.5 text-sm font-medium text-red-500">
              {{ form.errors.type }}
            </p>
          </div>

          <!-- Jumlah -->
          <div>
            <label
              for="adj-qty"
              class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
            >
              Jumlah <span class="text-red-500">*</span>
            </label>
            <input
              id="adj-qty"
              v-model.number="form.quantity"
              type="number"
              min="1"
              step="1"
              class="h-[46px] w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
              :class="{ 'border-red-500': form.errors.quantity }"
              placeholder="Masukkan jumlah"
            />
            <p v-if="form.errors.quantity" class="mt-1.5 text-sm font-medium text-red-500">
              {{ form.errors.quantity }}
            </p>
          </div>

          <!-- Catatan -->
          <div>
            <label
              for="adj-note"
              class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
            >
              Catatan / Alasan
            </label>
            <textarea
              id="adj-note"
              v-model="form.note"
              rows="2"
              maxlength="500"
              class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
              :class="{ 'border-red-500': form.errors.note }"
              placeholder="Contoh: Barang datang dari supplier / koreksi stok"
            ></textarea>
            <p v-if="form.errors.note" class="mt-1.5 text-sm font-medium text-red-500">
              {{ form.errors.note }}
            </p>
          </div>

          <p
            v-if="successMessage"
            class="rounded-lg bg-emerald-50 px-3.5 py-2.5 text-sm font-medium text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
          >
            {{ successMessage }}
          </p>

          <footer
            class="flex justify-end gap-3 border-t border-slate-100 pt-4 dark:border-[#1c3350]"
          >
            <Button
              type="button"
              variant="secondary"
              :disabled="form.processing"
              @click="emit('close')"
            >
              Batal
            </Button>
            <Button
              type="submit"
              :loading="form.processing"
              :disabled="!canSubmit || form.processing"
              :variant="form.type === 'in' ? 'primary' : 'danger'"
            >
              {{ form.type === 'in' ? 'Tambah Stok' : 'Kurangi Stok' }}
            </Button>
          </footer>
        </form>
      </div>
    </div>
  </Teleport>
</template>
