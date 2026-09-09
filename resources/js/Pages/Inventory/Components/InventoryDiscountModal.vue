<script setup>
import { computed, ref } from 'vue'
import { X } from 'lucide-vue-next'
import axios from 'axios'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
  item: { type: Object, required: true },
})
const emit = defineEmits(['close', 'saved'])

const discount = ref(Number(props.item.stock?.discount ?? 0))
const submitting = ref(false)
const error = ref('')

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value || 0))

const normalPrice = computed(() => Number(props.item.product?.selling_price ?? 0))
const discountedPrice = computed(() =>
  Math.round(normalPrice.value * (1 - Number(discount.value || 0) / 100))
)

const submit = async () => {
  error.value = ''
  const value = Number(discount.value)

  if (!Number.isInteger(value) || value < 0 || value > 100) {
    error.value = 'Diskon harus berupa angka bulat antara 0 dan 100.'
    return
  }

  submitting.value = true
  try {
    await axios.put(route('inventory.branch-product-stocks.discount.update', props.item.id), {
      discount: value,
    })
    emit('saved', value)
  } catch (requestError) {
    error.value = requestError.response?.data?.message ?? 'Diskon gagal diterapkan.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-[60] bg-slate-950/55" @click="emit('close')"></div>
    <div class="fixed inset-0 z-[60] grid place-items-center p-4" role="dialog" aria-modal="true">
      <form
        class="w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-[#102542]"
        @submit.prevent="submit"
        @click.stop
      >
        <header
          class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-[#29476b]"
        >
          <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Tambahkan Diskon</h2>
          <button
            type="button"
            class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-[#163354]"
            aria-label="Tutup"
            @click="emit('close')"
          >
            <X :size="20" />
          </button>
        </header>

        <div class="space-y-5 px-6 py-5">
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Produk</p>
            <p class="mt-1 font-medium text-slate-950 dark:text-white">
              {{ item.product?.name ?? '—' }}
            </p>
          </div>

          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Harga Normal</p>
            <p class="mt-1 font-medium text-slate-950 dark:text-white">
              {{ formatCurrency(normalPrice) }}
            </p>
          </div>

          <label class="block">
            <span class="text-sm text-slate-500 dark:text-slate-400">Diskon</span>
            <div class="mt-1 flex items-center gap-2">
              <input
                v-model.number="discount"
                type="number"
                min="0"
                max="100"
                step="1"
                inputmode="numeric"
                class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-950 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
              />
              <span class="font-medium text-slate-500">%</span>
            </div>
          </label>

          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Harga Setelah Diskon</p>
            <p class="mt-1 text-lg font-semibold text-emerald-600">
              {{ formatCurrency(discountedPrice) }}
            </p>
          </div>

          <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
          </p>
        </div>

        <footer
          class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4 dark:border-[#29476b]"
        >
          <Button variant="secondary" type="button" :disabled="submitting" @click="emit('close')">
            Batal
          </Button>
          <Button type="submit" :loading="submitting">Terapkan Diskon</Button>
        </footer>
      </form>
    </div>
  </Teleport>
</template>
